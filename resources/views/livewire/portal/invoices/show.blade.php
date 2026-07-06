<div class="max-w-4xl mx-auto">

    {{-- Page header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="/portal/invoices"
           class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <p class="text-xs text-gray-400 mb-0.5">
                <a href="/portal/invoices" class="hover:text-primary-600">Инвойсы</a>
                <span class="mx-1">/</span>
                {{ $invoice->number }}
            </p>
            <h1 class="text-xl font-bold text-gray-900">Инвойс {{ $invoice->number }}</h1>
        </div>
        <div class="flex items-center gap-2">
            <x-invoice-status-badge :status="$invoice->status" />
            <a href="{{ route('portal.invoices.pdf', $invoice) }}" target="_blank"
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

    {{-- Payment progress bar --}}
    @php
        $paidPercent = $invoice->total > 0
            ? min(100, round(($invoice->paid_amount / $invoice->total) * 100))
            : 0;
    @endphp
    @if($invoice->status !== 'draft' && $invoice->status !== 'cancelled')
        <div class="mb-4 bg-white rounded-lg border border-gray-200 shadow-card px-5 py-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Оплата</span>
                <span class="text-sm font-medium text-gray-900">{{ $paidPercent }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="h-2 rounded-full {{ $paidPercent >= 100 ? 'bg-success-500' : 'bg-primary-500' }} transition-all"
                     style="width: {{ $paidPercent }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span>Оплачено: {{ number_format($invoice->paid_amount, 0, '.', ' ') }} {{ $invoice->currency }}</span>
                <span>Итого: {{ number_format($invoice->total, 0, '.', ' ') }} {{ $invoice->currency }}</span>
            </div>
        </div>
    @endif

    {{-- Info grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Дата создания</p>
            <p class="font-medium text-gray-900">{{ $invoice->created_at->format('d.m.Y') }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Срок оплаты</p>
            <p class="font-medium {{ $invoice->due_date?->isPast() && $invoice->status !== 'paid' ? 'text-danger-600' : 'text-gray-900' }}">
                {{ $invoice->due_date?->format('d.m.Y') ?? '—' }}
            </p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Валюта</p>
            <p class="font-medium text-gray-900">{{ $invoice->currency }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Менеджер</p>
            <p class="font-medium text-gray-900">{{ $invoice->manager?->name ?? '—' }}</p>
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
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide hidden sm:table-cell">НДС</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Итого</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($items as $item)
                    <tr>
                        <td class="px-5 py-3">
                            <p class="font-medium text-gray-900">{{ $item->name ?? '—' }}</p>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ $item->quantity }}</td>
                        <td class="px-5 py-3 text-right text-gray-700 hidden sm:table-cell">
                            {{ number_format($item->unit_price, 2, '.', ' ') }}
                        </td>
                        <td class="px-5 py-3 text-right text-gray-500 hidden sm:table-cell">
                            {{ $item->tax_rate > 0 ? $item->tax_rate . '%' : '—' }}
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
                @if(isset($invoice->subtotal))
                    <div class="flex justify-between gap-12 text-sm text-gray-600">
                        <span>Подытог</span>
                        <span>{{ number_format($invoice->subtotal, 2, '.', ' ') }} {{ $invoice->currency }}</span>
                    </div>
                @endif
                @if(isset($invoice->tax_total) && $invoice->tax_total > 0)
                    <div class="flex justify-between gap-12 text-sm text-gray-600">
                        <span>НДС</span>
                        <span>{{ number_format($invoice->tax_total, 2, '.', ' ') }}</span>
                    </div>
                @endif
                @if(isset($invoice->discount_total) && $invoice->discount_total > 0)
                    <div class="flex justify-between gap-12 text-sm text-danger-600">
                        <span>Скидка</span>
                        <span>−{{ number_format($invoice->discount_total, 2, '.', ' ') }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-12 text-base font-bold text-gray-900 border-t border-gray-100 pt-1.5">
                    <span>Итого</span>
                    <span>{{ number_format($invoice->total, 2, '.', ' ') }} {{ $invoice->currency }}</span>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Payments history --}}
    @if($payments->isNotEmpty())
        <x-card>
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-gray-900">История оплат</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($payments as $payment)
                    <div class="py-3 flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ number_format($payment->amount, 2, '.', ' ') }} {{ $invoice->currency }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $payment->paid_at?->format('d.m.Y') ?? '—' }}
                                @if($payment->method)
                                    · {{ $payment->method }}
                                @endif
                            </p>
                            @if($payment->notes)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $payment->notes }}</p>
                            @endif
                        </div>
                        <x-badge color="green">Оплата</x-badge>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif

</div>
