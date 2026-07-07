<?php

namespace App\Livewire\Admin\Settings;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class Roles extends Component
{
    /**
     * Роли, которые нельзя редактировать через этот интерфейс:
     * - super-admin: wildcard-роль, всегда полный доступ, никогда не редактируется здесь.
     * - client-admin / client-user: доступ ownership-based (портал), а не через module permissions.
     *
     * Показываются в списке (read-only), чтобы не сбивать с толку, почему их нет,
     * но любая попытка записи (даже прямым вызовом Livewire-метода) отклоняется server-side.
     */
    private const LOCKED_ROLES = ['super-admin', 'client-admin', 'client-user'];

    /**
     * Человекочитаемые метки ролей (config/permissions.php не содержит label/description).
     */
    private const ROLE_LABELS = [
        'super-admin'     => 'Супер-администратор',
        'sales-director'  => 'Директор по продажам',
        'sales-manager'   => 'Менеджер по продажам',
        'tech-support'    => 'Техническая поддержка',
        'catalog-manager' => 'Менеджер каталога',
        'accountant'      => 'Бухгалтер',
        'client-admin'    => 'Клиент (администратор компании)',
        'client-user'     => 'Клиент (сотрудник)',
    ];

    /**
     * Текущее (редактируемое в UI, ещё не сохранённое) состояние чекбоксов.
     * Структура: ['<role-name>' => ['<permission.key>', ...]]
     *
     * Каждая группа чекбоксов в Blade биндится на `selectedPermissions.<role-name>`
     * (стандартный Livewire-паттерн: несколько чекбоксов с одинаковым wire:model
     * и разными value автоматически формируют/меняют массив).
     */
    public array $selectedPermissions = [];

    public function mount(): void
    {
        // Defense-in-depth: страница уже закрыта `role_or_permission:super-admin|settings.roles`
        // middleware на роуте, дублируем ту же проверку здесь. Гейтинг по permission (а не
        // жёстко по роли super-admin) безопасен именно потому, что super-admin/client-admin/
        // client-user — LOCKED_ROLES ниже, их нельзя отредактировать через эту страницу даже
        // тому, кому делегирован доступ через settings.roles — так что случайно отозвать
        // права у самого себя/заблокировать всех через эту же страницу невозможно.
        abort_unless(
            auth()->user()?->hasRole('super-admin') || auth()->user()?->can('settings.roles'),
            403
        );

        foreach (Role::all() as $role) {
            $this->selectedPermissions[$role->name] = $role->getPermissionNames()->toArray();
        }
    }

    #[Computed]
    public function roles()
    {
        return Role::query()->orderBy('name')->get();
    }

    /**
     * Permissions, сгруппированные точно как в config('permissions.permissions'):
     * ['<group>' => ['<permission.key>' => '<человекочитаемое описание>']]
     */
    #[Computed]
    public function permissionGroups(): array
    {
        return config('permissions.permissions', []);
    }

    public function roleLabel(string $roleName): string
    {
        return self::ROLE_LABELS[$roleName] ?? \Illuminate\Support\Str::headline($roleName);
    }

    public function isLocked(string $roleName): bool
    {
        return in_array($roleName, self::LOCKED_ROLES, true);
    }

    /**
     * Сохраняет права для одной роли из текущего состояния $selectedPermissions[$roleName].
     *
     * Server-side guard (defense in depth): даже если кто-то вызовет этот метод напрямую
     * (Livewire action call) с именем locked-роли, действие будет отклонено с 403 —
     * не полагаемся только на то, что Blade не покажет чекбоксы/кнопку для этой роли.
     */
    public function savePermissions(string $roleName): void
    {
        abort_unless(
            auth()->user()?->hasRole('super-admin') || auth()->user()?->can('settings.roles'),
            403
        );

        if ($this->isLocked($roleName)) {
            abort(403, 'Эта роль не редактируется через данный интерфейс.');
        }

        $role = Role::where('name', $roleName)->firstOrFail();

        // Белый список: сохраняем только permissions, реально описанные в config/permissions.php,
        // чтобы через манипуляцию payload'ом нельзя было присвоить роли произвольную строку-permission.
        $validPermissions = collect(config('permissions.permissions', []))
            ->flatMap(fn (array $group) => array_keys($group))
            ->all();

        $permissions = array_values(array_intersect(
            $this->selectedPermissions[$roleName] ?? [],
            $validPermissions
        ));

        $role->syncPermissions($permissions);

        // Держим локальное состояние в синхронизации с БД после сохранения.
        $this->selectedPermissions[$roleName] = $permissions;

        unset($this->roles);

        session()->flash('success', "Права роли «{$this->roleLabel($roleName)}» обновлены.");
    }

    public function render()
    {
        return view('livewire.admin.settings.roles');
    }
}
