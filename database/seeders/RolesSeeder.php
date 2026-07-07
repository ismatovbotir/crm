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
 *
 * Baseline vs ручные правки (UI /admin/settings/roles):
 * `config/permissions.php` остаётся гарантированным МИНИМУМОМ (baseline) для каждой
 * роли — сидер никогда не даст роли "забыть" permission, описанный в конфиге. Но он
 * больше не является жёстким сбросом при обычном `migrate:fresh --seed`:
 *
 *   - Роль СОЗДАНА только что этим запуском сидера (`wasRecentlyCreated === true`,
 *     терять нечего) → `syncPermissions()` — точное соответствие конфигу.
 *   - Роль УЖЕ СУЩЕСТВОВАЛА (могла нести ручные правки через UI управления ролями,
 *     /admin/settings/roles) → `givePermissionTo()` — аддитивный merge: гарантирует
 *     наличие всех permissions из конфига, но НИКОГДА не отбирает то, что было
 *     добавлено вручную через UI.
 *
 * Trade-off (осознанный компромисс): если permission позже УДАЛЯЕТСЯ из
 * config/permissions.php для какой-то роли, сидер теперь НЕ отзовёт его у ролей,
 * которые уже успели его получить в БД (в отличие от старого поведения
 * syncPermissions, которое стирало бы его). Чтобы полностью убрать permission у
 * существующей роли, это нужно сделать вручную (через UI или tinker) либо явно
 * пересоздать роль.
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

            // Роль только что создана этим запуском сидера — терять нечего,
            // применяем конфиг как точный snapshot. Роль уже существовала —
            // могла нести ручные правки через UI (/admin/settings/roles),
            // поэтому только добавляем недостающие permissions, не отбирая лишние.
            $isNewRole = $role->wasRecentlyCreated;

            $rolePermissions = $roleConfig['permissions'] ?? [];

            // "*" означает все permissions
            if (in_array('*', $rolePermissions, true)) {
                $isNewRole ? $role->syncPermissions($allPermissions) : $role->givePermissionTo($allPermissions);
                $this->command->info("✓ Роль {$roleName}: все permissions (".count($allPermissions).')');
                continue;
            }

            $isNewRole ? $role->syncPermissions($rolePermissions) : $role->givePermissionTo($rolePermissions);
            $this->command->info("✓ Роль {$roleName}: ".count($rolePermissions).' permissions'.($isNewRole ? '' : ' (аддитивно, baseline)'));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
