<form id="invoice-create-form" wire:submit="save" class="flex flex-col gap-0">

    <div class="flex gap-0 min-h-0">

        {{-- ── Левая панель: параметры инвойса ──────────────────────────── --}}
        <div class="w-64 flex-shrink-0 border-r border-gray-100 pr-6 space-y-5 overflow-y-auto"
             style="max-height: calc(100vh - 18rem)">

            {{-- Клиент --}}
            <section>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Клиент</p>
                <x-customer-search
                    :selected-id="$customer_id"
                    :selected-name="$selectedCustomerName"
                    :results="$customerResults"
                    :query="$customerQuery"
                    :error="$errors->first('customer_id')"
                />
            </section>

            {{-- Параметры --}}
            <section>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Параметры</p>
                <div class="space-y-3">
                    <x-select label="Валюта" wire:model.live="currency">
                        <option value="UZS">UZS — узбекский сум</option>
                        <option value="USD">USD — доллар США</option>
                    </x-select>

                    <x-input label="Срок оплаты" type="date" wire:model="due_date"
                             :error="$errors->first('due_date')" required />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">НДС, %</label>
                        <div class="relative">
                            <input wire:model.live="tax_rate" type="number" min="0" max="100" step="1"
                                   class="w-full rounded-lg border border-gray-300 pl-3 pr-7 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 no-spinner">
                            <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">%</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Примечания --}}
            <section>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-3">Дополнительно</p>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Примечания</label>
                    <textarea wire:model="notes" rows="3"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                              placeholder="Условия оплаты, реквизиты..."></textarea>
                </div>
            </section>

        </div>{{-- /left --}}


        {{-- ── Правая панель: позиции ────────────────────────────────────── --}}
        <div class="flex-1 pl-6 flex flex-col min-h-0 overflow-y-auto"
             style="max-height: calc(100vh - 18rem)">

            {{-- Поиск товара ──────────────────────────────────────────────── --}}
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
                class="relative mb-4 flex-shrink-0"
                x-on:click.outside="open = false"
            >
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-2">
                    Добавить товар
                </label>
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

                {{-- Dropdown --}}
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
                        <button
                            type="button"
                            @click="select(p.id)"
                            class="flex items-center justify-between w-full px-4 py-2.5 text-sm text-left hover:bg-primary-50 transition-colors group"
                        >
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
                                        : p.price_uzs.toLocaleString('ru-RU') + ' UZS'">
                                </p>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- No results --}}
                <div
                    x-show="open && query.length > 1 && filtered.length === 0"
                    class="absolute left-0 right-0 top-full mt-1 z-30 bg-white rounded-xl shadow-xl border border-gray-200 px-4 py-3 text-sm text-gray-400"
                    x-cloak
                >
                    Товары не найдены
                </div>
            </div>

            {{-- Validation error --}}
            @error('items')
            <p class="text-xs text-danger-600 mb-2 flex-shrink-0">{{ $message }}</p>
            @enderror

            {{-- Items table ───────────────────────────────────────────────── --}}
            @if(count($items) > 0)
            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead class="flex-shrink-0">
                        <tr class="border-b-2 border-gray-200">
                            <th class="pb-2 pr-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wide w-5">#</th>
                            <th class="pb-2 px-2 text-left text-[10px] font-bold text-gray-400 uppercase tracking-wide">Наименование</th>
                            <th class="pb-2 px-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-16">Кол.</th>
                            <th class="pb-2 px-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-32">Цена</th>
                            <th class="pb-2 pl-2 text-right text-[10px] font-bold text-gray-400 uppercase tracking-wide w-28">Итого</th>
                            <th class="w-7"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($items as $i => $item)
                        <tr class="group">
                            <td class="py-2 pr-2 text-[10px] text-gray-400 text-center align-middle">{{ $i + 1 }}</td>

                            <td class="py-2 px-2 align-middle">
                                <div class="font-medium text-gray-900 text-xs leading-tight truncate max-w-[200px]"
                                     title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                @if($item['sku'] ?? '')
                                <div class="text-[10px] text-gray-400 font-mono">{{ $item['sku'] }}</div>
                                @endif
                            </td>

                            <td class="py-2 px-2 align-middle">
                                <input
                                    wire:model.live="items.{{ $i }}.quantity"
                                    type="number" min="1"
                                    class="w-full rounded-md border border-gray-200 px-1.5 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-primary-400 bg-gray-50 focus:bg-white transition-colors no-spinner"
                                >
                            </td>

                            <td class="py-2 px-2 align-middle">
                                <input
                                    wire:model.live="items.{{ $i }}.unit_price"
                                    type="number" min="0" step="1"
                                    class="w-full rounded-md border border-gray-200 px-1.5 py-1 text-xs text-right focus:outline-none focus:ring-1 focus:ring-primary-400 bg-gray-50 focus:bg-white transition-colors no-spinner"
                                >
                            </td>

                            <td class="py-2 pl-2 align-middle text-right text-xs font-semibold text-gray-700 tabular-nums whitespace-nowrap">
                                {{ number_format((float)($item['quantity']??0) * (float)($item['unit_price']??0), 0, '.', ' ') }}
                            </td>

                            <td class="py-2 pl-1 align-middle text-center">
                                <button
                                    type="button"
                                    wire:click="removeItem({{ $i }})"
                                    class="opacity-0 group-hover:opacity-100 p-1 rounded text-gray-300 hover:text-danger-500 hover:bg-danger-50 transition-all"
                                    title="Удалить позицию"
                                >
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
            <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm text-gray-400">Позиций ещё нет</p>
                <p class="text-xs text-gray-300 mt-1">Найдите товар через поиск выше</p>
            </div>
            @endif

            {{-- Итоги ─────────────────────────────────────────────────────── --}}
            @if(count($items) > 0)
            <div class="mt-4 flex-shrink-0">
                <div class="bg-gray-50 rounded-xl border border-gray-100 px-4 py-3 space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Подытог</span>
                        <span class="tabular-nums font-medium text-gray-700">
                            {{ number_format($subtotal, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                    @if($tax_rate > 0)
                    <div class="flex justify-between text-gray-500">
                        <span>НДС ({{ $tax_rate }}&thinsp;%)</span>
                        <span class="tabular-nums font-medium text-gray-600">
                            +{{ number_format($taxAmount, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-200 pt-2 mt-0.5">
                        <span>Итого к оплате</span>
                        <span class="tabular-nums">
                            {{ number_format($total, 0, '.', ' ') }}&thinsp;{{ $currency }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

        </div>{{-- /right --}}
    </div>{{-- /panels --}}

</form>
