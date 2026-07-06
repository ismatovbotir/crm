<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.5; }

        .header { background: #1d4ed8; color: white; padding: 24px 32px; }
        .header h1 { font-size: 22px; font-weight: bold; letter-spacing: -0.5px; }
        .header .tagline { font-size: 11px; opacity: 0.75; margin-top: 3px; }
        .doc-title { float: right; text-align: right; }
        .doc-title h2 { font-size: 18px; font-weight: bold; }
        .doc-title .doc-number { font-size: 12px; opacity: 0.85; margin-top: 2px; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .meta { padding: 20px 32px; display: flex; justify-content: space-between; border-bottom: 2px solid #e5e7eb; }
        .meta-block { flex: 1; }
        .meta-block h4 { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 4px; }
        .meta-block p { font-size: 12px; font-weight: 600; color: #111827; }
        .meta-block .sub { font-size: 10px; color: #6b7280; font-weight: normal; margin-top: 1px; }

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
        .totals-table { width: 280px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 5px 8px; font-size: 11px; }
        .totals-table .label { color: #6b7280; }
        .totals-table .value { text-align: right; font-weight: 600; min-width: 120px; }
        .totals-table .discount-row td { color: #dc2626; }
        .totals-table .total-row { border-top: 2px solid #1d4ed8; }
        .totals-table .total-row td { font-weight: bold; font-size: 13px; padding-top: 8px; color: #1d4ed8; }

        .notes { padding: 0 32px 16px; font-size: 10px; color: #374151; }
        .notes p + p { margin-top: 6px; }
        .notes strong { font-weight: 600; }

        .footer { margin: 24px 32px 0; padding: 16px 0; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; display: flex; justify-content: space-between; }
        .footer .left { }
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
            <h2>Коммерческое предложение</h2>
            <p class="doc-number">{{ $quote->number }}</p>
        </div>
    </div>

    {{-- Meta row --}}
    <div class="meta">
        <div class="meta-block">
            <h4>Клиент</h4>
            <p>{{ $quote->customer?->name ?? '—' }}</p>
            @if($quote->customer?->inn)
                <p class="sub">ИНН: {{ $quote->customer->inn }}</p>
            @endif
            @if($quote->customer?->address)
                <p class="sub">{{ $quote->customer->address }}</p>
            @endif
        </div>
        <div class="meta-block">
            <h4>Дата</h4>
            <p>{{ $quote->created_at->format('d.m.Y') }}</p>
        </div>
        <div class="meta-block">
            <h4>Действительно до</h4>
            <p>{{ $quote->valid_until?->format('d.m.Y') ?? '—' }}</p>
        </div>
        <div class="meta-block">
            <h4>Валюта</h4>
            <p>{{ $quote->currency }}</p>
        </div>
        <div class="meta-block">
            <h4>Менеджер</h4>
            <p>{{ $quote->manager?->name ?? '—' }}</p>
        </div>
    </div>

    {{-- Items table --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th style="width: 28px;">#</th>
                    <th>Наименование</th>
                    <th style="width: 90px;">Артикул</th>
                    <th style="width: 50px; text-align: right;">Кол-во</th>
                    <th style="width: 100px; text-align: right;">Цена</th>
                    <th style="width: 60px; text-align: right;">Скидка</th>
                    <th style="width: 110px; text-align: right;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td class="font-semibold">{{ $item->name ?? $item->product?->name_ru ?? '—' }}</td>
                    <td class="text-muted">{{ $item->sku ?? $item->product?->sku ?? '—' }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, '.', ' ') }}</td>
                    <td class="text-right">
                        @if(isset($item->discount_percent) && $item->discount_percent > 0)
                            <span style="color:#dc2626;">{{ $item->discount_percent }}%</span>
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
                <td class="value">{{ number_format($quote->subtotal, 2, '.', ' ') }} {{ $quote->currency }}</td>
            </tr>
            @if(isset($quote->discount_total) && $quote->discount_total > 0)
            <tr class="discount-row">
                <td class="label">Скидка</td>
                <td class="value">&minus;{{ number_format($quote->discount_total, 2, '.', ' ') }} {{ $quote->currency }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Итого</td>
                <td class="value">{{ number_format($quote->total, 2, '.', ' ') }} {{ $quote->currency }}</td>
            </tr>
        </table>
    </div>

    {{-- Notes & Terms --}}
    @if($quote->notes || $quote->terms)
    <div class="notes">
        @if($quote->notes)
            <p><strong>Примечания:</strong> {{ $quote->notes }}</p>
        @endif
        @if($quote->terms)
            <p><strong>Условия:</strong> {{ $quote->terms }}</p>
        @endif
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <div class="left">
            <p>RSG &nbsp;|&nbsp; Торговое оборудование и автоматизация ритейла &nbsp;|&nbsp; rsg.uz</p>
            <p>Данный документ сформирован автоматически и действителен без подписи и печати.</p>
        </div>
        <div class="right">
            <p>{{ $quote->number }}</p>
            <p>{{ $quote->created_at->format('d.m.Y') }}</p>
        </div>
    </div>

</body>
</html>
