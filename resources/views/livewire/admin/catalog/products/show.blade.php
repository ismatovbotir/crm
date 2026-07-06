<div class="max-w-6xl mx-auto">

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.catalog.products.index') }}" class="hover:text-primary-600 transition-colors">Товары</a>
                <span class="mx-1.5 text-gray-300">/</span>
                <span class="text-gray-900">{{ $product->name_ru }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $product->name_ru }}</h1>
            @if($product->name_uz)
            <p class="text-sm text-gray-400 mt-0.5">{{ $product->name_uz }}</p>
            @endif
            <p class="text-sm text-gray-500 mt-1">
                SKU: <span class="font-mono">{{ $product->sku }}</span>
                @if($product->brand) · {{ $product->brand }}@endif
            </p>
        </div>
        <div class="flex items-center gap-2 mt-1">
            @can('update', $product)
            <x-button wire:click="openEdit" variant="secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </x-button>
            @endcan
            @if($product->is_active)
            <x-badge color="green">Активен</x-badge>
            @else
            <x-badge color="gray">Отключён</x-badge>
            @endif
            @if($product->is_visible_portal)
            <x-badge color="blue">Виден в портале</x-badge>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">

        {{-- Left: tabs --}}
        <div class="col-span-2" x-data="{ tab: 'prices' }">
            <div class="flex border-b border-gray-200 mb-4">
                @php
                $tabs = ['prices' => 'Цены', 'stock' => 'Остатки', 'attributes' => 'Характеристики', 'attachments' => 'Документы'];
                if ($product->is_serial) $tabs['serials'] = 'Серийные номера';
                @endphp
                @foreach($tabs as $key => $label)
                <button type="button"
                        @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'border-primary-600 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors whitespace-nowrap">
                    {{ $label }}
                </button>
                @endforeach
            </div>

            {{-- Prices --}}
            <div x-show="tab === 'prices'" x-cloak>
                <x-card :padding="false">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/60">
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Тип цены</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Сумма</th>
                                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Вал.</th>
                                <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($product->prices as $price)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-4 py-2.5 text-gray-700">
                                    {{ match($price->type) {
                                        'retail'    => 'Розничная',
                                        'wholesale' => 'Оптовая',
                                        'cost'      => 'Себестоимость',
                                        'special'   => 'Специальная',
                                        default     => $price->type,
                                    } }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-medium text-gray-900 tabular-nums">
                                    {{ number_format($price->amount, 0, '.', ' ') }}
                                </td>
                                <td class="px-4 py-2.5 text-xs text-gray-400">{{ $price->currency }}</td>
                                <td class="px-4 py-2.5">
                                    <x-badge :color="$price->is_active ? 'green' : 'gray'">
                                        {{ $price->is_active ? 'Активна' : 'Откл.' }}
                                    </x-badge>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-400">Цены не заданы</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </x-card>
            </div>

            {{-- Stock --}}
            <div x-show="tab === 'stock'" x-cloak>
                <x-card title="Складские остатки">
                    @if($product->stock)
                    <div class="grid grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 mb-1">Всего</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $product->stock->quantity }}</p>
                            <p class="text-xs text-gray-400 mt-1">шт</p>
                        </div>
                        <div class="text-center p-4 bg-warning-50 rounded-xl">
                            <p class="text-xs text-warning-700 mb-1">Резерв</p>
                            <p class="text-2xl font-bold text-warning-700">{{ $product->stock->reserved }}</p>
                            <p class="text-xs text-warning-500 mt-1">шт</p>
                        </div>
                        <div class="text-center p-4 bg-success-50 rounded-xl">
                            <p class="text-xs text-success-700 mb-1">Доступно</p>
                            <p class="text-2xl font-bold text-success-700">{{ $product->stock->available }}</p>
                            <p class="text-xs text-success-500 mt-1">шт</p>
                        </div>
                    </div>
                    @if($product->stock->warehouse)
                    <p class="mt-4 text-sm text-gray-500">Склад: <span class="font-medium text-gray-700">{{ $product->stock->warehouse }}</span></p>
                    @endif
                    @else
                    <p class="text-sm text-gray-400 text-center py-6">Данные об остатках отсутствуют</p>
                    @endif
                </x-card>
            </div>

            {{-- Attributes --}}
            <div x-show="tab === 'attributes'" x-cloak>
                <x-card title="Характеристики" :padding="false">
                    @forelse($product->attributeValues as $av)
                    <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                        <span class="text-sm text-gray-500">
                            {{ $av->attribute->name }}
                            @if($av->attribute->unit)
                            <span class="text-xs text-gray-400">({{ $av->attribute->unit }})</span>
                            @endif
                        </span>
                        <span class="text-sm font-medium text-gray-900">{{ $av->value ?? '—' }}</span>
                    </div>
                    @empty
                    <div class="px-4 py-10 text-center text-sm text-gray-400">Характеристики не заданы</div>
                    @endforelse
                </x-card>
            </div>

            {{-- Attachments --}}
            <div x-show="tab === 'attachments'" x-cloak>
                <x-card title="Документы" :padding="false">
                    @forelse($product->attachments as $att)
                    <div class="flex items-center gap-3 px-4 py-2.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors">
                        <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $att->name }}</p>
                            <p class="text-xs text-gray-400">{{ $att->type }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-10 text-center text-sm text-gray-400">Документы не прикреплены</div>
                    @endforelse
                </x-card>
            </div>

            {{-- Serials --}}
            @if($product->is_serial)
            <div x-show="tab === 'serials'" x-cloak>
                <livewire:admin.catalog.products.serials :product="$product" />
            </div>
            @endif
        </div>

        {{-- Right: sidebar --}}
        <div class="space-y-4">
            <x-card title="Информация">
                <dl class="space-y-3 text-sm">
                    @if($product->category)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Категория</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $product->category->name_ru }}</dd>
                    </div>
                    @endif
                    @if($product->brand)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Бренд</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $product->brand }}</dd>
                    </div>
                    @endif
                    @if($product->model_number)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Модель</dt>
                        <dd class="mt-0.5 font-mono text-sm text-gray-900">{{ $product->model_number }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Единица измерения</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $product->unit ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Видимость в портале</dt>
                        <dd class="mt-0.5">
                            <x-badge :color="$product->is_visible_portal ? 'green' : 'gray'">
                                {{ $product->is_visible_portal ? 'Виден клиентам' : 'Скрыт' }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Добавлен</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $product->created_at->format('d.m.Y') }}</dd>
                    </div>
                    @if($product->updated_at != $product->created_at)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Обновлён</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $product->updated_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            @if($product->description_ru)
            <x-card title="Описание">
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $product->description_ru }}</p>
            </x-card>
            @endif

            @if($product->notes)
            <x-card title="Заметки">
                <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-wrap">{{ $product->notes }}</p>
            </x-card>
            @endif
        </div>
    </div>

    @if($showEdit)
    <x-slide-over title="Редактировать товар" formId="product-edit-form">
        <livewire:admin.catalog.products.edit-form :product-id="$product->id" />
    </x-slide-over>
    @endif
</div>
