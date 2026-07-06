<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }

        .header { background: #065f46; color: white; padding: 24px 32px; }
        .header h1 { font-size: 22px; font-weight: bold; letter-spacing: -0.5px; }
        .header .tagline { font-size: 11px; opacity: 0.75; margin-top: 3px; }
        .doc-title { float: right; text-align: right; }
        .doc-title h2 { font-size: 18px; font-weight: bold; }
        .doc-title .doc-number { font-size: 12px; opacity: 0.85; margin-top: 2px; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .meta { padding: 20px 32px; display: flex; justify-content: space-between; border-bottom: 2px solid #e5e7eb; }
        .meta-block { flex: 1; }
        .meta-block h4 { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 4px; font-weight: 600; }
        .meta-block p { font-size: 12px; font-weight: 600; color: #111827; }
        .meta-block .sub { font-size: 10px; color: #6b7280; font-weight: normal; margin-top: 1px; }
        .meta-block .quote-link { font-size: 10px; color: #065f46; font-weight: normal; margin-top: 3px; }

        .overdue-notice { margin: 12px 32px; padding: 8px 12px; background: #fef2f2; border-left: 3px solid #dc2626; font-size: 10px; color: #b91c1c; }

        .section { padding: 0 32px; margin-top: 20px; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; padding: 8px 10px; text-align: left; font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; border-bottom: 1px solid #e5e7eb; font-weight: 600; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:nth-child(even) td { background: #fafafa; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-muted { color: #6b7280; }
        .font-semibold { font-weight: 600; }

        .totals { padding: 16px 32px; }
        .totals-table { width: 300px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 5px 8px; font-size: 11px; }
        .totals-table .label { color: #6b7280; }
        .totals-table .value { text-align: right; font-weight: 600; min-width: 130px; }
        .totals-table .tax-row td { color: #374151; }
        .totals-table .total-row { border-top: 2px solid #065f46; }
        .totals-table .total-row td { font-weight: bold; font-size: 13px; padding-top: 8px; color: #065f46; }
        .totals-table .paid-row td { color: #059669; }
        .totals-table .due-row { border-top: 1px dashed #d1fae5; }
        .totals-table .due-row td { font-weight: bold; font-size: 12px; color: #1f2937; padding-top: 6px; }
        .totals-table .due-zero td { color: #059669; }

        .payments-section { padding: 0 32px 20px; }
        .payments-section h4 { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 10px; font-weight: 600; }
        .payments-section table thead th { background: #f0fdf4; color: #059669; border-bottom-color: #d1fae5; }
        .payments-section table tbody td { font-size: 10px; }

        .footer { margin: 24px 32px 0; padding: 16px 0; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; display: flex; justify-content: space-between; }
        .footer .right { text-align: right; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header clearfix">
        <div style="float: left;">
            <h1>RSG</h1>
            <p class="tagline">rsg.uz &nbsp;&middot;&nbsp; +998 71 XXX-XX-XX &nbsp;&middot;&nbsp; info@rsg.uz</p>
        </div>
        <div class="doc-title">
            <h2>Инвойс / Счёт</h2>
            <p class="doc-number">{{ $invoice->number }}</p>
        </div>
    </div>

    {{-- Overdue notice --}}
    @if(isset($invoice->due_date) && $invoice->due_date && $invoice->due_date->isPast() && ($invoice->paid_amount ?? 0) < $invoice->total)
    <div class="overdue-notice">
        Срок оплаты истёк: {{ $invoice->due_date->format('d.m.Y') }}. Пожалуйста, произведите оплату как можно скорее.
    </div>
    @endif

    {{-- Meta row --}}
    <div class="meta">
        <div class="meta-block">
            <h4>Клиент</h4>
            <p>{{ $invoice->customer?->name ?? '—' }}</p>
            @if($invoice->customer?->inn)
                <p class="sub">ИНН: {{ $invoice->customer->inn }}</p>
            @endif
            @if($invoice->customer?->address)
                <p class="sub">{{ $invoice->customer->address }}</p>
            @endif
        </div>
        <div class="meta-block">
            <h4>Дата</h4>
            <p>{{ $invoice->created_at->format('d.m.Y') }}</p>
        </div>
        <div class="meta-block">
            <h4>Срок оплаты</h4>
            <p>{{ $invoice->due_date?->format('d.m.Y') ?? '—' }}</p>
        </div>
        <div class="meta-block">
            <h4>Валюта</h4>
            <p>{{ $invoice->currency }}</p>
        </div>
        <div class="meta-block">
            <h4>Менеджер</h4>
            <p>{{ $invoice->manager?->name ?? '—' }}</p>
            @if($invoice->quote)
                <p class="quote-link">КП: {{ $invoice->quote->number }}</p>
            @endif
        </div>
    </div>

    {{-- Items table --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th style="width: 28px;">#</th>
                    <th>Наименование</th>
                    <th style="width: 50px; text-align: right;">Кол-во</th>
                    <th style="width: 110px; text-align: right;">Цена</th>
                    <th style="width: 55px; text-align: right;">НДС%</th>
                    <th style="width: 120px; text-align: right;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td class="font-semibold">{{ $item->name ?? $item->product?->name_ru ?? '—' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, '.', ' ') }}</td>
                    <td class="text-right">
                        @if(isset($item->tax_rate) && $item->tax_rate > 0)
                            {{ $item->tax_rate }}%
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-right font-semibold">{{ number_format($item->total, 2, '.', ' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="totals">
        <table class="totals-table">
            <tr>
                <td class="label">Подытог</td>
                <td class="value">{{ number_format($invoice->subtotal, 2, '.', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @if(isset($invoice->tax_amount) && $invoice->tax_amount > 0)
            <tr class="tax-row">
                <td class="label">НДС ({{ $invoice->tax_rate ?? 12 }}%)</td>
                <td class="value">{{ number_format($invoice->tax_amount, 2, '.', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Итого</td>
                <td class="value">{{ number_format($invoice->total, 2, '.', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @if(isset($invoice->paid_amount) && $invoice->paid_amount > 0)
            <tr class="paid-row">
                <td class="label">Оплачено</td>
                <td class="value">&minus;{{ number_format($invoice->paid_amount, 2, '.', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @php $remaining = $invoice->total - ($invoice->paid_amount ?? 0); @endphp
            <tr class="due-row {{ $remaining <= 0 ? 'due-zero' : '' }}">
                <td>К оплате</td>
                <td class="value">
                    @if($remaining <= 0)
                        Оплачено полностью
                    @else
                        {{ number_format($remaining, 2, '.', ' ') }} {{ $invoice->currency }}
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    {{-- Payments history --}}
    @if(isset($payments) && $payments->count() > 0)
    <div class="payments-section">
        <h4>История платежей</h4>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Дата оплаты</th>
                    <th>Способ оплаты</th>
                    <th style="text-align: right;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $j => $payment)
                <tr>
                    <td class="text-muted">{{ $j + 1 }}</td>
                    <td>{{ $payment->paid_at?->format('d.m.Y') ?? '—' }}</td>
                    <td class="text-muted">{{ $payment->method ?? '—' }}</td>
                    <td class="text-right font-semibold" style="color:#059669;">
                        {{ number_format($payment->amount, 2, '.', ' ') }} {{ $invoice->currency }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="left">
            <p>RSG &nbsp;|&nbsp; Торговое оборудование и автоматизация ритейла &nbsp;|&nbsp; rsg.uz</p>
            <p>Данный документ сформирован автоматически и действителен без подписи и печати.</p>
        </div>
        <div class="right">
            <p>{{ $invoice->number }}</p>
            <p>{{ $invoice->created_at->format('d.m.Y') }}</p>
        </div>
    </div>

</body>
</html>
