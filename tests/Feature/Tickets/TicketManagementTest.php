<?php

namespace Tests\Feature\Tickets;

use App\Livewire\Admin\Tickets\CreateForm;
use App\Livewire\Admin\Tickets\Index;
use App\Livewire\Admin\Tickets\Show;
use App\Models\Customer\Customer;
use App\Models\Support\Ticket;
use App\Models\Support\TicketCategory;
use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    // ── Creation & defaults ────────────────────────────────────────────────

    public function test_tech_support_can_create_ticket_with_default_priority_and_open_status(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $customer = Customer::factory()->create();

        Livewire::actingAs($agent)
            ->test(CreateForm::class)
            ->set('customer_id', $customer->id)
            ->set('subject', 'Не печатает чек')
            ->call('save')
            ->assertHasNoErrors();

        $ticket = Ticket::where('subject', 'Не печатает чек')->first();
        $this->assertNotNull($ticket);
        $this->assertSame('open', $ticket->status);
        $this->assertSame('medium', $ticket->priority, 'Default priority column value is "medium" (see tickets migration), not "normal".');
        $this->assertSame($agent->id, $ticket->created_by);
    }

    public function test_ticket_priority_must_be_a_valid_enum_value(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');

        Livewire::actingAs($agent)
            ->test(CreateForm::class)
            ->set('subject', 'Test')
            ->set('priority', 'urgent') // not one of low/medium/high/critical
            ->call('save')
            ->assertHasErrors(['priority']);
    }

    public function test_ticket_subject_is_required(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');

        Livewire::actingAs($agent)
            ->test(CreateForm::class)
            ->set('subject', '')
            ->call('save')
            ->assertHasErrors(['subject' => 'required']);
    }

    public function test_ticket_carries_category_sla_hours_via_relation(): void
    {
        $this->seedRoles();
        $category = TicketCategory::create([
            'name'       => 'Оборудование',
            'slug'       => 'hardware',
            'sla_hours'  => 8,
            'is_active'  => true,
        ]);
        $ticket = TicketFactory::new()->create(['category_id' => $category->id]);

        $this->assertSame(8, $ticket->category->sla_hours);
    }

    // ── Ticket ownership policy (view) ──────────────────────────────────────

    public function test_assigned_tech_support_can_view_ticket(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $ticket = TicketFactory::new()->create(['assignee_id' => $agent->id]);

        Livewire::actingAs($agent)
            ->test(Show::class, ['ticket' => $ticket])
            ->assertOk();
    }

    public function test_unassigned_tech_support_cannot_view_someone_elses_ticket(): void
    {
        $this->seedRoles();
        $owner = User::factory()->create();
        $owner->assignRole('tech-support');
        $other = User::factory()->create();
        $other->assignRole('tech-support');
        $ticket = TicketFactory::new()->create(['assignee_id' => $owner->id, 'created_by' => $owner->id]);

        Livewire::actingAs($other)
            ->test(Show::class, ['ticket' => $ticket])
            ->assertForbidden();
    }

    public function test_sales_director_can_view_any_ticket(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $director = User::factory()->create();
        $director->assignRole('sales-director');
        $ticket = TicketFactory::new()->create(['assignee_id' => $agent->id]);

        Livewire::actingAs($director)
            ->test(Show::class, ['ticket' => $ticket])
            ->assertOk();
    }

    // ── close/assign permission (tech-support allow, sales-manager deny) ────

    public function test_tech_support_can_change_ticket_status(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $ticket = TicketFactory::new()->create(['assignee_id' => $agent->id]);

        Livewire::actingAs($agent)
            ->test(Show::class, ['ticket' => $ticket])
            ->call('changeStatus', 'resolved')
            ->assertOk();

        $this->assertSame('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    public function test_sales_manager_cannot_change_ticket_status(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $ticket = TicketFactory::new()->create(['assignee_id' => $agent->id, 'created_by' => $agent->id]);

        // sales-manager isn't assignee/creator, so mount() itself already 403s
        // (TicketPolicy::view requires ownership for non-director/admin roles).
        Livewire::actingAs($sm)
            ->test(Show::class, ['ticket' => $ticket])
            ->assertForbidden();

        $this->assertSame('open', $ticket->fresh()->status);
    }

    /**
     * BUG: App\Livewire\Admin\Tickets\Index has no `mount()`/`viewAny` authorization
     * check at all (unlike Catalog\Products\Index / Catalog\Categories\Index, which
     * both call `$this->authorize('viewAny', ...)`). The route middleware for
     * `/admin/tickets` only requires ANY of
     * `super-admin|sales-director|sales-manager|tech-support|catalog-manager|accountant`
     * (see routes/web.php), and `TicketPolicy::viewAny()` correctly requires the
     * `tickets.view` permission — which, per config/permissions.php, only
     * super-admin/sales-director/tech-support actually have. sales-manager,
     * catalog-manager and accountant do NOT have `tickets.view`, yet they can open
     * `/admin/tickets` and see the FULL ticket list (subjects, customers, assignees)
     * because `getTicketsProperty()` only narrows the query for the literal
     * `tech-support` role and otherwise returns everything unfiltered.
     *
     * This test encodes the secure/expected behavior (sales-manager, who lacks
     * `tickets.view`, should not see the tickets list) and currently fails.
     *
     * Fix belongs to ticket-system (owns the Tickets vertical per CLAUDE.md):
     * add `$this->authorize('viewAny', Ticket::class)` in an Index::mount()
     * (mirroring Catalog\Products\Index), or otherwise gate the component.
     */
    public function test_sales_manager_without_tickets_permission_cannot_see_tickets_index(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        TicketFactory::new()->create(['subject' => 'Конфиденциальная тема тикета']);

        $this->assertFalse($sm->can('viewAny', Ticket::class), 'Sanity check: sales-manager has no tickets.view permission.');

        Livewire::actingAs($sm)
            ->test(Index::class)
            ->assertDontSee('Конфиденциальная тема тикета');
    }

    /**
     * BUG: App\Livewire\Admin\Tickets\CreateForm::save() never calls
     * `$this->authorize(...)` (TicketPolicy::create() exists and correctly
     * requires `tickets.view`, but the component never invokes it). Combined
     * with the broad admin route middleware, ANY internal role that can reach
     * `/admin/tickets` — including sales-manager, catalog-manager, accountant,
     * none of whom have any `tickets.*` permission — can create tickets.
     *
     * This test encodes the secure/expected behavior (sales-manager should not
     * be able to create a ticket) and currently fails because the ticket is
     * created successfully.
     *
     * Fix belongs to ticket-system: add `$this->authorize('create', Ticket::class)`
     * at the top of CreateForm::save().
     */
    public function test_sales_manager_cannot_create_ticket(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');

        Livewire::actingAs($sm)
            ->test(CreateForm::class)
            ->set('subject', 'Не должно создаться')
            ->call('save')
            ->assertForbidden();

        $this->assertDatabaseMissing('tickets', ['subject' => 'Не должно создаться']);
    }

    // ── internal vs public comment visibility (Portal) ──────────────────────

    public function test_portal_ticket_show_hides_internal_comments_but_shows_public_ones(): void
    {
        $this->seedRoles();

        $clientUser = User::factory()->create();
        $clientUser->assignRole('client-user');
        $customer = Customer::factory()->create();
        $clientUser->customers()->attach($customer->id, ['role' => 'viewer']);

        $staff = User::factory()->create();
        $staff->assignRole('tech-support');

        $ticket = TicketFactory::new()->create(['customer_id' => $customer->id]);

        $ticket->comments()->create([
            'user_id'     => $staff->id,
            'body'        => 'Внутренняя заметка для коллег',
            'is_internal' => true,
        ]);
        $ticket->comments()->create([
            'user_id'     => $staff->id,
            'body'        => 'Публичный ответ клиенту',
            'is_internal' => false,
        ]);

        Livewire::actingAs($clientUser)
            ->test(\App\Livewire\Portal\Tickets\Show::class, ['ticket' => $ticket])
            ->assertSee('Публичный ответ клиенту')
            ->assertDontSee('Внутренняя заметка для коллег');
    }

    public function test_admin_ticket_show_displays_both_internal_and_public_comments(): void
    {
        $this->seedRoles();
        $staff = User::factory()->create();
        $staff->assignRole('tech-support');
        $ticket = TicketFactory::new()->create(['assignee_id' => $staff->id]);

        $ticket->comments()->create([
            'user_id'     => $staff->id,
            'body'        => 'Внутренняя заметка видна сотруднику',
            'is_internal' => true,
        ]);
        $ticket->comments()->create([
            'user_id'     => $staff->id,
            'body'        => 'Публичный комментарий тоже виден сотруднику',
            'is_internal' => false,
        ]);

        Livewire::actingAs($staff)
            ->test(Show::class, ['ticket' => $ticket])
            ->assertSee('Внутренняя заметка видна сотруднику')
            ->assertSee('Публичный комментарий тоже виден сотруднику');
    }
}
