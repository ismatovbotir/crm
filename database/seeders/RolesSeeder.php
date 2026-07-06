<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Создаёт роли и permissions из config/permissions.php
 *
 * Запуск: php artisan db:seed --class=RolesSeeder
 *
 * Идемпотентен — можно запускать многократно, не создаст дубликаты.
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Сброс кеша Spatie перед запуском
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $config = config('permissions');
        $permissionGroups = $config['permissions'] ?? [];
        $roles = $config['roles'] ?? [];

        // 1. Создаём все permissions
        $allPermissions = [];
        foreach ($permissionGroups as $group => $perms) {
            foreach ($perms as $key => $description) {
                Permission::firstOrCreate(
                    ['name' => $key, 'guard_name' => 'web']
                );
                $allPermissions[] = $key;
            }
        }

        $this->command->info('✓ Permissions: '.count($allPermissions).' штук');

        // 2. Создаём роли и привязываем permissions
        foreach ($roles as $roleName => $roleConfig) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $roleConfig['guard'] ?? 'web']
            );

            $rolePermissions = $roleConfig['permissions'] ?? [];

            // "*" означает все permissions
            if (in_array('*', $rolePermissions, true)) {
                $role->syncPermissions($allPermissions);
                $this->command->info("✓ Роль {$roleName}: все permissions (".count($allPermissions).')');
                continue;
            }

            $role->syncPermissions($rolePermissions);
            $this->command->info("✓ Роль {$roleName}: ".count($rolePermissions).' permissions');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
