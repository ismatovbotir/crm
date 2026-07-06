<div class="max-w-6xl mx-auto">
    @if(session('success'))<div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">{{ session('error') }}</div>@endif

    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.invoices.index') }}" class="hover:text-primary-600">Инвойсы</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">{{ $invoice->number }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $invoice->number }}</h1>
            <p class="text-sm text-gray-500">{{ $invoice->customer->name }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-invoice-status-badge :status="$invoice->status" />
            <x-shipment-status-badge :status="$invoice->shipment_status ?? 'none'" />
            @if($invoice->quote)
            <a href="{{ route('admin.quotes.show', $invoice->quote) }}" class="text-sm text-primary-600 hover:text-primary-700">← КП {{ $invoice->quote->number }}</a>
            @endif
            <a href="{{ route('admin.invoices.pdf', $invoice) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
            </a>
            @if($invoice->sells->isNotEmpty())
            <a href="{{ route('admin.returns.create', ['sell_id' => $invoice->sells->last()->id]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Возврат
            </a>
            @endif
            <x-button wire:click="$set('showPaymentForm', true)" variant="secondary">Добавить платёж</x-button>
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
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">НДС%</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Итого</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($invoice->items as $i => $item)
                        <tr>
                            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $i+1 }}</td>
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                @if($item->sku)<p class="text-xs text-gray-400 font-mono">{{ $item->sku }}</p>@endif
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($item->unit_price,0,'.',' ') }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500">{{ $item->tax_rate }}%</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900">{{ number_format($item->total,0,'.',' ') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Позиций нет</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-500">Сумма без НДС:</td>
                            <td class="px-4 py-2 text-right text-gray-700">{{ number_format($invoice->subtotal,0,'.',' ') }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-500">НДС ({{ $invoice->tax_rate }}%):</td>
                            <td class="px-4 py-2 text-right text-gray-700">{{ number_format($invoice->tax_amount,0,'.',' ') }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-900">Итого:</td>
                            <td class="px-4 py-3 text-right text-lg font-bold text-gray-900">{{ number_format($invoice->total,0,'.',' ') }} {{ $invoice->currency }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-card>

            {{-- Payments --}}
            <x-card title="Платежи" :padding="false">
                @forelse($invoice->payments as $payment)
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-50 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ number_format($payment->amount,0,'.',' ') }} {{ $payment->currency }}</p>
                        <p class="text-xs text-gray-400">{{ match($payment->method){'bank_transfer'=>'Банк. перевод','cash'=>'Наличные','card'=>'Карта',default=>$payment->method} }} · {{ $payment->paid_at->format('d.m.Y') }}</p>
                    </div>
                    @if($payment->reference)<p class="text-xs text-gray-400 font-mono">{{ $payment->reference }}</p>@endif
                </div>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Платежей нет</div>
                @endforelse
            </x-card>

            {{-- Sells / Shipments --}}
            <x-card :padding="false">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Продажи / Отгрузки</h3>
                    @can('create', \App\Models\Sell\Sell::class)
                    <button type="button" wire:click="openShipmentModal"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Создать отгрузку
                    </button>
                    @endcan
                </div>
                @forelse($invoice->sells as $sell)
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm font-medium text-gray-900">{{ $sell->number }}</span>
                        <x-sell-status-badge :status="$sell->status" />
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-gray-500">{{ $sell->sold_at?->format('d.m.Y') ?? '—' }}</span>
                        <span class="font-medium text-gray-900">{{ number_format($sell->total, 0, '.', ' ') }} {{ $sell->currency }}</span>
                        <a href="{{ route('admin.sells.show', $sell) }}"
                           class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="px-4 py-6 text-center text-sm text-gray-400">Отгрузок ещё нет</div>
                @endforelse
            </x-card>

            {{-- Add payment form --}}
            @if($showPaymentForm)
            <x-card title="Добавить платёж">
                <form wire:submit="addPayment" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <x-input label="Сумма" type="number" wire:model="paymentAmount" :error="$errors->first('paymentAmount')" required step="0.01" />
                        <x-input label="Дата" type="date" wire:model="paymentDate" :error="$errors->first('paymentDate')" required />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <x-select label="Метод" wire:model="paymentMethod">
                            <option value="bank_transfer">Банк. перевод</option>
                            <option value="cash">Наличные</option>
                            <option value="card">Карта</option>
                        </x-select>
                        <x-input label="Референс / номер" wire:model="paymentReference" placeholder="п/п 12345" />
                    </div>
                    <div class="flex justify-end gap-3">
                        <x-button type="button" variant="secondary" wire:click="$set('showPaymentForm', false)">Отмена</x-button>
                        <x-button type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove>Сохранить платёж</span><span wire:loading>Сохранение...</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
            @endif
        </div>

        <div class="space-y-4">
            {{-- Progress --}}
            <x-card title="Оплата">
                @php $pct = $invoice->total > 0 ? min(100, round($invoice->paid_amount / $invoice->total * 100)) : 0 @endphp
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Оплачено</span>
                        <span class="font-semibold text-success-700">{{ number_format($invoice->paid_amount,0,'.',' ') }}</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="bg-success-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-400">
                        <span>{{ $pct }}%</span>
                        <span>Остаток: {{ number_format($invoice->remaining,0,'.',' ') }}</span>
                    </div>
                </div>
            </x-card>

            <x-card title="Информация">
                <dl class="space-y-2.5 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Клиент</dt>
                        <dd class="text-gray-900 font-medium mt-0.5">{{ $invoice->customer->name }}</dd>
                    </div>
                    @if($invoice->manager)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Менеджер</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $invoice->manager->name }}</dd>
                    </div>
                    @endif
                    @if($invoice->due_date)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Срок оплаты</dt>
                        <dd class="mt-0.5 @if($invoice->due_date->isPast() && !in_array($invoice->status,['paid','cancelled'])) text-danger-600 font-medium @else text-gray-700 @endif">{{ $invoice->due_date->format('d.m.Y') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Создан</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $invoice->created_at->format('d.m.Y') }}</dd>
                    </div>
                </dl>
            </x-card>

            <x-card title="Изменить статус">
                @php
                    $hasPayments = $invoice->payments->isNotEmpty();
                    $hasSells    = $invoice->sells->isNotEmpty();
                    $hasActivity = $hasPayments || $hasSells;

                    // Statuses managed automatically by payment recording — not manually settable
                    $autoStatuses = ['partially_paid', 'paid'];

                    $statuses = [
                        'draft'          => 'Черновик',
                        'sent'           => 'Отправлен',
                        'partially_paid' => 'Частично оплачен',
                        'paid'           => 'Оплачен',
                        'overdue'        => 'Просрочен',
                        'cancelled'      => 'Отменён',
                    ];
                @endphp
                <div class="space-y-1.5">
                    @foreach($statuses as $s => $label)
                    @php
                        $isActive   = $invoice->status === $s;
                        $isAuto     = in_array($s, $autoStatuses);
                        $isBlocked  = $s === 'cancelled' && $hasActivity;
                        $isDisabled = $isAuto || $isBlocked;
                    @endphp

                    @if($isDisabled)
                        <div title="{{ $isAuto ? 'Устанавливается автоматически при оплате' : 'Нельзя отменить: есть платежи или отгрузки' }}"
                             @class(['w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm cursor-not-allowed select-none',
                                 'bg-primary-50 text-primary-700 font-medium' => $isActive,
                                 'text-gray-300 bg-gray-50' => ! $isActive])>
                            <span>{{ $label }}</span>
                            <span class="text-xs font-normal opacity-70">
                                {{ $isAuto ? 'авто' : 'заблокировано' }}
                            </span>
                        </div>
                    @else
                        <button type="button" wire:click="changeStatus('{{ $s }}')"
                                @class(['w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                                    'bg-primary-50 text-primary-700 font-medium' => $isActive,
                                    'text-gray-600 hover:bg-gray-50' => ! $isActive])>
                            {{ $label }}
                        </button>
                    @endif
                    @endforeach
                </div>
                @if($hasActivity)
                <p class="mt-3 text-xs text-gray-400">
                    Статусы «Частично оплачен» и «Оплачен» управляются автоматически.
                    Отмена недоступна, так как по инвойсу есть
                    @if($hasPayments)платежи@endif
                    @if($hasPayments && $hasSells) и @endif
                    @if($hasSells)отгрузки@endif.
                </p>
                @else
                <p class="mt-3 text-xs text-gray-400">
                    Статусы «Частично оплачен» и «Оплачен» управляются автоматически при добавлении платежей.
                </p>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Serials Picker Modal --}}
    @if($showSerialsModal)
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-gray-900/50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 flex flex-col max-h-[80vh]">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Выбор серийных номеров</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Выбрано: {{ count($shipmentLines[$serialsLineIndex]['selected_serial_ids'] ?? []) }} шт.
                    </p>
                </div>
                <button type="button" wire:click="closeSerialsModal"
                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            {{-- Serial list --}}
            <div class="flex-1 overflow-y-auto divide-y divide-gray-50">
                @forelse($availableSerials as $serial)
                @php $checked = in_array($serial['id'], $shipmentLines[$serialsLineIndex]['selected_serial_ids'] ?? []) @endphp
                <label class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                    <input type="checkbox"
                           wire:click="toggleSerial({{ $serial['id'] }})"
                           @checked($checked)
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="font-mono text-sm text-gray-900">{{ $serial['serial_number'] }}</span>
                </label>
                @empty
                <div class="px-5 py-10 text-center">
                    <p class="text-sm text-gray-400">Нет доступных серийных номеров</p>
                    <p class="text-xs text-gray-400 mt-1">Добавьте серийные номера на странице товара</p>
                </div>
                @endforelse
            </div>
            {{-- Footer --}}
            <div class="px-5 py-3 border-t border-gray-200 flex justify-end">
                <x-button wire:click="closeSerialsModal">Готово</x-button>
            </div>
        </div>
    </div>
    @endif

    {{-- Shipment modal --}}
    <div x-data="{ open: $wire.entangle('showShipmentModal') }"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center">

        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/50" @click="open = false"></div>

        {{-- Panel --}}
        <div class="relative w-full max-w-2xl mx-4 bg-white rounded-xl shadow-xl flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Создать отгрузку</h2>
                <div class="flex items-center gap-3">
                    <button type="button" @click="open = false"
                            class="px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Отмена
                    </button>
                    <button type="button" wire:click="createShipment" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="createShipment">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="createShipment">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        <span wire:loading.remove wire:target="createShipment">Создать отгрузку</span>
                        <span wire:loading wire:target="createShipment">Создание...</span>
                    </button>
                </div>
            </div>

            {{-- Body --}}
            <div class="overflow-y-auto flex-1 px-6 py-4 space-y-4">

                @error('shipmentLines')
                <div class="px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">{{ $message }}</div>
                @enderror

                {{-- Items table --}}
                <div class="rounded-lg border border-gray-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="w-8 px-3 py-2.5"></th>
                                <th class="text-left px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">Товар</th>
                                <th class="text-right px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide">По инвойсу</th>
                                <th class="text-right px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide w-28">К отгрузке</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($shipmentLines as $idx => $line)
                            <tr @class(['bg-white', 'opacity-50' => empty($line['checked'])])>
                                <td class="px-3 py-3">
                                    <input type="checkbox" wire:model="shipmentLines.{{ $idx }}.checked"
                                           class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-medium text-gray-900">{{ $line['name'] }}</p>
                                    @if($line['sku'])<p class="text-xs text-gray-400 font-mono">{{ $line['sku'] }}</p>@endif
                                </td>
                                <td class="px-3 py-3 text-right text-gray-600">{{ $line['invoice_quantity'] }}</td>
                                <td class="px-3 py-3 text-right">
                                    @if(!empty($line['is_serial']))
                                        {{-- Serial product: show selected count + picker button --}}
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="text-sm font-medium text-gray-700">
                                                {{ count($line['selected_serial_ids'] ?? []) }} шт
                                            </span>
                                            @if(!empty($line['checked']))
                                            <button type="button"
                                                    wire:click="openSerialsModal({{ $idx }})"
                                                    class="text-xs text-primary-600 hover:text-primary-700 hover:underline whitespace-nowrap">
                                                Выбрать серии
                                            </button>
                                            @endif
                                        </div>
                                        @error("shipmentLines.{$idx}.quantity")
                                        <p class="text-xs text-danger-600 mt-0.5 text-left">{{ $message }}</p>
                                        @enderror
                                    @else
                                        <input type="number"
                                               wire:model="shipmentLines.{{ $idx }}.quantity"
                                               min="0.001" step="any"
                                               class="w-24 text-right text-sm rounded-md border border-gray-300 px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-50 disabled:text-gray-400"
                                               @if(empty($line['checked'])) disabled @endif>
                                        @error("shipmentLines.{$idx}.quantity")
                                        <p class="text-xs text-danger-600 mt-0.5 text-left">{{ $message }}</p>
                                        @enderror
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-400">
                                    Нет позиций с привязанным товаром для отгрузки
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                    $itemsWithoutProduct = $invoice->items->where('product_id', null)->count();
                @endphp
                @if($itemsWithoutProduct > 0)
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    {{ $itemsWithoutProduct }} {{ $itemsWithoutProduct === 1 ? 'позиция' : 'позиции' }} без привязанного товара — они не могут быть отгружены.
                </p>
                @endif

                {{-- Date & notes --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Дата отгрузки</label>
                        <input type="date" wire:model="shipmentDate"
                               class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500">
                        @error('shipmentDate')<p class="text-xs text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Примечание</label>
                        <textarea wire:model="shipmentNotes" rows="2"
                                  class="w-full text-sm rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 resize-none"
                                  placeholder="Необязательно"></textarea>
                        @error('shipmentNotes')<p class="text-xs text-danger-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
