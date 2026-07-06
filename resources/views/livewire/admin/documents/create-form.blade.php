<form id="document-create-form" wire:submit="save" class="h-full flex flex-col">

    {{-- ═══════════════════════════════════════════════════════════════════
         ШАПКА (fixed top)
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-shrink-0 pb-4 border-b border-gray-100 space-y-3">

        {{-- Строка 1: Клиент + Даты --}}
        <div class="grid grid-cols-[2fr_1fr_1fr] gap-5 items-start">

            <div class="space-y-2">
                <x-customer-search
                    :selected-id="$customer_id"
                    :selected-name="$selectedCustomerName"
                    :results="$customerResults"
                    :query="$customerQuery"
                    :error="$errors->first('customer_id')"
                />
                @if($customer_id && $contacts->count())
                <x-select label="Контактное лицо" wire:model="contact_id">
                    <option value="">— не выбрано —</option>
                    @foreach($contacts as $ct)
                    <option value="{{ $ct->id }}">
                        {{ $ct->name }}{{ $ct->position ? ' · '.$ct->position : '' }}
                    </option>
                    @endforeach
                </x-select>
                @endif
            </div>

            <x-input label="Дата выдачи" wire:model="issue_date"
                     type="date" :error="$errors->first('issue_date')" required />

            @if($type === 'quote')
            <x-input label="Действует до" wire:model="valid_until"
                     type="date" :error="$errors->first('valid_until')" required />
            @else
            <x-input label="Срок оплаты" wire:model="due_date"
                     type="date" :error="$errors->first('due_date')" required />
            @endif

        </div>

        {{-- Строка 2: Параметры --}}
        <div class="flex items-end gap-4">

            <div class="w-44">
                <x-select label="Валюта" wire:model.live="currency">
                    <option value="UZS">UZS — узбекский сум</option>
                    <option value="USD">USD — доллар США</option>
                </x-select>
            </div>

            @if($currency === 'USD')
            <div class="w-36">
                <x-input label="Курс UZS / USD" wire:model="exchange_rate"
                         type="number" min="1" step="1" placeholder="12 500" />
            </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Скидка общая</label>
                <div class="flex items-center gap-1.5">
                    <select wire:model.live="global_discount_type"
                            class="rounded-lg border border-gray-300 py-2 pl-2 pr-6 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent cursor-pointer">
                        <option value="percent">%</option>
                        <option value="sum">Сум</option>
                    </select>
                    <input wire:model.blur="global_discount_value"
                           type="number" min="0" step="0.5"
                           x-bind:max="$wire.global_discount_type === 'percent' ? 100 : ''"
                           class="w-32 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent no-spinner">
                </div>
            </div>

        </div>

        {{-- Поиск товара --}}
        <div
            x-data="{
                query: '',
                open: false,
                products: {{ Js::from($productsList) }},
                get filtered() {
                    if (this.query.length < 1) return [];
                    const q = this.query.toLowerCase();
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(q) ||
                        (p.sku && p.sku.toLowerCase().includes(q))
                    ).slice(0, 10);
                },
                select(id) {
                    this.query = '';
                    this.open  = false;
                    $wire.addProduct(id);
                }
            }"
            class="relative"
            x-on:click.outside="open = false"
        >
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </div>
                <input
                    x-model="query"
                    x-on:input="open = query.length > 0"
                    x-on:focus="open = query.length > 0"
                    x-on:keydown.escape="open = false; query = ''"
                    type="text"
                    autocomplete="off"
                    placeholder="Поиск по названию или артикулу..."
                    class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-shadow"
                >
                <div x-show="query.length > 0" x-on:click="query = ''; open = false"
                     class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>

            <div
                x-show="open && filtered.length > 0"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute left-0 right-0 top-full mt-1 z-30 bg-white rounded-xl shadow-xl border border-gray-200 max-h-60 overflow-y-auto divide-y divide-gray-50"
                x-cloak
            >
                <template x-for="p in filtered" :key="p.id">
                    <button type="button" @click="select(p.id)"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm text-left hover:bg-primary-50 transition-colors group">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-900 group-hover:text-primary-700 truncate" x-text="p.name"></p>
                            <p class="text-xs text-gray-400 font-mono" x-show="p.sku" x-text="p.sku"></p>
                            <p class="text-[10px] text-gray-400" x-show="p.group_name" x-text="p.group_name"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 text-right">
                            <p class="text-xs font-semibold tabular-nums text-gray-600"
                               x-show="$wire.currency === 'USD' ? p.price_usd > 0 : p.price_uzs > 0"
                               x-text="$wire.currency === 'USD'
                                    ? '$ ' + p.price_usd.toLocaleString('ru-RU')
                                    : p.price_uzs.toLocaleString('ru-RU') + ' UZS'"></p>
                            <p class="text-[10px] text-gray-400" x-show="$wire.currency === 'USD' ? p.price_usd > 0 : p.price_uzs > 0">розн. цена</p>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="open && query.length > 1 && filtered.length === 0"
                 class="absolute left-0 right-0 top-full mt-1 z-30 bg-white rounded-xl shadow-xl border border-gray-200 px-4 py-3 text-sm text-gray-400"
                 x-cloak>
                Товары не найдены
            </div>
        </div>

    </div>


    {{-- ═══════════════════════════════════════════════════════════════════
         ТОВАРЫ (scrollable middle)
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 min-h-0 overflow-y-auto py-4 space-y-3">

        @if($type === 'quote')
        {{-- Рекомендации --}}
        @if(count($recommendations) > 0)
        <div>
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">Рекомендации</p>
            <div class="flex flex-wrap gap-1.5">
                @foreach($recommendations as $rec)
                <div class="flex items-center gap-1.5 rounded-lg border border-gray-100 pl-2 pr-1.5 py-1 bg-white hover:bg-gray-50 transition-colors"
                     x-data>
                    <span class="flex-shrink-0 text-[9px] font-bold uppercase tracking-wide rounded px-1 py-0.5 leading-none
                        {{ $rec['priority'] === 'required' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $rec['priority'] === 'required' ? 'обяз' : 'рек' }}
                    </span>
                    <span class="text-xs text-gray-700 font-medium leading-tight">{{ $rec['name'] }}</span>
                    @if($rec['group_name'])
                    <span class="text-[10px] text-gray-400 leading-tight">· {{ $rec['group_name'] }}</span>
                    @endif
                    <div class="flex-shrink-0"
                         x-data="{ get added() { return $wire.items.some(i => i.product_id == {{ $rec['product_id'] }}) } }">
                        <button x-show="!added" type="button"
                                wire:click="addProduct({{ $rec['product_id'] }})"
                                class="text-[10px] font-semibold text-primary-600 hover:text-primary-800 px-1.5 py-0.5 rounded hover:bg-primary-50 transition-colors leading-tight">
                            + Добавить
                        </button>
                        <span x-show="added" class="text-[10px] font-semibold text-green-600 px-1.5 py-0.5">✓</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif

        @error('items')
        <p class="text-xs text-danger-600">{{ $message }}</p>
        @enderror

        {{-- Таблица позиций --}}
        @if(count($items) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-max w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b-2 border-gray-200">
                        <th class="pb-2 pr-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wide w-5">#</th>
                        <th class="pb-2 px-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wide">Наименование</th>
                        <th class="pb-2 px-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-14">Кол.</th>
                        <th class="pb-2 px-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-28">Скидка</th>
                        <th class="pb-2 px-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-32">
                            <span class="block text-[9px] text-gray-300 font-normal leading-none">по прайсу</span>
                            Цена
                            <span class="block text-[9px] text-amber-400 font-normal leading-none">фин. цена</span>
                        </th>
                        <th class="pb-2 pl-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-32">
                            <span class="block text-[9px] text-gray-300 font-normal leading-none">по прайсу</span>
                            Итого
                            <span class="block text-[9px] text-primary-400 font-normal leading-none">фин. итого</span>
                        </th>
                        <th class="w-7"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($items as $i => $item)
                    @php
                        $grossItemTotal = (float)($item['quantity'] ?? 1) * (float)($item['unit_price'] ?? 0);
                    @endphp
                    <tr class="group">
                        <td class="py-2 pr-2 text-[10px] text-gray-400 text-center align-middle">{{ $i + 1 }}</td>

                        <td class="py-2 px-2 align-top">
                            <div class="font-medium text-gray-900 text-xs leading-tight truncate max-w-[220px]"
                                 title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                            @if($item['sku'] ?? '')
                            <div class="text-[10px] text-gray-400 font-mono">{{ $item['sku'] }}</div>
                            @endif
                            @php
                                $prod = collect($productsList)->firstWhere('id', $item['product_id'] ?? 0);
                                $groupColor = match($prod['group_color'] ?? 'gray') {
                                    'blue'   => 'bg-blue-50 text-blue-700',
                                    'green'  => 'bg-green-50 text-green-700',
                                    'orange' => 'bg-orange-50 text-orange-700',
                                    default  => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            @if($prod && ($prod['group_name'] ?? ''))
                            <span class="inline-flex items-center rounded text-[9px] font-medium px-1.5 py-0.5 mt-0.5 {{ $groupColor }}">
                                {{ $prod['group_name'] }}
                            </span>
                            @endif
                        </td>

                        <td class="py-2 px-2 align-middle">
                            <input wire:model.live="items.{{ $i }}.quantity"
                                   type="number" min="1"
                                   class="w-full rounded-md border border-gray-200 px-1.5 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-primary-400 focus:border-primary-400 bg-gray-50 focus:bg-white transition-colors no-spinner">
                        </td>

                        <td class="py-2 px-2 align-middle">
                            <div class="flex items-center gap-1">
                                <select wire:model.live="items.{{ $i }}.discount_type"
                                        class="rounded-md border border-gray-200 py-1 pl-1 pr-4 text-xs bg-gray-50 focus:outline-none focus:ring-1 focus:ring-primary-400 focus:border-primary-400 transition-colors cursor-pointer">
                                    <option value="percent">%</option>
                                    <option value="sum">Сум</option>
                                </select>
                                <input wire:model.live="items.{{ $i }}.discount_value"
                                       type="number" min="0" step="0.01"
                                       x-bind:max="($wire.items[{{ $i }}]?.discount_type ?? 'percent') === 'percent' ? 100 : ''"
                                       class="w-14 rounded-md border border-gray-200 px-1.5 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-primary-400 focus:border-primary-400 bg-gray-50 focus:bg-white transition-colors no-spinner">
                            </div>
                        </td>

                        <td class="py-2 px-2">
                            <div class="flex flex-col gap-1">
                                <div class="text-xs text-gray-400 text-right tabular-nums leading-tight px-1.5 whitespace-nowrap">
                                    {{ number_format((float)($item['unit_price'] ?? 0), 0, '.', ' ') }}
                                </div>
                                <input wire:model.live="items.{{ $i }}.final_price"
                                       type="number" min="0" step="0.01"
                                       title="Цена за единицу после скидки"
                                       class="w-full rounded-md border border-amber-200 px-1.5 py-1 text-xs text-right font-medium text-gray-900 focus:outline-none focus:ring-1 focus:ring-amber-400 focus:border-amber-400 bg-amber-50 focus:bg-white transition-colors no-spinner">
                            </div>
                        </td>

                        <td class="py-2 pl-2">
                            <div class="flex flex-col gap-1">
                                <div class="text-xs text-gray-400 text-right tabular-nums leading-tight px-1.5 whitespace-nowrap">
                                    {{ number_format($grossItemTotal, 0, '.', ' ') }}
                                </div>
                                <input wire:model.live="items.{{ $i }}.total"
                                       type="number" min="0" step="1"
                                       title="Итоговая сумма — скидка пересчитается автоматически"
                                       class="w-full rounded-md border border-primary-200 px-1.5 py-1 text-xs text-right font-semibold text-gray-900 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 bg-primary-50 focus:bg-white transition-colors no-spinner">
                            </div>
                        </td>

                        <td class="py-2 pl-1 align-middle text-center">
                            <button type="button" wire:click="removeItem({{ $i }})"
                                    class="opacity-0 group-hover:opacity-100 p-1 rounded text-gray-300 hover:text-danger-500 hover:bg-danger-50 transition-all"
                                    title="Удалить позицию">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-sm text-gray-400">Позиций ещё нет</p>
            <p class="text-xs text-gray-300 mt-1">Найдите товар через поиск выше</p>
        </div>
        @endif

    </div>


    {{-- ═══════════════════════════════════════════════════════════════════
         ФУТЕР (fixed bottom): Итоги + Дополнительно
    ════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-shrink-0 pt-4 border-t border-gray-100 space-y-3">

        {{-- Строка 1: Итоги --}}
        @if(count($items) > 0)
        <div class="flex justify-end">
            <div class="w-72">
                <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3 space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Подытог</span>
                        <span class="tabular-nums font-medium text-gray-700">
                            {{ number_format($grossSubtotal, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>

                    @if($itemsDiscount > 0)
                    <div class="flex justify-between text-gray-500">
                        <span>Скидка по позициям</span>
                        <span class="tabular-nums font-medium text-danger-600">
                            −{{ number_format($itemsDiscount, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                    <div class="flex justify-between text-gray-500 border-t border-dashed border-gray-200 pt-1.5">
                        <span>После скидки</span>
                        <span class="tabular-nums font-medium text-gray-700">
                            {{ number_format($subtotal, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                    @endif

                    @if($globalDiscountAmount > 0)
                    <div class="flex justify-between text-gray-500">
                        <span>Скидка общая
                            @if($global_discount_type === 'percent')({{ $global_discount_value }}&thinsp;%)
                            @else({{ number_format($global_discount_value, 0, '.', ' ') }}&thinsp;{{ $currency }})
                            @endif
                        </span>
                        <span class="tabular-nums font-medium text-danger-600">
                            −{{ number_format($globalDiscountAmount, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                    @endif

                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-200 pt-2 mt-0.5">
                        <span>Итого</span>
                        <span class="tabular-nums">
                            {{ number_format($grandTotal, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Строка 2: Условия + Примечания --}}
        <div class="{{ $type === 'quote' ? 'grid grid-cols-2 gap-4' : '' }}">
            @if($type === 'quote')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Условия</label>
                <textarea wire:model="terms" rows="2"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                          placeholder="Условия оплаты, доставки..."></textarea>
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                <textarea wire:model="notes" rows="2"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                          placeholder="Внутренние заметки..."></textarea>
            </div>
        </div>

    </div>

</form>
