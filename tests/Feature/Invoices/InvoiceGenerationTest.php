<?php

namespace Tests\Feature\Invoices;

use App\Livewire\Admin\Invoices\Show;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function makeInvoiceWithItem(array $attrs = []): Invoice
    {
        $invoice = Invoice::factory()->create(array_merge([
            'subtotal'    => 1000000,
            'tax_rate'    => 12,
            'tax_amount'  => 120000,
            'total'       => 1120000,
            'paid_amount' => 0,
            'status'      => 'draft',
        ], $attrs));

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => null,
            'name'       => 'POS-терминал Sunmi T2',
            'sku'        => 'POS-T2',
            'quantity'   => 2,
            'unit_price' => 500000,
            'tax_rate'   => 12,
            'total'      => 1000000,
            'sort_order' => 0,
        ]);

        return $invoice->fresh();
    }

    public function test_invoice_has_correct_items_and_total(): void
    {
        $this->seedRoles();

        $invoice = $this->makeInvoiceWithItem();

        $this->assertDatabaseCount('invoice_items', 1);
        $this->assertEquals(1120000.0, (float) $invoice->total);
        $this->assertEquals(120000.0, (float) $invoice->tax_amount);
        $this->assertSame(0.0, (float) $invoice->paid_amount);
        $this->assertSame('1120000.00', $invoice->getRemainingAttribute());
    }

    public function test_partial_payment_marks_invoice_as_partially_paid(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $invoice = $this->makeInvoiceWithItem(['manager_id' => $manager->id]);

        Livewire::actingAs($manager)
            ->test(Show::class, ['invoice' => $invoice])
            ->set('paymentAmount', '500000')
            ->set('paymentDate', now()->toDateString())
            ->set('paymentMethod', 'bank_transfer')
            ->call('addPayment')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);
        $this->assertEquals(500000.0, (float) $invoice->paid_amount);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount'     => 500000,
            'method'     => 'bank_transfer',
        ]);
    }

    public function test_full_payment_marks_invoice_as_paid(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $invoice = $this->makeInvoiceWithItem(['manager_id' => $manager->id]);

        Livewire::actingAs($manager)
            ->test(Show::class, ['invoice' => $invoice])
            ->set('paymentAmount', '1120000')
            ->set('paymentDate', now()->toDateString())
            ->call('addPayment')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertEquals(1120000.0, (float) $invoice->paid_amount);
    }

    public function test_two_partial_payments_accumulate_to_paid(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $invoice = $this->makeInvoiceWithItem(['manager_id' => $manager->id]);

        Livewire::actingAs($manager)
            ->test(Show::class, ['invoice' => $invoice])
            ->set('paymentAmount', '600000')
            ->set('paymentDate', now()->toDateString())
            ->call('addPayment');

        $invoice->refresh();
        $this->assertSame('partially_paid', $invoice->status);

        Livewire::actingAs($manager)
            ->test(Show::class, ['invoice' => $invoice])
            ->set('paymentAmount', '520000')
            ->set('paymentDate', now()->toDateString())
            ->call('addPayment');

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertEquals(1120000.0, (float) $invoice->paid_amount);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_payment_amount_must_be_positive(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $invoice = $this->makeInvoiceWithItem(['manager_id' => $manager->id]);

        Livewire::actingAs($manager)
            ->test(Show::class, ['invoice' => $invoice])
            ->set('paymentAmount', '0')
            ->set('paymentDate', now()->toDateString())
            ->call('addPayment')
            ->assertHasErrors(['paymentAmount']);

        $this->assertDatabaseCount('payments', 0);
    }
}
