<?php

namespace Tests\Feature\Tickets;

use App\Livewire\Admin\EquipmentRequests\Index;
use App\Livewire\Admin\EquipmentRequests\Show;
use App\Models\Support\EquipmentRequest;
use App\Models\User;
use Database\Factories\EquipmentRequestFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Covers the admin/internal side of Equipment Requests (status lifecycle,
 * manager assignment, permission checks on Admin\EquipmentRequests\Index/Show).
 * Creation below goes through the factory directly since that's sufficient
 * for exercising the admin-side behavior under test.
 *
 * Portal-side self-service creation (App\Livewire\Portal\EquipmentRequests\*)
 * is now covered separately in
 * Tests\Feature\Tickets\EquipmentRequestPortalCreationTest.
 */
class EquipmentRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    public function test_equipment_request_defaults_to_submitted_status(): void
    {
        $this->seedRoles();
        $request = EquipmentRequestFactory::new()->create();

        $this->assertSame('submitted', $request->status);
    }

    // ── status transitions: submitted -> under_review -> quoted -> closed ──

    public function test_tech_support_can_move_request_through_full_status_lifecycle(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $request = EquipmentRequestFactory::new()->create();

        $component = Livewire::actingAs($agent)->test(Show::class, ['equipmentRequest' => $request]);

        $component->call('changeStatus', 'under_review');
        $this->assertSame('under_review', $request->fresh()->status);

        $component->call('changeStatus', 'quoted');
        $this->assertSame('quoted', $request->fresh()->status);

        $component->call('changeStatus', 'closed');
        $this->assertSame('closed', $request->fresh()->status);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $request = EquipmentRequestFactory::new()->create();

        Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->call('changeStatus', 'bogus-status')
            ->assertStatus(422);

        $this->assertSame('submitted', $request->fresh()->status);
    }

    public function test_manager_can_be_assigned_to_request(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $request = EquipmentRequestFactory::new()->create();

        Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->set('assignManagerId', $manager->id)
            ->call('assignManager');

        $this->assertSame($manager->id, $request->fresh()->manager_id);
    }

    // ── permission checks (Show correctly gated; Index is not) ─────────────

    public function test_tech_support_can_view_equipment_request(): void
    {
        $this->seedRoles();
        $agent = User::factory()->create();
        $agent->assignRole('tech-support');
        $request = EquipmentRequestFactory::new()->create();

        Livewire::actingAs($agent)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->assertOk();
    }

    public function test_sales_manager_cannot_view_equipment_request(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        $request = EquipmentRequestFactory::new()->create();

        $this->assertFalse($sm->can('equipment-requests.view'));

        Livewire::actingAs($sm)
            ->test(Show::class, ['equipmentRequest' => $request])
            ->assertForbidden();
    }

    /**
     * BUG: App\Livewire\Admin\EquipmentRequests\Index has no authorization
     * check at all (no `mount()`, no `abort_unless`, no `$this->authorize()`),
     * unlike Show which correctly gates on `equipment-requests.view`. Per
     * config/permissions.php only super-admin/sales-director/tech-support hold
     * `equipment-requests.view` — sales-manager, catalog-manager and
     * accountant do not — yet any of them can open `/admin/equipment-requests`
     * (route middleware only checks the broad internal-role list) and see
     * every customer's equipment request (subject, customer name, budget).
     *
     * This test encodes the secure/expected behavior (sales-manager without
     * the permission should not see the list) and currently fails.
     *
     * Fix belongs to ticket-system (owns Equipment Requests per CLAUDE.md):
     * add an authorization check to Index, mirroring Show's `abort_unless`.
     */
    public function test_sales_manager_without_permission_cannot_see_equipment_requests_index(): void
    {
        $this->seedRoles();
        $sm = User::factory()->create();
        $sm->assignRole('sales-manager');
        EquipmentRequestFactory::new()->create(['subject' => 'Конфиденциальная заявка на оборудование']);

        Livewire::actingAs($sm)
            ->test(Index::class)
            ->assertDontSee('Конфиденциальная заявка на оборудование');
    }
}
