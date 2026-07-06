<?php

namespace Tests\Feature\Access;

use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * CRITICAL BUG (blocks almost every test in this class):
 *
 * `role:...` route middleware (Spatie Permission) is never actually registered
 * with the router. `app/Http/Kernel.php` still declares the alias
 * (`'role' => \Spatie\Permission\Middleware\RoleMiddleware::class`), but this
 * project's `bootstrap/app.php` uses the Laravel 11+ style
 * `Application::configure()->withMiddleware(function (Middleware $middleware) {...})`
 * bootstrap, which does NOT read `app/Http/Kernel.php` at all — that file is dead
 * code. Since the custom aliases ('role', 'permission', 'role_or_permission',
 * 'internal', 'client') are never passed to `$middleware->alias([...])` inside
 * `bootstrap/app.php`, every route using `role:...` (i.e. ALL of `/admin/*` and
 * `/portal/*`, see routes/web.php) throws
 * `Illuminate\Contracts\Container\BindingResolutionException: Target class [role]
 * does not exist.` for any request that reaches that middleware — which in practice
 * means every AUTHENTICATED request to `/admin` or `/portal` currently 500s in the
 * real running app (verified manually outside of tests: an authenticated user
 * hitting `/admin` gets an actual 500, not a 403). Guests still get redirected to
 * /login only because the 'auth' middleware (a Laravel built-in alias, always
 * registered) short-circuits the pipeline before 'role' is ever resolved.
 *
 * This is an application-breaking regression, most likely introduced when the
 * project was upgraded/rescaffolded to Laravel 13's bootstrap/app.php while
 * app/Http/Kernel.php was left over from Laravel 10. Fix belongs to backend-dev /
 * laravel-fullstack: register the aliases via
 * `$middleware->alias([...])` in `bootstrap/app.php`'s `withMiddleware()` closure
 * (or otherwise wire up Spatie's middleware for Laravel 11+), then delete the now
 * pointless `app/Http/Kernel.php`.
 *
 * Every test below that hits a real `/admin/*` or `/portal/*` route currently
 * ERRORs with "Target class [role] does not exist" as a direct consequence of this
 * one root cause — they are intentionally left failing/erroring to document the bug,
 * per QA policy (do not rewrite tests to hide a real product bug).
 */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    // ── Level 1: routes / guests ────────────────────────────────────────────

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_from_portal_dashboard(): void
    {
        $this->get('/portal')->assertRedirect('/login');
    }

    public function test_client_user_cannot_access_admin_area(): void
    {
        $this->seedRoles();

        $clientUser = User::factory()->create();
        $clientUser->assignRole('client-user');

        $this->actingAs($clientUser)->get('/admin')->assertForbidden();
    }

    public function test_internal_staff_cannot_access_customer_portal(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');

        $this->actingAs($manager)->get('/portal')->assertForbidden();
    }

    // ── Level 3: Policies — Leads ownership ─────────────────────────────────

    public function test_sales_manager_can_view_own_lead(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $source = LeadSource::create(['name' => 'Сайт', 'slug' => 'site', 'is_active' => true]);
        $lead = Lead::factory()->create(['manager_id' => $manager->id, 'source_id' => $source->id]);

        $this->actingAs($manager)->get('/admin/leads/'.$lead->id)->assertOk();
    }

    public function test_sales_manager_cannot_view_another_managers_lead(): void
    {
        $this->seedRoles();

        $owner = User::factory()->create();
        $owner->assignRole('sales-manager');
        $intruder = User::factory()->create();
        $intruder->assignRole('sales-manager');

        $source = LeadSource::create(['name' => 'Сайт', 'slug' => 'site', 'is_active' => true]);
        $lead = Lead::factory()->create(['manager_id' => $owner->id, 'source_id' => $source->id]);

        $this->actingAs($intruder)->get('/admin/leads/'.$lead->id)->assertForbidden();
    }

    public function test_sales_director_can_view_any_lead(): void
    {
        $this->seedRoles();

        $manager = User::factory()->create();
        $manager->assignRole('sales-manager');
        $director = User::factory()->create();
        $director->assignRole('sales-director');

        $source = LeadSource::create(['name' => 'Сайт', 'slug' => 'site', 'is_active' => true]);
        $lead = Lead::factory()->create(['manager_id' => $manager->id, 'source_id' => $source->id]);

        $this->actingAs($director)->get('/admin/leads/'.$lead->id)->assertOk();
    }

    public function test_leads_index_only_lists_own_leads_for_sales_manager(): void
    {
        $this->seedRoles();

        $me    = User::factory()->create();
        $me->assignRole('sales-manager');
        $other = User::factory()->create();
        $other->assignRole('sales-manager');

        $source = LeadSource::create(['name' => 'Сайт', 'slug' => 'site', 'is_active' => true]);
        $myLead    = Lead::factory()->create(['manager_id' => $me->id, 'source_id' => $source->id, 'name' => 'My Lead']);
        $otherLead = Lead::factory()->create(['manager_id' => $other->id, 'source_id' => $source->id, 'name' => 'Other Lead']);

        \Livewire\Livewire::actingAs($me)
            ->test(\App\Livewire\Admin\Leads\Index::class)
            ->assertSee('My Lead')
            ->assertDontSee('Other Lead');
    }

    // ── Level 3: Policies — Customers ownership (via customer_users pivot) ──

    public function test_sales_manager_can_view_customer_attached_to_them(): void
    {
        $this->seedRoles();

        $manager  = User::factory()->create();
        $manager->assignRole('sales-manager');
        $customer = Customer::factory()->create();
        $customer->users()->attach($manager->id, ['role' => 'manager']);

        $this->actingAs($manager)->get('/admin/customers/'.$customer->id)->assertOk();
    }

    public function test_sales_manager_cannot_view_customer_not_attached_to_them(): void
    {
        $this->seedRoles();

        $manager  = User::factory()->create();
        $manager->assignRole('sales-manager');
        $customer = Customer::factory()->create(); // not attached to $manager

        $this->actingAs($manager)->get('/admin/customers/'.$customer->id)->assertForbidden();
    }

    // ── BUG: Invoice Show has no ownership check (IDOR) ─────────────────────

    /**
     * BUG: App\Livewire\Admin\Invoices\Show::mount() never calls
     * `$this->authorize('view', $invoice)`, unlike Leads\Show and Quotes\Show
     * which both authorize on mount. App\Http\Controllers\Admin\InvoiceController::show()
     * also does not authorize. As a result, any authenticated internal user who can
     * pass the route-level `role:...` middleware (e.g. any sales-manager) can open
     * `/admin/invoices/{invoice}` for an invoice belonging to a DIFFERENT manager and
     * see its financial details (totals, payments), even though
     * App\Policies\InvoicePolicy::view() implements the correct "own invoices only"
     * rule and Invoices\Index already filters correctly by manager_id.
     *
     * This test encodes the SECURE expected behavior (403 for a non-owner) and
     * currently fails because the real response is 200.
     *
     * Fix belongs to backend-dev / laravel-fullstack: add
     * `$this->authorize('view', $invoice);` to Invoices\Show::mount().
     */
    public function test_sales_manager_cannot_view_another_managers_invoice(): void
    {
        $this->seedRoles();

        $owner = User::factory()->create();
        $owner->assignRole('sales-manager');
        $intruder = User::factory()->create();
        $intruder->assignRole('sales-manager');

        $invoice = Invoice::factory()->create(['manager_id' => $owner->id]);

        $this->actingAs($intruder)->get('/admin/invoices/'.$invoice->id)->assertForbidden();
    }
}
