<div class="max-w-6xl mx-auto">
    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>
    @endif

    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.sells.index') }}" class="hover:text-primary-600">Продажи</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">{{ $sell->number }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $sell->number }}</h1>
            <p class="text-sm text-gray-500">{{ $sell->customer->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-sell-status-badge :status="$sell->status" />
            <a href="{{ route('admin.sells.pdf', $sell) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-4">
            {{-- Items --}}
            <x-card title="Позиции" :padding="false">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50/50">
                            <th class="text-left px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">#</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Товар</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Кол.</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Цена</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Скидка%</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($sell->items as $i => $item)
                        <tr>
                            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                @if($item->product->sku)
                                <p class="text-xs text-gray-400 font-mono">{{ $item->product->sku }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($item->unit_price, 0, '.', ' ') }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500">{{ $item->discount_percent }}%</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900">{{ number_format($item->total, 0, '.', ' ') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Позиций нет</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-900">Итого:</td>
                            <td class="px-4 py-3 text-right text-lg font-bold text-gray-900">
                                {{ number_format($sell->total, 0, '.', ' ') }} {{ $sell->currency }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </x-card>

            @if($sell->notes)
            <x-card title="Примечание">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $sell->notes }}</p>
            </x-card>
            @endif
        </div>

        <div class="space-y-4">
            {{-- Status control --}}
            <x-card title="Статус отгрузки">
                <div class="mb-3"><x-sell-status-badge :status="$sell->status" /></div>
                <select wire:change="updateStatus($event.target.value)"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @foreach(['draft'=>'Черновик','confirmed'=>'Подтверждён','shipped'=>'Отгружен','delivered'=>'Доставлен','cancelled'=>'Отменён'] as $s => $label)
                    <option value="{{ $s }}" @selected($sell->status === $s)>{{ $label }}</option>
                    @endforeach
                </select>
            </x-card>

            {{-- Invoice --}}
            <x-card title="Инвойс">
                @if($sell->invoice)
                <div class="space-y-1.5">
                    <a href="{{ route('admin.invoices.show', $sell->invoice) }}"
                       class="font-mono font-medium text-primary-600 hover:text-primary-700 hover:underline text-sm">
                        {{ $sell->invoice->number }}
                    </a>
                    <p class="text-sm font-medium text-gray-700">
                        {{ number_format($sell->invoice->total, 0, '.', ' ') }} {{ $sell->invoice->currency }}
                    </p>
                    <x-invoice-status-badge :status="$sell->invoice->status" />
                </div>
                @else
                <p class="text-sm text-gray-400">Прямая продажа (без инвойса)</p>
                @endif
            </x-card>

            {{-- Info --}}
            <x-card title="Информация">
                <dl class="space-y-2.5 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Клиент</dt>
                        <dd class="text-gray-900 font-medium mt-0.5">{{ $sell->customer->name }}</dd>
                    </div>
                    @if($sell->manager)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Менеджер</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $sell->manager->name }}</dd>
                    </div>
                    @endif
                    @if($sell->sold_at)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Дата продажи</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $sell->sold_at->format('d.m.Y') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Создан</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $sell->created_at->format('d.m.Y') }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>
</div>
