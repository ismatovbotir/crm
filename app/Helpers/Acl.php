<?php

namespace App\Helpers;

/**
 * RSG-CRM — Access Control helper.
 *
 * Унифицирует проверку прав по всему приложению.
 * Работает в трёх режимах:
 *   1. Preview-mode (Spatie не установлен, нет auth) — показывает всё в local env
 *   2. Auth без Spatie — все авторизованные видят всё (на этапе разработки)
 *   3. Production (Spatie установлен) — нормальная проверка через can()
 *
 * См. CLAUDE.md → "Контроль доступа: 3 уровня защиты".
 */
class Acl
{
    /**
     * Может ли текущий пользователь выполнить действие?
     *
     * Использование в Blade:
     *   @if(\App\Helpers\Acl::can('leads.create')) ... @endif
     *
     * Использование в PHP:
     *   if (Acl::can('quotes.send')) { ... }
     */
    public static function can(?string $permission): bool
    {
        // null = доступно всем
        if ($permission === null) {
            return true;
        }

        $user = auth()->user();

        // Preview mode: нет аутентификации, разрешаем всё в local env
        if (!$user) {
            return app()->environment('local')
                && !class_exists(\Spatie\Permission\Models\Role::class);
        }

        // Spatie Permission установлен — используем нормальную проверку
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->can($permission);
        }

        // Auth без Spatie — пускаем всех авторизованных (этап разработки)
        return true;
    }

    /**
     * Имеет ли пользователь хотя бы одну из ролей?
     */
    public static function hasAnyRole(array $roles): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        return false;
    }

    /**
     * Является ли пользователь внутренним сотрудником RSG (CRM-контур)?
     */
    public static function isInternal(): bool
    {
        return self::hasAnyRole([
            'super-admin', 'sales-director', 'sales-manager',
            'tech-support', 'catalog-manager', 'accountant',
        ]);
    }

    /**
     * Является ли пользователь клиентом (Portal-контур)?
     */
    public static function isClient(): bool
    {
        return self::hasAnyRole(['client-admin', 'client-user']);
    }
}
