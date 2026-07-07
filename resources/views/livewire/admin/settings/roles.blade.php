<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Роли и права</h1>
            <p class="text-sm text-gray-500 mt-0.5">Какие действия доступны каждой роли сотрудников RSG</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @php
        // Человекочитаемые заголовки групп permissions (ключи зеркалят config('permissions.permissions')).
        $groupLabels = [
            'leads'               => 'Лиды',
            'customers'           => 'Клиенты',
            'quotes'              => 'КП',
            'invoices'            => 'Инвойсы',
            'sells'               => 'Продажи',
            'returns'             => 'Возвраты',
            'catalog'             => 'Каталог',
            'tickets'             => 'Тикеты',
            'equipment_requests'  => 'Заявки на оборудование',
            'reports'             => 'Отчёты',
            'settings'            => 'Настройки',
        ];

        // Цветовая маркировка роли (тот же паттерн, что и в admin/settings/users.blade.php).
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

        // Пояснение, почему locked-роль не редактируется здесь.
        $lockedNotes = [
            'super-admin'  => 'Полный доступ ко всем правам — не редактируется через эту страницу.',
            'client-admin' => 'Доступ клиентских ролей основан на владении данными (ownership), а не на правах модуля — permissions здесь не применяются.',
            'client-user'  => 'Доступ клиентских ролей основан на владении данными (ownership), а не на правах модуля — permissions здесь не применяются.',
        ];
    @endphp

    <div class="space-y-4">
        @foreach($this->roles as $role)
            @php
                $color = $roleColors[$role->name] ?? 'bg-gray-100 text-gray-600';
                $locked = $this->isLocked($role->name);
            @endphp

            <x-card :padding="false" x-data="{ expanded: {{ $locked ? 'true' : 'false' }} }">
                <button type="button"
                        @click="expanded = !expanded"
                        class="w-full flex items-center justify-between px-5 py-3.5 border-b border-gray-100 text-left">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $color }}">
                            {{ $this->roleLabel($role->name) }}
                        </span>
                        @if($locked)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                Не редактируется
                            </span>
                        @else
                            <span class="text-xs text-gray-400">
                                {{ count($this->selectedPermissions[$role->name] ?? []) }} прав выбрано
                            </span>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
                         :class="expanded ? 'rotate-180' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div x-show="expanded"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0">
                    <div class="px-5 py-4">
                        @if($locked)
                            <p class="text-xs text-gray-500 mb-3">{{ $lockedNotes[$role->name] ?? '' }}</p>

                            @php $currentPerms = $role->getPermissionNames(); @endphp
                            @if($currentPerms->isEmpty())
                                <p class="text-sm text-gray-400">Нет назначенных permissions (доступ основан на ownership).</p>
                            @else
                                @php
                                    $allPermissionLabels = collect($this->permissionGroups)->flatMap(fn($group) => $group)->toArray();
                                @endphp
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($currentPerms as $permName)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ $allPermissionLabels[$permName] ?? $permName }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-x-6 gap-y-5">
                                @foreach($this->permissionGroups as $groupKey => $perms)
                                    <fieldset>
                                        <legend class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">
                                            {{ $groupLabels[$groupKey] ?? \Illuminate\Support\Str::headline($groupKey) }}
                                        </legend>
                                        <div class="space-y-1.5">
                                            @foreach($perms as $permKey => $permLabel)
                                                <label class="flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                                                    <input type="checkbox"
                                                           wire:model="selectedPermissions.{{ $role->name }}"
                                                           value="{{ $permKey }}"
                                                           class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                                    <span>{{ $permLabel }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endforeach
                            </div>

                            <div class="flex justify-end pt-4 mt-4 border-t border-gray-100">
                                <x-button wire:click="savePermissions('{{ $role->name }}')"
                                          wire:loading.attr="disabled"
                                          wire:target="savePermissions('{{ $role->name }}')">
                                    <span wire:loading.remove wire:target="savePermissions('{{ $role->name }}')">Сохранить</span>
                                    <span wire:loading wire:target="savePermissions('{{ $role->name }}')">Сохранение...</span>
                                </x-button>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        @endforeach
    </div>
</div>
