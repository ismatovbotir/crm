<?php

namespace Tests\Feature\Access;

use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * HTTP-level regression pass over every "top nav" admin + portal route.
 *
 * Context: a previous critical bug meant `role:...` middleware was never
 * registered as an alias in bootstrap/app.php (Laravel 11+ style), so EVERY
 * authenticated request to /admin/* or /portal/* 500'd with
 * "Target class [role] does not exist." (see historical docblock on
 * Tests\Feature\Access\AccessControlTest). That has since been fixed. This
 * test exists purely as a regression guard so a future change to
 * bootstrap/app.php, route middleware, or a Livewire component's render()
 * cannot silently reintroduce a blanket 500 without a test failing.
 *
 * We assert `assertOk()` (200) rather than merely "not 500", since every one
 * of these routes should render successfully for an authorized demo user with
 * an otherwise-empty database.
 */
class RouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function makeInternalUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function makePortalUser(string $role): User
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->customers()->attach($customer->id, ['role' => $role === 'client-admin' ? 'owner' : 'viewer']);

        return $user;
    }

    // ── Admin (super-admin — has every permission, exercises all menu items) ──

    public function test_admin_dashboard_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin')->assertOk();
    }

    public function test_admin_leads_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/leads')->assertOk();
    }

    public function test_admin_customers_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/customers')->assertOk();
    }

    public function test_admin_quotes_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/quotes')->assertOk();
    }

    public function test_admin_invoices_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/invoices')->assertOk();
    }

    public function test_admin_catalog_products_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/catalog/products')->assertOk();
    }

    public function test_admin_catalog_categories_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/catalog/categories')->assertOk();
    }

    public function test_admin_tickets_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/tickets')->assertOk();
    }

    public function test_admin_equipment_requests_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/equipment-requests')->assertOk();
    }

    public function test_admin_sells_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/sells')->assertOk();
    }

    public function test_admin_reports_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'))->get('/admin/reports')->assertOk();
    }

    // ── Admin — one route per non-admin internal role, to catch role-specific breakage ──

    public function test_sales_director_can_reach_admin_dashboard(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('sales-director'))->get('/admin')->assertOk();
    }

    public function test_sales_manager_can_reach_admin_leads_index(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('sales-manager'))->get('/admin/leads')->assertOk();
    }

    public function test_tech_support_can_reach_admin_tickets_index(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('tech-support'))->get('/admin/tickets')->assertOk();
    }

    public function test_catalog_manager_can_reach_admin_products_index(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('catalog-manager'))->get('/admin/catalog/products')->assertOk();
    }

    public function test_accountant_can_reach_admin_invoices_index(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('accountant'))->get('/admin/invoices')->assertOk();
    }

    // ── Portal ──────────────────────────────────────────────────────────

    public function test_portal_dashboard_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-user'))->get('/portal')->assertOk();
    }

    public function test_portal_quotes_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-user'))->get('/portal/quotes')->assertOk();
    }

    public function test_portal_invoices_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-user'))->get('/portal/invoices')->assertOk();
    }

    public function test_portal_tickets_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-user'))->get('/portal/tickets')->assertOk();
    }

    public function test_portal_catalog_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-user'))->get('/portal/catalog')->assertOk();
    }

    public function test_portal_profile_index_is_reachable(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-admin'))->get('/portal/profile')->assertOk();
    }

    public function test_client_admin_can_reach_portal_dashboard(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makePortalUser('client-admin'))->get('/portal')->assertOk();
    }
}
