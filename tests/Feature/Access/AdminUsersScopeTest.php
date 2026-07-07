<?php

namespace Tests\Feature\Access;

use App\Livewire\Admin\Settings\Users;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the fix to `App\Livewire\Admin\Settings\Users`:
 * the internal "Users" admin screen (`/admin/settings/users`) used to list
 * EVERY `User` row — including Customer Portal accounts (`client-admin` /
 * `client-user`) — and offered every role (including the two client roles)
 * in the role-assignment dropdown. `laravel-fullstack` fixed this by:
 *   - adding `User::INTERNAL_ROLES` as the single source of truth
 *   - `User::scopeManagers()` filtering by that constant (used by `users()`)
 *   - `Users::availableRoles()` intersecting `config('permissions.roles')`
 *     against `User::INTERNAL_ROLES`
 *
 * IMPORTANT — why these tests call the Livewire component directly instead
 * of going through `Livewire::test()` / HTTP:
 *
 * `resources/views/livewire/admin/settings/users.blade.php` has a PRE-EXISTING,
 * unrelated bug (present since the very first commit of this file, `c27788f`,
 * long before today's scoping fix): for every row rendered in the table, it
 * does:
 *   $roleLabels = collect(config('permissions.roles'))
 *       ->mapWithKeys(fn ($v, $k) => [$k => $v['label']]);
 * but `config('permissions.roles')` entries only ever have `guard` and
 * `permissions` keys — there is no `label` key — so this throws
 * `ErrorException: Undefined array key "label"` and the request 500s. This
 * fires for ANY row whose user has a role assigned, which in practice is
 * every row (including the authenticated super-admin viewing the page).
 * See `test_admin_settings_users_page_currently_errors_due_to_blade_bug()`
 * below, which documents this as a known, currently-failing bug — it is
 * NOT part of today's fix and belongs to `admin-bi-developer` (Blade-only
 * change; `Users::availableRoles()` / `Users::ROLE_LABELS` already computes
 * correct labels and the view should reuse `$this->availableRoles` instead
 * of re-deriving a broken mapping).
 *
 * Because that bug currently makes the full HTTP/Livewire render path
 * unusable, the tests for the *scoping* fix below call `users()` /
 * `availableRoles()` / `save()` / `toggleActive()` directly on a plain
 * instantiated `Users` component instance. This is a deliberate, valid way
 * to exercise the component's PHP logic in isolation from its (broken)
 * view — Livewire components are plain classes, and none of the methods
 * under test here depend on Livewire's request/render lifecycle.
 */
class AdminUsersScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(\Database\Seeders\RolesSeeder::class);
    }

    protected function makeInternalUser(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    // ── users() computed / managers() scope ─────────────────────────────

    public function test_users_list_excludes_client_admin_and_client_user(): void
    {
        $this->seedRoles();

        $admin = $this->makeInternalUser('super-admin', ['name' => 'Internal Admin']);
        $manager = $this->makeInternalUser('sales-manager', ['name' => 'Internal Manager']);
        $techSupport = $this->makeInternalUser('tech-support', ['name' => 'Internal Support']);
        $catalogManager = $this->makeInternalUser('catalog-manager', ['name' => 'Internal Catalog']);
        $accountant = $this->makeInternalUser('accountant', ['name' => 'Internal Accountant']);
        $salesDirector = $this->makeInternalUser('sales-director', ['name' => 'Internal Director']);

        $clientAdmin = User::factory()->create(['name' => 'Portal Client Admin']);
        $clientAdmin->assignRole('client-admin');
        $clientUser = User::factory()->create(['name' => 'Portal Client User']);
        $clientUser->assignRole('client-user');

        $component = new Users();
        $ids = $component->users()->pluck('id')->all();

        $this->assertEqualsCanonicalizing(
            [$admin->id, $manager->id, $techSupport->id, $catalogManager->id, $accountant->id, $salesDirector->id],
            $ids
        );
        $this->assertNotContains($clientAdmin->id, $ids);
        $this->assertNotContains($clientUser->id, $ids);
    }

    public function test_users_list_is_empty_when_only_client_users_exist(): void
    {
        $this->seedRoles();

        $clientAdmin = User::factory()->create();
        $clientAdmin->assignRole('client-admin');
        $clientUser = User::factory()->create();
        $clientUser->assignRole('client-user');

        $component = new Users();

        $this->assertSame(0, $component->users()->total());
    }

    public function test_users_list_search_filter_still_excludes_client_roles(): void
    {
        $this->seedRoles();

        $manager = $this->makeInternalUser('sales-manager', ['name' => 'Findme Manager']);
        $clientUser = User::factory()->create(['name' => 'Findme Client']);
        $clientUser->assignRole('client-user');

        $component = new Users();
        $component->search = 'Findme';

        $ids = $component->users()->pluck('id')->all();

        $this->assertSame([$manager->id], $ids);
    }

    // ── availableRoles() ─────────────────────────────────────────────────

    public function test_available_roles_never_includes_client_roles(): void
    {
        $this->seedRoles();

        $component = new Users();
        $roles = $component->availableRoles();

        $this->assertArrayNotHasKey('client-admin', $roles);
        $this->assertArrayNotHasKey('client-user', $roles);
        $this->assertEqualsCanonicalizing(User::INTERNAL_ROLES, array_keys($roles));
    }

    public function test_available_roles_have_human_readable_non_empty_labels(): void
    {
        $this->seedRoles();

        $component = new Users();
        $roles = $component->availableRoles();

        foreach (User::INTERNAL_ROLES as $role) {
            $this->assertArrayHasKey($role, $roles);
            $this->assertNotSame('', trim($roles[$role]));
        }
    }

    // ── CRUD happy path (called directly — see class docblock) ──────────

    public function test_creating_internal_user_with_internal_role_succeeds(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'));

        $component = new Users();
        $component->name = 'New Manager';
        $component->email = 'new.manager@example.com';
        $component->password = 'secret123';
        $component->role = 'sales-manager';
        $component->isActive = true;
        $component->save();

        $this->assertDatabaseHas('users', [
            'email' => 'new.manager@example.com',
            'name' => 'New Manager',
            'is_active' => true,
        ]);

        $created = User::where('email', 'new.manager@example.com')->firstOrFail();
        $this->assertTrue($created->hasRole('sales-manager'));
    }

    public function test_editing_internal_user_updates_fields_and_role(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'));

        $target = $this->makeInternalUser('sales-manager', [
            'name' => 'Old Name',
            'email' => 'old.email@example.com',
        ]);

        $component = new Users();
        $component->openEdit($target->id);

        $this->assertSame('Old Name', $component->name);
        $this->assertSame('old.email@example.com', $component->email);
        $this->assertSame('sales-manager', $component->role);

        $component->name = 'New Name';
        $component->email = 'new.email@example.com';
        $component->role = 'accountant';
        $component->password = '';
        $component->save();

        $target->refresh();
        $this->assertSame('New Name', $target->name);
        $this->assertSame('new.email@example.com', $target->email);
        $this->assertTrue($target->hasRole('accountant'));
        $this->assertFalse($target->hasRole('sales-manager'));
    }

    public function test_toggle_active_flips_is_active_flag(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'));

        $target = $this->makeInternalUser('sales-manager', ['is_active' => true]);

        $component = new Users();
        $component->toggleActive($target->id);
        $this->assertFalse($target->refresh()->is_active);

        $component->toggleActive($target->id);
        $this->assertTrue($target->refresh()->is_active);
    }

    // ── Known gap (not a blocker — documented per QA instructions) ───────

    /**
     * KNOWN GAP, not fixed here: `Users::rules()` validates `role` as
     * `required|string`, not `in:` a whitelist. The UI dropdown
     * (`availableRoles()`) never *offers* `client-admin`/`client-user`, so
     * this isn't reachable through the normal form — but nothing stops a
     * direct property write (e.g. a tampered Livewire payload, or any other
     * code path that sets `$component->role` and calls `save()`) from
     * assigning a client-only role to what is supposed to be an internal-only
     * user record. This test documents the current (permissive) behavior;
     * it does not assert a requirement was violated. If tightened, the
     * likely fix is `'role' => ['required', 'string', Rule::in(User::INTERNAL_ROLES)]`
     * in `Users::rules()` — that belongs to `laravel-fullstack`.
     */
    public function test_save_does_not_reject_a_client_role_assigned_via_direct_property_write(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeInternalUser('super-admin'));

        $component = new Users();
        $component->name = 'Sneaky User';
        $component->email = 'sneaky@example.com';
        $component->password = 'secret123';
        $component->role = 'client-user'; // never offered by availableRoles(), but not blocked by validation
        $component->isActive = true;
        $component->save();

        $created = User::where('email', 'sneaky@example.com')->firstOrFail();
        $this->assertTrue($created->hasRole('client-user'));
    }

    // ── Route-level access control (safe: never reaches the buggy render) ──

    public function test_guest_is_redirected_to_login_from_settings_users(): void
    {
        $this->get('/admin/settings/users')->assertRedirect('/login');
    }

    public function test_non_super_admin_internal_role_cannot_reach_settings_users(): void
    {
        $this->seedRoles();

        $this->actingAs($this->makeInternalUser('sales-manager'))
            ->get('/admin/settings/users')
            ->assertForbidden();
    }

    public function test_client_user_cannot_reach_settings_users(): void
    {
        $this->seedRoles();

        $this->actingAs($this->makeInternalUser('client-user'))
            ->get('/admin/settings/users')
            ->assertForbidden();
    }

    // ── Documents the pre-existing, unrelated Blade bug (see class docblock) ──

    /**
     * BUG (pre-existing, NOT introduced by today's scoping fix — present since
     * the file was first committed): `resources/views/livewire/admin/settings/users.blade.php`
     * reads `$v['label']` off `config('permissions.roles')` entries, which only
     * ever contain `guard` and `permissions` keys. This throws
     * `ErrorException: Undefined array key "label"` for any row rendered with
     * a role — i.e. in practice for almost every real visit to this screen,
     * since the authenticated super-admin viewing the page is themselves an
     * internal user with a role and appears in the very list being rendered.
     *
     * This test encodes the expected behavior (200 OK) and currently fails
     * with a 500. Fix belongs to `admin-bi-developer` (Blade-only change):
     * reuse the already-correct `$this->availableRoles` (or the component's
     * private `ROLE_LABELS` map) instead of re-deriving a broken mapping
     * inline in the view.
     */
    public function test_admin_settings_users_page_currently_errors_due_to_blade_bug(): void
    {
        $this->seedRoles();

        $this->actingAs($this->makeInternalUser('super-admin'))
            ->get('/admin/settings/users')
            ->assertOk();
    }
}
