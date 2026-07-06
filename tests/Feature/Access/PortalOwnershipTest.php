<?php

namespace Tests\Feature\Access;

use App\Livewire\Portal\Invoices\Index as InvoicesIndex;
use App\Livewire\Portal\Invoices\Show as InvoicesShow;
use App\Livewire\Portal\Quotes\Index as QuotesIndex;
use App\Livewire\Portal\Quotes\Show as QuotesShow;
use App\Livewire\Portal\Tickets\Index as TicketsIndex;
use App\Livewire\Portal\Tickets\Show as TicketsShow;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Client Portal ownership: a client-user/client-admin must only ever see
 * Quotes/Invoices/Tickets belonging to a Customer they're attached to via the
 * `customer_users` pivot (App\Models\User::customers()) — never another
 * company's records, whether via the list (Index) or a direct link (Show).
 */
class PortalOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function makeClientUser(Customer $customer): User
    {
        $user = User::factory()->create();
        $user->assignRole('client-user');
        $user->customers()->attach($customer->id, ['role' => 'viewer']);

        return $user;
    }

    // ── Quotes ───────────────────────────────────────────────────────────

    public function test_client_sees_only_own_quotes_in_index(): void
    {
        $this->seedRoles();
        $myCustomer    = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);

        Quote::factory()->create(['customer_id' => $myCustomer->id, 'number' => 'КП-MINE-001']);
        Quote::factory()->create(['customer_id' => $otherCustomer->id, 'number' => 'КП-OTHER-001']);

        Livewire::actingAs($me)
            ->test(QuotesIndex::class)
            ->assertSee('КП-MINE-001')
            ->assertDontSee('КП-OTHER-001');
    }

    public function test_client_cannot_open_another_customers_quote_directly(): void
    {
        $this->seedRoles();
        $myCustomer    = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);

        $otherQuote = Quote::factory()->create(['customer_id' => $otherCustomer->id]);

        Livewire::actingAs($me)
            ->test(QuotesShow::class, ['quote' => $otherQuote])
            ->assertForbidden();
    }

    public function test_client_can_open_own_quote_directly(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);
        $myQuote = Quote::factory()->create(['customer_id' => $myCustomer->id, 'status' => 'sent']);

        Livewire::actingAs($me)
            ->test(QuotesShow::class, ['quote' => $myQuote])
            ->assertOk();
    }

    // ── Invoices ─────────────────────────────────────────────────────────

    public function test_client_sees_only_own_invoices_in_index(): void
    {
        $this->seedRoles();
        $myCustomer    = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);

        Invoice::factory()->create(['customer_id' => $myCustomer->id, 'number' => 'INV-MINE-001']);
        Invoice::factory()->create(['customer_id' => $otherCustomer->id, 'number' => 'INV-OTHER-001']);

        Livewire::actingAs($me)
            ->test(InvoicesIndex::class)
            ->assertSee('INV-MINE-001')
            ->assertDontSee('INV-OTHER-001');
    }

    public function test_client_cannot_open_another_customers_invoice_directly(): void
    {
        $this->seedRoles();
        $myCustomer    = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);

        $otherInvoice = Invoice::factory()->create(['customer_id' => $otherCustomer->id]);

        Livewire::actingAs($me)
            ->test(InvoicesShow::class, ['invoice' => $otherInvoice])
            ->assertForbidden();
    }

    public function test_client_can_open_own_invoice_directly(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);
        $myInvoice = Invoice::factory()->create(['customer_id' => $myCustomer->id]);

        Livewire::actingAs($me)
            ->test(InvoicesShow::class, ['invoice' => $myInvoice])
            ->assertOk();
    }

    // ── Tickets ──────────────────────────────────────────────────────────

    public function test_client_sees_only_own_tickets_in_index(): void
    {
        $this->seedRoles();
        $myCustomer    = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);

        TicketFactory::new()->create(['customer_id' => $myCustomer->id, 'subject' => 'My printer is broken']);
        TicketFactory::new()->create(['customer_id' => $otherCustomer->id, 'subject' => 'Other company issue']);

        Livewire::actingAs($me)
            ->test(TicketsIndex::class)
            ->assertSee('My printer is broken')
            ->assertDontSee('Other company issue');
    }

    public function test_client_cannot_open_another_customers_ticket_directly(): void
    {
        $this->seedRoles();
        $myCustomer    = Customer::factory()->create();
        $otherCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);

        $otherTicket = TicketFactory::new()->create(['customer_id' => $otherCustomer->id]);

        Livewire::actingAs($me)
            ->test(TicketsShow::class, ['ticket' => $otherTicket])
            ->assertForbidden();
    }

    public function test_client_can_open_own_ticket_directly(): void
    {
        $this->seedRoles();
        $myCustomer = Customer::factory()->create();
        $me = $this->makeClientUser($myCustomer);
        $myTicket = TicketFactory::new()->create(['customer_id' => $myCustomer->id]);

        Livewire::actingAs($me)
            ->test(TicketsShow::class, ['ticket' => $myTicket])
            ->assertOk();
    }

    // ── Multi-customer user edge case ───────────────────────────────────

    /**
     * BUG: App\Livewire\Portal\Invoices\Show::mount() and
     * App\Livewire\Portal\Tickets\Show::mount() both resolve ownership via
     * `auth()->user()->customers()->first()` and then compare
     * `$model->customer_id === $customer->id`. For a portal user attached to
     * MORE THAN ONE customer (a legitimate case per the `customer_users`
     * many-to-many pivot — see data-contracts.md), this only ever checks the
     * FIRST attached customer, so viewing an invoice/ticket that belongs to
     * their SECOND (or later) attached customer is incorrectly rejected with
     * 403 — even though the user legitimately owns it.
     *
     * App\Livewire\Portal\Quotes\Show::mount() does NOT have this problem: it
     * correctly checks `auth()->user()->customers()->where('customers.id', $quote->customer_id)->exists()`,
     * i.e. ANY of the user's attached customers. Invoices\Show/Tickets\Show
     * should use the same pattern for consistency.
     *
     * This is a false-deny (fails closed, not an IDOR/security hole), but it's
     * still a functional access-control bug for any multi-company portal user.
     *
     * This test encodes the expected/secure behavior (own invoice via 2nd
     * attached customer should be viewable) and currently fails with 403.
     *
     * Fix belongs to laravel-fullstack: change
     * `auth()->user()->customers()->first()` to the `->where(...)->exists()`
     * pattern (or resolve the specific customer_id from the model instead of
     * always picking "first") in both Portal\Invoices\Show and Portal\Tickets\Show.
     */
    public function test_client_with_two_customers_can_view_invoice_belonging_to_second_customer(): void
    {
        $this->seedRoles();
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $me = User::factory()->create();
        $me->assignRole('client-user');
        $me->customers()->attach($customerA->id, ['role' => 'viewer']);
        $me->customers()->attach($customerB->id, ['role' => 'viewer']);

        $invoiceForSecondCustomer = Invoice::factory()->create(['customer_id' => $customerB->id]);

        Livewire::actingAs($me)
            ->test(InvoicesShow::class, ['invoice' => $invoiceForSecondCustomer])
            ->assertOk();
    }

    /** @see test_client_with_two_customers_can_view_invoice_belonging_to_second_customer — same root cause, Tickets side. */
    public function test_client_with_two_customers_can_view_ticket_belonging_to_second_customer(): void
    {
        $this->seedRoles();
        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();
        $me = User::factory()->create();
        $me->assignRole('client-user');
        $me->customers()->attach($customerA->id, ['role' => 'viewer']);
        $me->customers()->attach($customerB->id, ['role' => 'viewer']);

        $ticketForSecondCustomer = TicketFactory::new()->create(['customer_id' => $customerB->id]);

        Livewire::actingAs($me)
            ->test(TicketsShow::class, ['ticket' => $ticketForSecondCustomer])
            ->assertOk();
    }
}
