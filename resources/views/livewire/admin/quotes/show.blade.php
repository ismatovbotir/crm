<div class="max-w-6xl mx-auto">
    @if(session('success'))<div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">{{ session('error') }}</div>@endif

    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.quotes.index') }}" class="hover:text-primary-600">КП</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">{{ $quote->number }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $quote->number }}</h1>
            <p class="text-sm text-gray-500">{{ $quote->customer->name }}</p>
            @if($quote->equipment_request_id)
            <p class="text-xs text-gray-400 mt-1">
                Оформлено из заявки на оборудование
                <a href="{{ route('admin.equipment-requests.show', $quote->equipment_request_id) }}" class="text-primary-600 hover:underline">#{{ $quote->equipment_request_id }}</a>
            </p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <x-quote-status-badge :status="$quote->status" />

            {{-- Редактировать: только если не финальный статус и нет инвойса --}}
            @if(!in_array($quote->status, ['accepted', 'rejected', 'expired']) && !$quote->invoice)
            <a href="{{ route('admin.quotes.edit', $quote) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </a>
            @endif

            <a href="{{ route('admin.quotes.pdf', $quote) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF
            </a>

            {{-- Создать инвойс: только принятое КП без инвойса --}}
            @if($quote->status === 'accepted' && !$quote->invoice)
            <button wire:click="convertToInvoice" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors disabled:opacity-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" wire:loading.remove wire:target="convertToInvoice">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" wire:loading wire:target="convertToInvoice">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span wire:loading.remove wire:target="convertToInvoice">Создать инвойс</span>
                <span wire:loading wire:target="convertToInvoice">Создание...</span>
            </button>
            @endif

            {{-- Ссылка на инвойс если уже создан --}}
            @if($quote->invoice)
            <a href="{{ route('admin.invoices.show', $quote->invoice) }}"
               class="inline-flex items-center gap-1.5 px-4 py-1.5 text-sm font-medium rounded-lg bg-success-50 text-success-700 border border-success-200 hover:bg-success-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Инвойс {{ $quote->invoice->number }}
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-4">
            <x-card title="Позиции КП" :padding="false">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50/50">
                            <th class="text-left px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide w-8">#</th>
                            <th class="text-left px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Товар</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Кол.</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Цена</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Скидка</th>
                            <th class="text-right px-4 py-2 text-xs text-gray-500 font-semibold uppercase tracking-wide">Итого</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($quote->items as $i => $item)
                        <tr>
                            <td class="px-4 py-2.5 text-gray-400 text-xs">{{ $i+1 }}</td>
                            <td class="px-4 py-2.5">
                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                @if($item->sku)<p class="text-xs text-gray-400 font-mono">{{ $item->sku }}</p>@endif
                            </td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ $item->quantity }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-700">{{ number_format($item->unit_price,0,'.',' ') }}</td>
                            <td class="px-4 py-2.5 text-right text-gray-500">{{ $item->discount_percent > 0 ? $item->discount_percent.'%' : '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold text-gray-900">{{ number_format($item->total,0,'.',' ') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Позиций нет</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        @if($quote->discount_total > 0)
                        <tr>
                            <td colspan="5" class="px-4 py-2 text-right text-sm text-gray-500">Скидка:</td>
                            <td class="px-4 py-2 text-right text-danger-600 font-medium">−{{ number_format($quote->discount_total,0,'.',' ') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-900">Итого:</td>
                            <td class="px-4 py-3 text-right text-lg font-bold text-gray-900">{{ number_format($quote->total,0,'.',' ') }} {{ $quote->currency }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-card>

            <x-card title="Изменить статус">
                <div class="flex flex-wrap gap-2">
                    @foreach(['draft'=>'Черновик','sent'=>'Отправлен','viewed'=>'Просмотрен','accepted'=>'Принят','rejected'=>'Отклонён','expired'=>'Истёк'] as $s => $label)
                    <button wire:click="changeStatus('{{ $s }}')"
                            @class(['px-3 py-1.5 rounded-lg text-sm font-medium border transition-colors',
                                'bg-primary-600 text-white border-primary-600'=>$quote->status===$s,
                                'text-gray-600 border-gray-200 hover:bg-gray-50'=>$quote->status!==$s])>{{ $label }}</button>
                    @endforeach
                </div>
            </x-card>
        </div>

        <div class="space-y-4">
            <x-card title="Клиент">
                <dl class="space-y-2.5 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Компания</dt>
                        <dd class="text-gray-900 font-medium mt-0.5">{{ $quote->customer->name }}</dd>
                    </div>
                    @if($quote->customer->phone)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Телефон</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $quote->customer->phone }}</dd>
                    </div>
                    @endif
                    @if($quote->manager)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Менеджер</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $quote->manager->name }}</dd>
                    </div>
                    @endif
                    @if($quote->valid_until)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Действует до</dt>
                        <dd class="mt-0.5 @if($quote->valid_until->isPast()) text-danger-600 @else text-gray-700 @endif">{{ $quote->valid_until->format('d.m.Y') }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Версия</dt>
                        <dd class="text-gray-700 mt-0.5">v{{ $quote->version }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Создан</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $quote->created_at->format('d.m.Y') }}</dd>
                    </div>
                </dl>
            </x-card>

            @if($quote->versions->count() > 0)
            <x-card title="История версий" :padding="false">
                @foreach($quote->versions as $ver)
                <div class="px-4 py-2.5 border-b border-gray-50 last:border-0">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">v{{ $ver->version }}</span>
                        <span class="text-xs text-gray-400">{{ $ver->created_at->format('d.m.Y') }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ number_format($ver->total,0,'.',' ') }} · {{ $ver->creator->name }}</p>
                </div>
                @endforeach
            </x-card>
            @endif
        </div>
    </div>
</div>
