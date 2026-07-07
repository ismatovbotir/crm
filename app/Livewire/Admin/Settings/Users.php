<?php

namespace App\Livewire\Admin\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Users extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';

    public bool $showForm = false;
    public ?int $editingUserId = null;

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public bool $isActive = true;

    protected function rules(): array
    {
        $uniqueEmail = $this->editingUserId
            ? 'unique:users,email,' . $this->editingUserId
            : 'unique:users,email';

        return [
            'name'     => 'required|string|max:255',
            'email'    => "required|email|{$uniqueEmail}",
            'password' => $this->editingUserId ? 'nullable|min:6' : 'required|min:6',
            'role'     => 'required|string',
            'isActive' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'     => 'Введите имя',
            'email.required'    => 'Введите email',
            'email.unique'      => 'Этот email уже занят',
            'password.required' => 'Введите пароль',
            'password.min'      => 'Пароль минимум 6 символов',
            'role.required'     => 'Выберите роль',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Человекочитаемые метки внутренних ролей (config/permissions.php не
     * содержит label/description для ролей — только guard и permissions).
     */
    private const ROLE_LABELS = [
        'super-admin'     => 'Супер-администратор',
        'sales-director'  => 'Директор по продажам',
        'sales-manager'   => 'Менеджер по продажам',
        'tech-support'    => 'Техническая поддержка',
        'catalog-manager' => 'Менеджер каталога',
        'accountant'      => 'Бухгалтер',
    ];

    #[Computed]
    public function users()
    {
        return User::query()
            ->managers()
            ->with('roles')
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            }))
            ->when($this->roleFilter, fn ($q) => $q->whereHas('roles', fn ($q) => $q->where('name', $this->roleFilter)))
            ->orderBy('name')
            ->paginate(20);
    }

    #[Computed]
    public function availableRoles(): array
    {
        // Только внутренние роли (сотрудники RSG) — клиентские роли портала
        // (client-admin/client-user) сюда не попадают, они управляются
        // отдельно через Customers\Show::attachUser()/detachUser().
        $config = array_intersect_key(config('permissions.roles', []), array_flip(User::INTERNAL_ROLES));

        $roles = [];
        foreach ($config as $slug => $data) {
            $roles[$slug] = self::ROLE_LABELS[$slug] ?? \Illuminate\Support\Str::headline($slug);
        }
        return $roles;
    }

    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'role', 'isActive', 'editingUserId']);
        $this->isActive = true;
        $this->showForm = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name     = $user->name;
        $this->email    = $user->email;
        $this->password = '';
        $this->role     = $user->getRoleNames()->first() ?? '';
        $this->isActive = $user->is_active;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['name', 'email', 'password', 'role', 'isActive', 'editingUserId']);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $data = [
                'name'      => $this->name,
                'email'     => $this->email,
                'is_active' => $this->isActive,
            ];
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }
            $user->update($data);
        } else {
            $user = User::create([
                'name'                  => $this->name,
                'email'                 => $this->email,
                'password'              => Hash::make($this->password),
                'is_active'             => $this->isActive,
                'email_verified_at'     => now(),
            ]);
        }

        $user->syncRoles([$this->role]);

        $this->closeForm();
        session()->flash('success', $this->editingUserId ? 'Пользователь обновлён.' : 'Пользователь создан.');
        unset($this->users);
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => ! $user->is_active]);
        unset($this->users);
    }

    public function render()
    {
        return view('livewire.admin.settings.users');
    }
}
