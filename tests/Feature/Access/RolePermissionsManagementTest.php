<?php

namespace Tests\Feature\Access;

use App\Livewire\Admin\Settings\Roles;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

/**
 * Coverage for `/admin/settings/roles` (App\Livewire\Admin\Settings\Roles):
 * viewing/editing the permission set of each internal role, backed by
 * `config/permissions.php` as the whitelist and `database/seeders/RolesSeeder.php`'s
 * additive re-seed semantics.
 *
 * Access is gated by `role_or_permission:super-admin|settings.roles` -- super-admin
 * always has it (via its '*' wildcard), and it can also be delegated to any other
 * role by granting `settings.roles` through this same page. This is safe because
 * super-admin/client-admin/client-user rows are permanently locked/uneditable
 * server-side (see isLocked()), so there's always a way back in even if
 * `settings.roles` is accidentally revoked from every other role.
 */
class RolePermissionsManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRoles(): void
    {
        $this->seed(RolesSeeder::class);
    }

    protected function makeUser(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function allConfiguredPermissionNames(): array
    {
        return collect(config('permissions.permissions', []))
            ->flatMap(fn (array $group) => array_keys($group))
            ->all();
    }

    // ── Level 1: route access — super-admin only ─────────────────────────

    public function test_super_admin_can_access_roles_settings_page(): void
    {
        $this->seedRoles();

        $this->actingAs($this->makeUser('super-admin'))
            ->get('/admin/settings/roles')
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_settings_roles(): void
    {
        $this->get('/admin/settings/roles')->assertRedirect('/login');
    }

    #[DataProvider('nonSuperAdminRoleProvider')]
    public function test_non_super_admin_role_cannot_reach_settings_roles(string $role): void
    {
        $this->seedRoles();

        $this->actingAs($this->makeUser($role))
            ->get('/admin/settings/roles')
            ->assertForbidden();
    }

    public static function nonSuperAdminRoleProvider(): array
    {
        return [
            'sales-director' => ['sales-director'],
            'sales-manager' => ['sales-manager'],
            'tech-support' => ['tech-support'],
            'catalog-manager' => ['catalog-manager'],
            'accountant' => ['accountant'],
            'client-admin' => ['client-admin'],
            'client-user' => ['client-user'],
        ];
    }

    public function test_role_granted_settings_roles_permission_can_access_the_page(): void
    {
        $this->seedRoles();

        // None of these roles have settings.roles by default -- confirm the
        // baseline 403, then grant it and confirm access opens up.
        $director = $this->makeUser('sales-director');
        $this->actingAs($director)->get('/admin/settings/roles')->assertForbidden();

        SpatieRole::findByName('sales-director')->givePermissionTo('settings.roles');

        $this->actingAs($director)->get('/admin/settings/roles')->assertOk();
    }

    public function test_role_granted_settings_roles_permission_can_toggle_other_roles_permissions(): void
    {
        $this->seedRoles();

        $director = $this->makeUser('sales-director');
        SpatieRole::findByName('sales-director')->givePermissionTo('settings.roles');

        $this->assertFalse(SpatieRole::findByName('sales-manager')->hasPermissionTo('reports.sales'));

        $component = Livewire::actingAs($director)->test(Roles::class);
        $current = $component->get('selectedPermissions.sales-manager');

        $component
            ->set('selectedPermissions.sales-manager', array_merge($current, ['reports.sales']))
            ->call('savePermissions', 'sales-manager');

        $this->assertTrue(SpatieRole::findByName('sales-manager')->fresh()->hasPermissionTo('reports.sales'));
    }

    // ── Toggle permission + persistence ──────────────────────────────────

    public function test_super_admin_can_add_a_new_permission_to_sales_manager_role(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        // Baseline sanity: 'reports.sales' is NOT in sales-manager's config permission list.
        $this->assertFalse(SpatieRole::findByName('sales-manager')->hasPermissionTo('reports.sales'));

        $component = Livewire::actingAs($admin)->test(Roles::class);

        $current = $component->get('selectedPermissions.sales-manager');
        $this->assertIsArray($current);
        $this->assertNotContains('reports.sales', $current);

        $component
            ->set('selectedPermissions.sales-manager', array_merge($current, ['reports.sales']))
            ->call('savePermissions', 'sales-manager');

        $this->assertTrue(SpatieRole::findByName('sales-manager')->fresh()->hasPermissionTo('reports.sales'));

        // Baseline permissions untouched by the addition.
        $this->assertTrue(SpatieRole::findByName('sales-manager')->hasPermissionTo('leads.view'));
        $this->assertTrue(SpatieRole::findByName('sales-manager')->hasPermissionTo('quotes.send'));

        // A freshly mounted instance of the component reflects the persisted change.
        $reloaded = Livewire::actingAs($admin)->test(Roles::class);
        $this->assertContains('reports.sales', $reloaded->get('selectedPermissions.sales-manager'));
    }

    public function test_super_admin_can_remove_an_existing_permission_from_sales_manager_role(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $this->assertTrue(SpatieRole::findByName('sales-manager')->hasPermissionTo('leads.delete'));

        $component = Livewire::actingAs($admin)->test(Roles::class);
        $current = $component->get('selectedPermissions.sales-manager');
        $withoutDelete = array_values(array_diff($current, ['leads.delete']));

        $component
            ->set('selectedPermissions.sales-manager', $withoutDelete)
            ->call('savePermissions', 'sales-manager');

        $this->assertFalse(SpatieRole::findByName('sales-manager')->fresh()->hasPermissionTo('leads.delete'));
        // Other baseline permissions remain.
        $this->assertTrue(SpatieRole::findByName('sales-manager')->hasPermissionTo('leads.view'));

        $reloaded = Livewire::actingAs($admin)->test(Roles::class);
        $this->assertNotContains('leads.delete', $reloaded->get('selectedPermissions.sales-manager'));
    }

    // ── Locked roles cannot be edited even via a direct method call ──────

    public function test_save_permissions_rejects_super_admin_role_and_leaves_permissions_untouched(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $allPermissions = $this->allConfiguredPermissionNames();
        $before = SpatieRole::findByName('super-admin')->fresh()->getPermissionNames()->all();
        $this->assertEqualsCanonicalizing($allPermissions, $before);

        Livewire::actingAs($admin)
            ->test(Roles::class)
            ->set('selectedPermissions.super-admin', [])
            ->call('savePermissions', 'super-admin')
            ->assertForbidden();

        $after = SpatieRole::findByName('super-admin')->fresh()->getPermissionNames()->all();
        $this->assertEqualsCanonicalizing($allPermissions, $after);
    }

    public function test_save_permissions_rejects_client_admin_role_and_leaves_it_with_no_permissions(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $this->assertSame(0, SpatieRole::findByName('client-admin')->permissions()->count());

        Livewire::actingAs($admin)
            ->test(Roles::class)
            ->set('selectedPermissions.client-admin', ['leads.view', 'customers.view'])
            ->call('savePermissions', 'client-admin')
            ->assertForbidden();

        $this->assertSame(0, SpatieRole::findByName('client-admin')->fresh()->permissions()->count());
    }

    public function test_save_permissions_rejects_client_user_role_and_leaves_it_with_no_permissions(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        $this->assertSame(0, SpatieRole::findByName('client-user')->permissions()->count());

        Livewire::actingAs($admin)
            ->test(Roles::class)
            ->set('selectedPermissions.client-user', ['leads.view'])
            ->call('savePermissions', 'client-user')
            ->assertForbidden();

        $this->assertSame(0, SpatieRole::findByName('client-user')->fresh()->permissions()->count());
    }

    // ── Locked roles still render read-only, no crash ────────────────────

    public function test_component_renders_all_eight_roles_including_locked_client_roles(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        Livewire::actingAs($admin)
            ->test(Roles::class)
            ->assertOk()
            ->assertSee('Супер-администратор')
            ->assertSee('Директор по продажам')
            ->assertSee('Менеджер по продажам')
            ->assertSee('Техническая поддержка')
            ->assertSee('Менеджер каталога')
            ->assertSee('Бухгалтер')
            ->assertSee('Клиент (администратор компании)')
            ->assertSee('Клиент (сотрудник)');
    }

    public function test_is_locked_correctly_identifies_the_three_locked_roles(): void
    {
        $this->seedRoles();
        $this->actingAs($this->makeUser('super-admin'));

        $component = new Roles();

        $this->assertTrue($component->isLocked('super-admin'));
        $this->assertTrue($component->isLocked('client-admin'));
        $this->assertTrue($component->isLocked('client-user'));

        $this->assertFalse($component->isLocked('sales-director'));
        $this->assertFalse($component->isLocked('sales-manager'));
        $this->assertFalse($component->isLocked('tech-support'));
        $this->assertFalse($component->isLocked('catalog-manager'));
        $this->assertFalse($component->isLocked('accountant'));
    }

    // ── Happy-path smoke: permission groups render without error ─────────

    public function test_permission_groups_render_with_group_headers_and_no_error(): void
    {
        $this->seedRoles();
        $admin = $this->makeUser('super-admin');

        Livewire::actingAs($admin)
            ->test(Roles::class)
            ->assertOk()
            ->assertSee('Лиды')
            ->assertSee('Клиенты')
            ->assertSee('КП')
            ->assertSee('Инвойсы')
            ->assertSee('Тикеты')
            ->assertSee('Настройки');
    }

    // ── RolesSeeder additive re-seed semantics (regression guard) ─────────

    public function test_reseeding_preserves_manually_granted_permission_via_ui(): void
    {
        $this->seedRoles();

        $role = SpatieRole::findByName('sales-manager');

        // 'reports.sales' is not part of sales-manager's config baseline.
        $this->assertFalse($role->hasPermissionTo('reports.sales'));

        // Simulate a manual grant made through /admin/settings/roles.
        $role->givePermissionTo('reports.sales');
        $this->assertTrue($role->fresh()->hasPermissionTo('reports.sales'));

        // Re-running the seeder must not strip the manually granted permission...
        $this->seedRoles();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('reports.sales'));

        // ...and must still guarantee every baseline permission from the config.
        $configuredPermissions = config('permissions.roles.sales-manager.permissions');
        foreach ($configuredPermissions as $permission) {
            $this->assertTrue(
                $role->hasPermissionTo($permission),
                "Expected sales-manager to retain baseline permission '{$permission}' after re-seed."
            );
        }
    }

    public function test_fresh_seed_run_gives_a_role_exactly_the_configured_permissions_no_more(): void
    {
        $this->seedRoles();

        $role = SpatieRole::findByName('sales-manager');
        $configuredPermissions = config('permissions.roles.sales-manager.permissions');

        $this->assertEqualsCanonicalizing(
            $configuredPermissions,
            $role->getPermissionNames()->all()
        );
    }
}
