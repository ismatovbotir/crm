<?php

namespace Tests\Feature\Leads;

use App\Livewire\Admin\Leads\Index;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadActivity;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LeadKanbanTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function source(): LeadSource
    {
        return LeadSource::firstOrCreate(
            ['slug' => 'site'],
            ['name' => 'Сайт rsg.uz', 'is_active' => true, 'sort_order' => 1]
        );
    }

    // ── Ownership scoping in Kanban mode ─────────────────────────────────────

    public function test_sales_manager_sees_only_own_leads_in_kanban(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $other = User::factory()->create();
        $other->assignRole('sales-manager');

        $source = $this->source();
        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Свой Лид', 'status' => 'new',
        ]);
        Lead::factory()->create([
            'manager_id' => $other->id, 'source_id' => $source->id,
            'name' => 'Чужой Лид', 'status' => 'new',
        ]);

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->set('viewMode', 'kanban')
            ->assertSee('Свой Лид')
            ->assertDontSee('Чужой Лид');
    }

    public function test_sales_director_sees_leads_from_all_managers_in_kanban(): void
    {
        $this->seedRoles();

        $managerA = User::factory()->create();
        $managerA->assignRole('sales-manager');
        $managerB = User::factory()->create();
        $managerB->assignRole('sales-manager');
        $director = User::factory()->create();
        $director->assignRole('sales-director');

        $source = $this->source();
        Lead::factory()->create(['manager_id' => $managerA->id, 'source_id' => $source->id, 'name' => 'Kanban Lead A', 'status' => 'new']);
        Lead::factory()->create(['manager_id' => $managerB->id, 'source_id' => $source->id, 'name' => 'Kanban Lead B', 'status' => 'qualified']);

        Livewire::actingAs($director)
            ->test(Index::class)
            ->set('viewMode', 'kanban')
            ->assertSee('Kanban Lead A')
            ->assertSee('Kanban Lead B');
    }

    public function test_super_admin_sees_leads_from_all_managers_in_kanban(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $source = $this->source();
        Lead::factory()->create(['manager_id' => $manager->id, 'source_id' => $source->id, 'name' => 'Kanban Lead Admin View', 'status' => 'contacted']);

        Livewire::actingAs($admin)
            ->test(Index::class)
            ->set('viewMode', 'kanban')
            ->assertSee('Kanban Lead Admin View');
    }

    // ── `client` leads excluded from Kanban columns ──────────────────────────

    public function test_client_status_lead_is_excluded_from_kanban_but_visible_in_table_with_explicit_filter(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'name' => 'Конвертированный Канбан Лид', 'status' => 'client',
        ]);

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->set('viewMode', 'kanban')
            ->assertDontSee('Конвертированный Канбан Лид');

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->set('statusFilter', 'client')
            ->assertSee('Конвертированный Канбан Лид');
    }

    // ── moveLeadStatus() ──────────────────────────────────────────────────────

    public function test_sales_manager_can_move_own_lead_status_and_activity_is_logged(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        $lead = Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'status' => 'new',
        ]);

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->call('moveLeadStatus', $lead->id, 'qualified')
            ->assertOk();

        $lead->refresh();
        $this->assertSame('qualified', $lead->status);

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type'    => 'status_change',
        ]);

        $activity = LeadActivity::where('lead_id', $lead->id)->where('type', 'status_change')->first();
        $this->assertNotNull($activity);
        $this->assertSame(['from' => 'new', 'to' => 'qualified'], $activity->meta);
    }

    public function test_sales_manager_cannot_move_another_managers_lead_status(): void
    {
        $this->seedRoles();

        $owner = User::factory()->create();
        $owner->assignRole('sales-manager');
        $intruder = User::factory()->create();
        $intruder->assignRole('sales-manager');
        $source = $this->source();

        $lead = Lead::factory()->create([
            'manager_id' => $owner->id, 'source_id' => $source->id,
            'status' => 'new',
        ]);

        Livewire::actingAs($intruder)
            ->test(Index::class)
            ->call('moveLeadStatus', $lead->id, 'qualified')
            ->assertForbidden();

        $this->assertSame('new', $lead->fresh()->status);
        $this->assertDatabaseMissing('lead_activities', [
            'lead_id' => $lead->id,
            'type'    => 'status_change',
        ]);
    }

    public function test_moving_a_client_status_lead_is_a_silent_no_op(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        $lead = Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'status' => 'client',
        ]);

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->call('moveLeadStatus', $lead->id, 'qualified')
            ->assertOk();

        $this->assertSame('client', $lead->fresh()->status);
        $this->assertDatabaseMissing('lead_activities', [
            'lead_id' => $lead->id,
            'type'    => 'status_change',
        ]);
    }

    public function test_moving_to_an_invalid_status_is_a_silent_no_op(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        $lead = Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'status' => 'new',
        ]);

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->call('moveLeadStatus', $lead->id, 'bogus')
            ->assertOk();

        $this->assertSame('new', $lead->fresh()->status);
        $this->assertDatabaseMissing('lead_activities', [
            'lead_id' => $lead->id,
            'type'    => 'status_change',
        ]);
    }

    public function test_moving_to_the_same_status_is_a_silent_no_op_and_does_not_log_activity(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = $this->source();

        $lead = Lead::factory()->create([
            'manager_id' => $manager->id, 'source_id' => $source->id,
            'status' => 'new',
        ]);

        $countBefore = LeadActivity::where('lead_id', $lead->id)->count();

        Livewire::actingAs($manager)
            ->test(Index::class)
            ->call('moveLeadStatus', $lead->id, 'new')
            ->assertOk();

        $this->assertSame('new', $lead->fresh()->status);
        $this->assertSame($countBefore, LeadActivity::where('lead_id', $lead->id)->count());
    }
}
