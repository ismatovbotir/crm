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
        .doc-title .doc-date { font-size: 11px; opacity: 0.70; margin-top: 2px; }
        .clearfix::after { content: ''; display: table; clear: both; }

        .meta { padding: 20px 32px; display: flex; justify-content: space-between; border-bottom: 2px solid #e5e7eb; }
        .meta-block { flex: 1; }
        .meta-block h4 { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 4px; font-weight: 600; }
        .meta-block p { font-size: 12px; font-weight: 600; color: #111827; }
        .meta-block .sub { font-size: 10px; color: #6b7280; font-weight: normal; margin-top: 1px; }
        .meta-block .link { font-size: 10px; color: #065f46; font-weight: normal; margin-top: 3px; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: 600; }
        .status-draft     { background: #f3f4f6; color: #6b7280; }
        .status-confirmed { background: #eff6ff; color: #1d4ed8; }
        .status-shipped   { background: #f0fdf4; color: #059669; }
        .status-delivered { background: #f0fdf4; color: #065f46; }
        .status-cancelled { background: #fef2f2; color: #dc2626; }

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
        .font-mono { font-family: 'Courier New', monospace; font-size: 10px; }

        .totals { padding: 16px 32px; }
        .totals-table { width: 280px; margin-left: auto; border-collapse: collapse; }
        .totals-table td { padding: 5px 8px; font-size: 11px; }
        .totals-table .label { color: #6b7280; }
        .totals-table .value { text-align: right; font-weight: 600; min-width: 120px; }
        .totals-table .total-row { border-top: 2px solid #065f46; }
        .totals-table .total-row td { font-weight: bold; font-size: 13px; padding-top: 8px; color: #065f46; }

        .notes-section { padding: 0 32px 20px; }
        .notes-section h4 { font-size: 10px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 6px; font-weight: 600; }
        .notes-section p { font-size: 11px; color: #374151; white-space: pre-wrap; }

        .signatures { padding: 24px 32px 0; display: flex; justify-content: space-between; }
        .sig-block { flex: 1; max-width: 220px; }
        .sig-block .sig-label { font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.07em; font-weight: 600; margin-bottom: 32px; }
        .sig-block .sig-line { border-bottom: 1px solid #9ca3af; margin-bottom: 4px; }
        .sig-block .sig-name { font-size: 9px; color: #9ca3af; }

        .footer { margin: 20px 32px 0; padding: 12px 0; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; display: flex; justify-content: space-between; }
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
            <h2>Накладная на отгрузку</h2>
            <p class="doc-number">{{ $sell->number }}</p>
            <p class="doc-date">{{ $sell->sold_at?->format('d.m.Y') ?? $sell->created_at->format('d.m.Y') }}</p>
        </div>
    </div>

    {{-- Meta row --}}
    <div class="meta">
        <div class="meta-block">
            <h4>Клиент</h4>
            <p>{{ $sell->customer?->name ?? '—' }}</p>
            @if($sell->customer?->inn)
                <p class="sub">ИНН: {{ $sell->customer->inn }}</p>
            @endif
            @if($sell->customer?->address)
                <p class="sub">{{ $sell->customer->address }}</p>
            @endif
        </div>
        <div class="meta-block">
            <h4>Дата отгрузки</h4>
            <p>{{ $sell->sold_at?->format('d.m.Y') ?? '—' }}</p>
        </div>
        <div class="meta-block">
            <h4>Статус</h4>
            @php
                $statusMap   = ['draft' => 'Черновик', 'confirmed' => 'Подтверждён', 'shipped' => 'Отгружен', 'delivered' => 'Доставлен', 'cancelled' => 'Отменён'];
                $statusClass = ['draft' => 'status-draft', 'confirmed' => 'status-confirmed', 'shipped' => 'status-shipped', 'delivered' => 'status-delivered', 'cancelled' => 'status-cancelled'];
            @endphp
            <span class="status-badge {{ $statusClass[$sell->status] ?? 'status-draft' }}">
                {{ $statusMap[$sell->status] ?? $sell->status }}
            </span>
        </div>
        <div class="meta-block">
            <h4>Валюта</h4>
            <p>{{ $sell->currency }}</p>
            @if($sell->exchange_rate && $sell->currency !== 'UZS')
                <p class="sub">Курс: {{ number_format($sell->exchange_rate, 2, '.', ' ') }}</p>
            @endif
        </div>
        <div class="meta-block">
            <h4>Менеджер</h4>
            <p>{{ $sell->manager?->name ?? '—' }}</p>
            @if($sell->invoice)
                <p class="link">Инвойс: {{ $sell->invoice->number }}</p>
            @endif
        </div>
    </div>

    {{-- Items table --}}
    <div class="section">
        <table>
            <thead>
                <tr>
                    <th style="width: 28px;">#</th>
                    <th>Наименование / Артикул</th>
                    <th style="width: 60px; text-align: right;">Кол-во</th>
                    <th style="width: 110px; text-align: right;">Цена</th>
                    <th style="width: 55px; text-align: right;">Скидка</th>
                    <th style="width: 120px; text-align: right;">Сумма</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $i => $item)
                <tr>
                    <td class="text-muted">{{ $i + 1 }}</td>
                    <td>
                        <span class="font-semibold">{{ $item->product?->name_ru ?? $item->product?->name ?? '—' }}</span>
                        @if($item->product?->sku)
                            <br><span class="text-muted font-mono">{{ $item->product->sku }}</span>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2, '.', ' ') }}</td>
                    <td class="text-right text-muted">
                        {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}
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
            @if($sell->subtotal != $sell->total)
            <tr>
                <td class="label">Подытог</td>
                <td class="value">{{ number_format($sell->subtotal, 2, '.', ' ') }} {{ $sell->currency }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Итого</td>
                <td class="value">{{ number_format($sell->total, 2, '.', ' ') }} {{ $sell->currency }}</td>
            </tr>
        </table>
    </div>

    {{-- Notes --}}
    @if($sell->notes)
    <div class="notes-section">
        <h4>Примечание</h4>
        <p>{{ $sell->notes }}</p>
    </div>
    @endif

    {{-- Signature lines --}}
    <div class="signatures">
        <div class="sig-block">
            <p class="sig-label">Сдал (продавец)</p>
            <div class="sig-line"></div>
            <p class="sig-name">{{ $sell->manager?->name ?? '' }}</p>
        </div>
        <div class="sig-block" style="text-align: right; margin-left: auto;">
            <p class="sig-label">Принял (клиент)</p>
            <div class="sig-line"></div>
            <p class="sig-name">{{ $sell->customer?->name ?? '' }}</p>
        </div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <div class="left">
            <p>RSG &nbsp;|&nbsp; Торговое оборудование и автоматизация ритейла &nbsp;|&nbsp; rsg.uz</p>
            <p>Документ сформирован автоматически системой RSG-CRM.</p>
        </div>
        <div class="right">
            <p>{{ $sell->number }}</p>
            <p>Сформирован: {{ now()->format('d.m.Y H:i') }}</p>
        </div>
    </div>

</body>
</html>
