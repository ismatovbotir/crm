<div class="max-w-4xl mx-auto">

    {{-- Page header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="/portal/quotes"
           class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <p class="text-xs text-gray-400 mb-0.5">
                <a href="/portal/quotes" class="hover:text-primary-600">КП</a>
                <span class="mx-1">/</span>
                {{ $quote->number }}
            </p>
            <h1 class="text-xl font-bold text-gray-900">КП {{ $quote->number }}</h1>
            @if($quote->equipment_request_id)
            <p class="text-xs text-gray-400 mt-0.5">
                Оформлено из заявки на оборудование
                <a href="{{ route('portal.equipment-requests.show', $quote->equipment_request_id) }}" class="text-primary-600 hover:underline">#{{ $quote->equipment_request_id }}</a>
            </p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <x-quote-status-badge :status="$quote->status" />
            <a href="{{ route('portal.quotes.pdf', $quote) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Скачать PDF
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Accept / Reject action block --}}
    @if(in_array($quote->status, ['sent', 'viewed']))
        <div class="mb-4 bg-primary-50 border border-primary-200 rounded-lg px-5 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="font-medium text-primary-900">Вы можете принять или отклонить это предложение</p>
                    <p class="text-sm text-primary-700 mt-0.5">
                        Действительно до {{ $quote->valid_until?->format('d.m.Y') ?? '—' }}
                    </p>
                </div>
                <div class="flex gap-3 flex-shrink-0">
                    <x-button wire:click="reject"
                              wire:confirm="Вы уверены, что хотите отклонить это КП?"
                              variant="secondary"
                              class="text-danger-600 border-danger-300 hover:bg-danger-50">
                        Отклонить
                    </x-button>
                    <x-button wire:click="accept"
                              wire:confirm="Подтвердить принятие этого КП?">
                        Принять
                    </x-button>
                </div>
            </div>
        </div>
    @endif

    {{-- Info grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Дата создания</p>
            <p class="font-medium text-gray-900">{{ $quote->created_at->format('d.m.Y') }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Действительно до</p>
            <p class="font-medium {{ $quote->valid_until?->isPast() ? 'text-danger-600' : 'text-gray-900' }}">
                {{ $quote->valid_until?->format('d.m.Y') ?? '—' }}
            </p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Валюта</p>
            <p class="font-medium text-gray-900">{{ $quote->currency }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Менеджер</p>
            <p class="font-medium text-gray-900">{{ $quote->manager?->name ?? '—' }}</p>
        </x-card>
    </div>

    {{-- Items table --}}
    <x-card :padding="false" class="mb-4">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Позиции</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">Товар</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Кол-во</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide hidden sm:table-cell">Цена</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide hidden sm:table-cell">Скидка</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Итого</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $item)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">
                                {{ $item->product?->name_ru ?? $item->product_name ?? '—' }}
                            </p>
                            @if($item->product?->sku)
                                <p class="text-xs text-gray-400 font-mono">{{ $item->product->sku }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ $item->quantity }}</td>
                        <td class="px-5 py-3 text-right text-gray-700 hidden sm:table-cell">
                            {{ number_format($item->unit_price, 2, '.', ' ') }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500 hidden sm:table-cell">
                            {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right font-medium text-gray-900">
                            {{ number_format($item->total, 2, '.', ' ') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="border-t border-gray-100 px-5 py-4 flex justify-end">
            <div class="text-right space-y-1.5 min-w-48">
                <div class="flex justify-between gap-12 text-sm text-gray-600">
                    <span>Подытог</span>
                    <span>{{ number_format($quote->subtotal, 2, '.', ' ') }} {{ $quote->currency }}</span>
                </div>
                @if($quote->discount_total > 0)
                    <div class="flex justify-between gap-12 text-sm text-danger-600">
                        <span>Скидка</span>
                        <span>−{{ number_format($quote->discount_total, 2, '.', ' ') }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-12 text-base font-bold text-gray-900 border-t border-gray-100 pt-1.5">
                    <span>Итого</span>
                    <span>{{ number_format($quote->total, 2, '.', ' ') }} {{ $quote->currency }}</span>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Notes / Terms --}}
    @if($quote->notes || $quote->terms)
        <x-card>
            @if($quote->notes)
                <div class="{{ $quote->terms ? 'mb-4' : '' }}">
                    <p class="text-xs text-gray-500 uppercase font-medium tracking-wide mb-1">Примечания</p>
                    <p class="text-sm text-gray-700">{{ $quote->notes }}</p>
                </div>
            @endif
            @if($quote->terms)
                <div>
                    <p class="text-xs text-gray-500 uppercase font-medium tracking-wide mb-1">Условия</p>
                    <p class="text-sm text-gray-700">{{ $quote->terms }}</p>
                </div>
            @endif
        </x-card>
    @endif

</div>
