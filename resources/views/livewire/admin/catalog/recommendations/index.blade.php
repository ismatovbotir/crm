<div>
    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Рекомендации по типу бизнеса</h1>
            <p class="text-sm text-gray-500 mt-0.5">Настройте какие товары предлагать клиентам при создании КП</p>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="flex gap-6 items-start">

        {{-- ── Левая колонка: типы бизнеса ─────────────────────────────── --}}
        <div class="w-52 flex-shrink-0">
            <x-card :padding="false">
                <div class="px-3 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 px-1 mb-2">Тип бизнеса</p>
                    <div class="space-y-0.5">
                        @foreach($businessTypes as $type)
                        <button type="button"
                                wire:click="selectType({{ $type->id }})"
                                class="w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium transition-colors flex items-center justify-between gap-2
                                       {{ $selectedTypeId === $type->id
                                          ? 'bg-primary-50 text-primary-700 border border-primary-200'
                                          : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-transparent' }}">
                            <span class="truncate">{{ $type->name }}</span>
                            @if(($type->recommendations_count ?? 0) > 0)
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full flex-shrink-0
                                         {{ $selectedTypeId === $type->id ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $type->recommendations_count }}
                            </span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
            </x-card>
        </div>

        {{-- ── Правая колонка: рекомендации ────────────────────────────── --}}
        <div class="flex-1 min-w-0">

            @if(! $selectedTypeId)
            {{-- Empty state --}}
            <x-card>
                <div class="py-16 flex flex-col items-center text-center">
                    <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <p class="text-sm text-gray-400">Выберите тип бизнеса слева</p>
                    <p class="text-xs text-gray-300 mt-1">чтобы настроить список рекомендованных товаров</p>
                </div>
            </x-card>

            @else

            {{-- Форма добавления товара --}}
            <x-card class="mb-4">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 mb-3">Добавить товар</p>
                <div class="flex gap-3 items-start flex-wrap">

                    {{-- Поиск товара --}}
                    <div class="flex-1 min-w-48 relative" x-data="{ open: false }" x-on:click.outside="open = false">
                        @if($selectedProduct)
                        {{-- Выбранный товар --}}
                        <div class="flex items-center gap-2 px-3 py-2 bg-primary-50 border border-primary-200 rounded-lg">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-primary-800 truncate">{{ $selectedProduct->name_ru }}</p>
                                <p class="text-xs text-primary-500 font-mono">{{ $selectedProduct->sku }}</p>
                            </div>
                            <button type="button"
                                    wire:click="$set('newProductId', null)"
                                    class="text-primary-400 hover:text-primary-700 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        @else
                        <input
                            wire:model.live.debounce.300ms="productSearch"
                            x-on:input="open = $wire.productSearch.length >= 2"
                            x-on:focus="open = $wire.productSearch.length >= 2"
                            type="text"
                            autocomplete="off"
                            placeholder="Поиск по названию или артикулу..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        >
                        @if(count($productSearchResults) > 0)
                        <div x-show="open"
                             class="absolute left-0 right-0 top-full mt-1 z-30 bg-white rounded-xl shadow-xl border border-gray-200 max-h-56 overflow-y-auto divide-y divide-gray-50"
                             x-cloak>
                            @foreach($productSearchResults as $p)
                            <button type="button"
                                    wire:click="selectProduct({{ $p['id'] }})"
                                    x-on:click="open = false"
                                    class="flex items-center justify-between w-full px-4 py-2.5 text-sm text-left hover:bg-primary-50 transition-colors group">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-gray-900 group-hover:text-primary-700 truncate">{{ $p['name_ru'] }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $p['sku'] }}</p>
                                </div>
                            </button>
                            @endforeach
                        </div>
                        @endif
                        @if(strlen($productSearch) >= 2 && count($productSearchResults) === 0)
                        <div class="absolute left-0 right-0 top-full mt-1 z-30 bg-white rounded-xl shadow-md border border-gray-200 px-4 py-3 text-sm text-gray-400">
                            Товары не найдены
                        </div>
                        @endif
                        @endif
                    </div>

                    {{-- Приоритет --}}
                    <div class="flex-shrink-0">
                        <select wire:model="newPriority"
                                class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="required">Обязательный</option>
                            <option value="recommended">Рекомендуемый</option>
                            <option value="optional">Опциональный</option>
                        </select>
                    </div>

                    {{-- Кнопка добавить --}}
                    <button type="button"
                            wire:click="addRecommendation"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 transition-colors flex-shrink-0">
                        <span wire:loading.remove wire:target="addRecommendation">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="addRecommendation">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </span>
                        Добавить
                    </button>
                </div>

                @error('newProductId')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </x-card>

            {{-- Список рекомендаций --}}
            <x-card>
                @if($recommendations->isEmpty())
                <div class="py-10 text-center">
                    <p class="text-sm text-gray-400">Нет рекомендаций для этого типа бизнеса</p>
                    <p class="text-xs text-gray-300 mt-1">Добавьте товары выше</p>
                </div>
                @else
                @php
                    $priorityGroups = [
                        'required'    => ['label' => 'Обязательные',   'dot' => 'bg-red-500',  'badge' => 'bg-red-100 text-red-700'],
                        'recommended' => ['label' => 'Рекомендуемые',  'dot' => 'bg-blue-500', 'badge' => 'bg-blue-100 text-blue-700'],
                        'optional'    => ['label' => 'Дополнительные', 'dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-600'],
                    ];
                @endphp
                @foreach($priorityGroups as $priority => $meta)
                @php $group = $recommendations->where('priority', $priority) @endphp
                @if($group->isNotEmpty())
                <div class="mb-5 last:mb-0">
                    <div class="flex items-center gap-2 mb-2.5">
                        <span class="block w-2 h-2 rounded-full {{ $meta['dot'] }}"></span>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            {{ $meta['label'] }} ({{ $group->count() }})
                        </p>
                    </div>
                    <div class="space-y-1.5">
                        @foreach($group as $rec)
                        @php
                            $groupColor = match($rec->product->category?->group?->color ?? 'gray') {
                                'blue'   => 'bg-blue-50 text-blue-700',
                                'green'  => 'bg-green-50 text-green-700',
                                'orange' => 'bg-orange-50 text-orange-700',
                                'red'    => 'bg-red-50 text-red-700',
                                'purple' => 'bg-purple-50 text-purple-700',
                                default  => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <div class="flex items-center gap-3 px-3 py-2.5 bg-gray-50 border border-gray-100 rounded-lg hover:bg-white transition-colors">
                            {{-- Группа товара --}}
                            @if($rec->product->category?->group)
                            <span class="text-[9px] font-semibold px-1.5 py-0.5 rounded flex-shrink-0 {{ $groupColor }}">
                                {{ $rec->product->category->group->name_ru }}
                            </span>
                            @endif
                            {{-- Название и SKU --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $rec->product->name_ru }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $rec->product->sku }}</p>
                            </div>
                            {{-- Смена приоритета --}}
                            <select wire:change="updatePriority({{ $rec->id }}, $event.target.value)"
                                    class="text-xs border border-gray-200 rounded-md py-1.5 pl-2 pr-6 bg-white focus:outline-none focus:ring-1 focus:ring-primary-500 flex-shrink-0">
                                <option value="required"    {{ $rec->priority === 'required'    ? 'selected' : '' }}>Обязательный</option>
                                <option value="recommended" {{ $rec->priority === 'recommended' ? 'selected' : '' }}>Рекомендуемый</option>
                                <option value="optional"    {{ $rec->priority === 'optional'    ? 'selected' : '' }}>Опциональный</option>
                            </select>
                            {{-- Удалить --}}
                            <button type="button"
                                    wire:click="removeRecommendation({{ $rec->id }})"
                                    wire:confirm="Удалить товар из рекомендаций?"
                                    class="p-1.5 text-gray-300 hover:text-red-500 transition-colors flex-shrink-0 rounded-md hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                @endif
            </x-card>

            @endif
        </div>{{-- /right --}}
    </div>
</div>
