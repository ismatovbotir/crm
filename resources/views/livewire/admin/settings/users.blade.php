<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Пользователи</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление учётными записями и ролями</p>
        </div>
        <x-button wire:click="openCreate">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить пользователя
        </x-button>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <x-card class="mb-4" :padding="false">
        <div class="flex gap-3 p-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search"
                       placeholder="Поиск по имени или email..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="roleFilter"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все роли</option>
                @foreach($this->availableRoles as $slug => $label)
                    <option value="{{ $slug }}">{{ Str::before($label, ' —') }}</option>
                @endforeach
            </select>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Пользователь</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Email</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Роль</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($this->users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-semibold text-xs flex-shrink-0">
                                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400 md:hidden">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @php $role = $user->getRoleNames()->first() @endphp
                            @if($role)
                                @php
                                    $roleColors = [
                                        'super-admin'     => 'bg-purple-100 text-purple-700',
                                        'sales-director'  => 'bg-blue-100 text-blue-700',
                                        'sales-manager'   => 'bg-primary-100 text-primary-700',
                                        'tech-support'    => 'bg-warning-100 text-warning-700',
                                        'catalog-manager' => 'bg-success-100 text-success-700',
                                        'accountant'      => 'bg-gray-100 text-gray-700',
                                        'client-admin'    => 'bg-orange-100 text-orange-700',
                                        'client-user'     => 'bg-orange-50 text-orange-600',
                                    ];
                                    $color = $roleColors[$role] ?? 'bg-gray-100 text-gray-600';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                                    {{ $this->availableRoles[$role] ?? $role }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $user->id }})"
                                    title="{{ $user->is_active ? 'Деактивировать' : 'Активировать' }}"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium transition-colors
                                           {{ $user->is_active
                                              ? 'bg-success-100 text-success-700 hover:bg-success-200'
                                              : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                {{ $user->is_active ? 'Активен' : 'Неактивен' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openEdit({{ $user->id }})"
                                    class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            Пользователи не найдены
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($this->users->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $this->users->links() }}
            </div>
        @endif
    </x-card>

    {{-- Create / Edit slide-over --}}
    @if($showForm)
        <x-slide-over :title="$editingUserId ? 'Редактировать пользователя' : 'Новый пользователь'">
            <form wire:submit="save" class="space-y-4">

                <x-input label="Имя" wire:model="name" :error="$errors->first('name')" required />

                <x-input label="Email" type="email" wire:model="email" :error="$errors->first('email')" required />

                <x-input
                    label="{{ $editingUserId ? 'Новый пароль (оставьте пустым, чтобы не менять)' : 'Пароль' }}"
                    type="password"
                    wire:model="password"
                    :error="$errors->first('password')"
                    :required="!$editingUserId"
                    autocomplete="new-password"
                />

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Роль <span class="text-danger-500">*</span></label>
                    <select wire:model="role"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 @error('role') border-danger-500 @enderror">
                        <option value="">— Выберите роль —</option>
                        @foreach($this->availableRoles as $slug => $label)
                            <option value="{{ $slug }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="button"
                            wire:click="$toggle('isActive')"
                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors
                                   {{ $isActive ? 'bg-primary-600' : 'bg-gray-300' }}">
                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform
                                     {{ $isActive ? 'translate-x-4.5' : 'translate-x-0.5' }}"></span>
                    </button>
                    <span class="text-sm text-gray-700">Активный аккаунт</span>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                    <x-button type="button" variant="secondary" wire:click="closeForm">Отмена</x-button>
                    <x-button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ $editingUserId ? 'Сохранить' : 'Создать' }}</span>
                        <span wire:loading>Сохранение...</span>
                    </x-button>
                </div>
            </form>
        </x-slide-over>
    @endif
</div>
