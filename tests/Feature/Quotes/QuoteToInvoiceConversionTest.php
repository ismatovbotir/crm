<?php

namespace Tests\Feature\Quotes;

use App\Livewire\Admin\Quotes\Show;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use App\Models\Quote\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteToInvoiceConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    /**
     * Builds a Quote with one line item, bypassing QuoteItem's mass-assignment
     * (see test_creating_quote_items_via_mass_assignment_fails_due_to_missing_final_price_fillable
     * for why ->create() cannot be used here).
     */
    protected function makeQuoteWithItem(array $quoteAttrs = []): Quote
    {
        $quote = Quote::factory()->accepted()->create(array_merge([
            'subtotal'    => 1000000,
            'vat_percent' => 12,
            'vat_amount'  => 120000,
            'total'       => 1120000,
        ], $quoteAttrs));

        $item = new QuoteItem();
        $item->quote_id = $quote->id;
        $item->product_id = null;
        $item->name = 'POS-терминал Sunmi T2';
        $item->sku = 'POS-T2';
        $item->description = null;
        $item->quantity = 2;
        $item->unit_price = 500000;
        $item->discount_percent = 0;
        $item->final_price = 500000;
        $item->total = 1000000;
        $item->sort_order = 0;
        $item->save();

        return $quote->fresh();
    }

    public function test_accepted_quote_converts_to_invoice_with_correct_totals_and_vat(): void
    {
        $this->seedRoles();

        $manager  = User::factory()->create();
        $manager->assignRole('sales-manager');

        $customer = Customer::factory()->create(['payment_terms_days' => 15]);
        $quote    = $this->makeQuoteWithItem([
            'manager_id'  => $manager->id,
            'customer_id' => $customer->id,
        ]);

        Livewire::actingAs($manager)
            ->test(Show::class, ['quote' => $quote])
            ->call('convertToInvoice');

        $invoice = Invoice::where('quote_id', $quote->id)->first();

        $this->assertNotNull($invoice, 'Invoice was not created from the accepted quote.');
        $this->assertSame('draft', $invoice->status);
        $this->assertSame($quote->customer_id, $invoice->customer_id);
        $this->assertSame($quote->manager_id, $invoice->manager_id);
        $this->assertEquals(1000000.0, (float) $invoice->subtotal);
        $this->assertEquals(12.0, (float) $invoice->tax_rate);
        $this->assertEquals(120000.0, (float) $invoice->tax_amount);
        $this->assertEquals(1120000.0, (float) $invoice->total);
        $this->assertSame(
            now()->addDays(15)->toDateString(),
            $invoice->due_date->toDateString(),
            'due_date should follow customer.payment_terms_days'
        );

        $this->assertDatabaseCount('invoice_items', 1);
        $invoiceItem = $invoice->items()->first();
        $this->assertSame('POS-терминал Sunmi T2', $invoiceItem->name);
        $this->assertSame('POS-T2', $invoiceItem->sku);
        $this->assertEquals(2, $invoiceItem->quantity);
        $this->assertEquals(500000.0, (float) $invoiceItem->unit_price);
        $this->assertEquals(1000000.0, (float) $invoiceItem->total);
    }

    public function test_quote_cannot_be_converted_twice(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $quote = $this->makeQuoteWithItem(['manager_id' => $manager->id]);

        Livewire::actingAs($manager)->test(Show::class, ['quote' => $quote])->call('convertToInvoice');
        $this->assertDatabaseCount('invoices', 1);

        // second attempt must not create a second invoice
        Livewire::actingAs($manager)->test(Show::class, ['quote' => $quote])->call('convertToInvoice');
        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_draft_quote_cannot_be_converted_to_invoice(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $quote = Quote::factory()->create([
            'manager_id' => $manager->id,
            'status'     => 'draft',
        ]);

        Livewire::actingAs($manager)
            ->test(Show::class, ['quote' => $quote])
            ->call('convertToInvoice');

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_sales_manager_cannot_convert_another_managers_quote(): void
    {
        $this->seedRoles();

        $owner  = User::factory()->create();
        $owner->assignRole('sales-manager');
        $intruder = User::factory()->create();
        $intruder->assignRole('sales-manager');

        $quote = $this->makeQuoteWithItem(['manager_id' => $owner->id]);

        Livewire::actingAs($intruder)
            ->test(Show::class, ['quote' => $quote])
            ->assertForbidden();

        $this->assertDatabaseCount('invoices', 0);
    }

    /**
     * REGRESSION for BUG #2 (fixed by laravel-fullstack, 2026-07-06):
     * App\Models\Quote\QuoteItem::$fillable previously did not include
     * `final_price`, while `quote_items.final_price` is a NOT NULL column with
     * no DB default (database/migrations/2026_04_28_150100_create_quote_items_table.php).
     *
     * Both App\Livewire\Admin\Documents\CreateForm::saveQuote() and
     * App\Livewire\Admin\Quotes\CreateForm::save() create items via
     * `$quote->items()->create([..., 'final_price' => ...])`. This test asserts
     * that mass-assignment with `final_price` now succeeds (no exception) and
     * that the value round-trips correctly through the database, guarding
     * against `final_price` being dropped from `$fillable`/`$casts` again.
     */
    public function test_creating_quote_items_via_mass_assignment_persists_final_price(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $customer = Customer::factory()->create();

        Livewire::actingAs($manager)
            ->test(\App\Livewire\Admin\Documents\CreateForm::class, ['type' => 'quote'])
            ->set('customer_id', $customer->id)
            ->set('items', [[
                'product_id'     => null,
                'name'           => 'POS-терминал Sunmi T2',
                'sku'            => 'POS-T2',
                'description'    => '',
                'quantity'       => 2,
                'unit_price'     => 500000,
                'discount_value' => 0,
                'discount_type'  => 'percent',
                'final_price'    => 500000,
                'total'          => 1000000,
            ]])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('quote_items', 1);

        $item = QuoteItem::first();
        $this->assertNotNull($item, 'QuoteItem was not persisted.');
        $this->assertEquals(500000.0, (float) $item->final_price);
        $this->assertEquals(500000.0, (float) $item->fresh()->final_price, 'final_price does not round-trip from the database.');
    }
}
