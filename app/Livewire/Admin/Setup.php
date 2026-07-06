<?php

namespace App\Livewire\Admin;

use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class Setup extends Component
{
    public bool $done = false;
    public string $log = '';

    public function mount(): void
    {
        // If roles already seeded, redirect to dashboard
        if (Role::count() > 0) {
            $this->redirect(route('admin.dashboard'));
        }
    }

    public function initialize(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $config     = config('permissions');
        $groups     = $config['permissions'] ?? [];
        $rolesConfig = $config['roles'] ?? [];

        // Create all permissions
        $allPermissions = [];
        foreach ($groups as $group => $perms) {
            foreach ($perms as $key => $description) {
                Permission::firstOrCreate(['name' => $key, 'guard_name' => 'web']);
                $allPermissions[] = $key;
            }
        }

        // Create roles and attach permissions
        foreach ($rolesConfig as $roleName => $roleConfig) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $roleConfig['guard'] ?? 'web',
            ]);

            $rolePermissions = $roleConfig['permissions'] ?? [];
            if (in_array('*', $rolePermissions, true)) {
                $role->syncPermissions($allPermissions);
            } else {
                $role->syncPermissions($rolePermissions);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Promote current user to super-admin
        $user = auth()->user();
        $user->syncRoles(['super-admin']);

        $this->log = "Создано ролей: " . count($rolesConfig) . "\n"
                   . "Создано прав: " . count($allPermissions) . "\n"
                   . "Роль super-admin назначена: {$user->name} ({$user->email})";

        $this->done = true;
    }

    public function render()
    {
        return view('livewire.admin.setup');
    }
}
