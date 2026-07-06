<div>
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Группы товаров</h1>
            <p class="text-sm text-gray-500 mt-0.5">Цветовые группы для визуальной классификации позиций каталога</p>
        </div>
        @can('catalog.products.update')
        <x-button wire:click="openCreate" type="button">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Создать группу
        </x-button>
        @endcan
    </div>

    {{-- Flash success --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-12">Цвет</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Название</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Описание</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-20">Порядок</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">Статус</th>
                        <th class="px-4 py-3 w-32"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($groups as $group)

                    {{-- View row --}}
                    @if($editingId !== $group->id)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3">
                            @php
                            $colorMap = [
                                'blue'   => 'bg-blue-500',
                                'green'  => 'bg-green-500',
                                'orange' => 'bg-orange-500',
                                'red'    => 'bg-red-500',
                                'purple' => 'bg-purple-500',
                                'gray'   => 'bg-gray-400',
                            ];
                            $dotClass = $colorMap[$group->color] ?? 'bg-gray-400';
                            @endphp
                            <span class="block w-5 h-5 rounded-full {{ $dotClass }} flex-shrink-0"></span>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900">{{ $group->name_ru }}</p>
                            @if($group->name_uz)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $group->name_uz }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs max-w-xs">
                            <span class="block truncate" style="max-width:200px" title="{{ $group->description }}">
                                {{ $group->description ?: '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-center">{{ $group->sort_order }}</td>
                        <td class="px-4 py-3">
                            @if($group->is_active)
                            <x-badge color="green">Активна</x-badge>
                            @else
                            <x-badge color="gray">Неактивна</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1 justify-end">
                                @can('catalog.products.update')
                                <button type="button"
                                        wire:click="startEdit({{ $group->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Изменить
                                </button>
                                <button type="button"
                                        wire:click="toggleActive({{ $group->id }})"
                                        title="{{ $group->is_active ? 'Отключить' : 'Включить' }}"
                                        class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md border transition-colors
                                               {{ $group->is_active
                                                  ? 'text-warning-700 bg-warning-50 border-warning-200 hover:bg-warning-100'
                                                  : 'text-success-700 bg-success-50 border-success-200 hover:bg-success-100' }}">
                                    @if($group->is_active)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    @else
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    @endif
                                    {{ $group->is_active ? 'Выкл' : 'Вкл' }}
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    {{-- Inline edit row --}}
                    @else
                    <tr class="bg-primary-50/30">
                        <td colspan="6" class="px-4 py-4">
                            <form wire:submit="saveEdit" class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <x-input
                                        label="Название (RU)"
                                        wire:model="editNameRu"
                                        :error="$errors->first('editNameRu')"
                                        required
                                        placeholder="Кассовое оборудование"
                                    />
                                    <x-input
                                        label="Название (UZ)"
                                        wire:model="editNameUz"
                                        placeholder="Kassa uskunalari"
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="sm:col-span-2">
                                        <x-input
                                            label="Описание"
                                            wire:model="editDescription"
                                            placeholder="Краткое описание группы..."
                                        />
                                    </div>
                                    <x-input
                                        label="Порядок сортировки"
                                        type="number"
                                        wire:model="editSortOrder"
                                        min="0"
                                    />
                                </div>

                                {{-- Color picker --}}
                                <div>
                                    <p class="block text-sm font-medium text-gray-700 mb-2">Цвет группы</p>
                                    <div class="flex items-center gap-2">
                                        @foreach(['gray' => 'bg-gray-400', 'blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'orange' => 'bg-orange-500', 'red' => 'bg-red-500', 'purple' => 'bg-purple-500'] as $val => $cls)
                                        <label class="cursor-pointer">
                                            <input type="radio" wire:model="editColor" value="{{ $val }}" class="sr-only">
                                            <span class="block w-6 h-6 rounded-full {{ $cls }} ring-2 ring-offset-1 transition-all
                                                         {{ $editColor === $val ? 'ring-gray-900 scale-110' : 'ring-transparent hover:ring-gray-400' }}">
                                            </span>
                                        </label>
                                        @endforeach
                                        <span class="ml-2 text-xs text-gray-400 capitalize">{{ $editColor }}</span>
                                    </div>
                                </div>

                                {{-- Active toggle --}}
                                <div class="flex items-center gap-2">
                                    <input type="checkbox"
                                           id="edit-is-active-{{ $group->id }}"
                                           wire:model="editIsActive"
                                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <label for="edit-is-active-{{ $group->id }}" class="text-sm font-medium text-gray-700">
                                        Группа активна
                                    </label>
                                </div>

                                {{-- Action buttons --}}
                                <div class="flex items-center gap-2 pt-1">
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Сохранить
                                    </button>
                                    <button type="button"
                                            wire:click="cancelEdit"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                        Отмена
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endif

                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-14 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            <p class="text-sm text-gray-400">Группы товаров не созданы</p>
                            <p class="text-xs text-gray-400 mt-1">Нажмите «Создать группу» чтобы добавить первую</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    {{-- Create slide-over --}}
    @if($showCreateForm)
    <x-slide-over title="Новая группа товаров" formId="group-create-form" saveLabel="Создать">
        <form id="group-create-form" wire:submit="create" class="space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input
                    label="Название (RU)"
                    wire:model="newNameRu"
                    :error="$errors->first('newNameRu')"
                    required
                    placeholder="Кассовое оборудование"
                />
                <x-input
                    label="Название (UZ)"
                    wire:model="newNameUz"
                    placeholder="Kassa uskunalari"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
                <textarea wire:model="newDescription"
                          rows="2"
                          placeholder="Краткое описание назначения группы..."
                          class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none transition-colors">
                </textarea>
            </div>

            {{-- Color picker --}}
            <div>
                <p class="block text-sm font-medium text-gray-700 mb-2">Цвет группы</p>
                <div class="flex items-center gap-2">
                    @foreach(['gray' => 'bg-gray-400', 'blue' => 'bg-blue-500', 'green' => 'bg-green-500', 'orange' => 'bg-orange-500', 'red' => 'bg-red-500', 'purple' => 'bg-purple-500'] as $val => $cls)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="newColor" value="{{ $val }}" class="sr-only">
                        <span class="block w-6 h-6 rounded-full {{ $cls }} ring-2 ring-offset-1 transition-all
                                     {{ $newColor === $val ? 'ring-gray-900 scale-110' : 'ring-transparent hover:ring-gray-400' }}">
                        </span>
                    </label>
                    @endforeach
                    <span class="ml-2 text-xs text-gray-400 capitalize">{{ $newColor ?? '—' }}</span>
                </div>
                @error('newColor')
                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-input
                label="Порядок сортировки"
                type="number"
                wire:model="newSortOrder"
                min="0"
                placeholder="0"
            />

        </form>
    </x-slide-over>
    @endif
</div>
