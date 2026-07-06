<div class="max-w-7xl mx-auto">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Отчёты</h1>
            <p class="text-sm text-gray-500 mt-1">Аналитика продаж и активности</p>
        </div>
    </div>

    {{-- Period selector --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Период:</span>
            <div class="flex rounded-lg border border-gray-200 overflow-hidden divide-x divide-gray-200">
                @foreach(['month' => 'Месяц', 'quarter' => 'Квартал', 'year' => 'Год', 'custom' => 'Произвольный'] as $value => $label)
                <button
                    wire:click="$set('period', '{{ $value }}')"
                    @class([
                        'px-4 py-2 text-sm font-medium transition-colors',
                        'bg-primary-600 text-white'      => $period === $value,
                        'bg-white text-gray-600 hover:bg-gray-50' => $period !== $value,
                    ])
                >{{ $label }}</button>
                @endforeach
            </div>
            @if($period === 'custom')
            <div class="flex items-center gap-2 ml-2">
                <x-input type="date" wire:model.live="dateFrom" class="w-40" />
                <span class="text-gray-400 text-sm">—</span>
                <x-input type="date" wire:model.live="dateTo" class="w-40" />
            </div>
            @else
            <span class="text-sm text-gray-500">
                {{ \Carbon\Carbon::parse($dateFrom)->format('d.m.Y') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d.m.Y') }}
            </span>
            @endif
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">

        {{-- Выручка --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 col-span-2 md:col-span-1">
            <p class="text-xs text-gray-500 uppercase font-medium tracking-wide">Выручка</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ $this->kpi['revenue'] >= 1000000
                    ? number_format($this->kpi['revenue'] / 1000000, 1) . 'M'
                    : number_format($this->kpi['revenue'], 0, '.', ' ') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">UZS, оплаченных</p>
        </div>

        {{-- Инвойсов --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-medium tracking-wide">Инвойсов</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $this->kpi['invoiceCount'] }}</p>
            <p class="text-xs text-gray-400 mt-1">за период</p>
        </div>

        {{-- Средний чек --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-medium tracking-wide">Средний чек</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ $this->kpi['avgDeal'] >= 1000000
                    ? number_format($this->kpi['avgDeal'] / 1000000, 1) . 'M'
                    : number_format($this->kpi['avgDeal'], 0, '.', ' ') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">UZS</p>
        </div>

        {{-- Новых лидов --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-medium tracking-wide">Новых лидов</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $this->kpi['newLeads'] }}</p>
            <p class="text-xs text-gray-400 mt-1">за период</p>
        </div>

        {{-- Конверсия --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-medium tracking-wide">Конверсия</p>
            <p class="text-2xl font-bold mt-1 {{ $this->kpi['conversion'] >= 20 ? 'text-success-700' : ($this->kpi['conversion'] > 0 ? 'text-warning-600' : 'text-gray-400') }}">
                {{ $this->kpi['conversion'] }}%
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $this->kpi['wonLeads'] }} выиграно</p>
        </div>

        {{-- Просроченных --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-xs text-gray-500 uppercase font-medium tracking-wide">Просроченных</p>
            <p class="text-2xl font-bold mt-1 {{ $this->kpi['overdueCount'] > 0 ? 'text-danger-600' : 'text-gray-900' }}">
                {{ $this->kpi['overdueCount'] }}
            </p>
            <p class="text-xs {{ $this->kpi['overdueCount'] > 0 ? 'text-danger-500' : 'text-gray-400' }} mt-1">
                {{ $this->kpi['overdueCount'] > 0 ? 'инвойс(ов) просрочено' : 'просрочек нет' }}
            </p>
        </div>

    </div>

    {{-- Revenue Chart --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Продажи по месяцам (последние 12 мес.)</h3>
        <div class="h-64"><canvas id="revenueChart"></canvas></div>
    </div>

    {{-- Funnel + Top Managers --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Lead Funnel --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Воронка лидов</h3>
            </div>
            @php
                $maxFunnelCount = collect($this->leadFunnel)->max('count') ?: 1;
            @endphp
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">Статус</th>
                        <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide w-16">Кол-во</th>
                        <th class="px-5 py-3 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">Доля</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($this->leadFunnel as $row)
                    <tr>
                        <td class="px-5 py-3 text-gray-800 font-medium">{{ $row['label'] }}</td>
                        <td class="px-5 py-3 text-right text-gray-700 font-semibold">{{ $row['count'] }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div
                                        class="h-2 rounded-full {{ match($row['status']) {
                                            'won'  => 'bg-success-500',
                                            'lost' => 'bg-danger-400',
                                            default => 'bg-primary-400'
                                        } }}"
                                        style="width: {{ $maxFunnelCount > 0 ? round($row['count'] / $maxFunnelCount * 100) : 0 }}%"
                                    ></div>
                                </div>
                                <span class="text-xs text-gray-400 w-8 text-right">
                                    {{ $maxFunnelCount > 0 ? round($row['count'] / $maxFunnelCount * 100) : 0 }}%
                                </span>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Top Managers --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Топ менеджеры</h3>
                <p class="text-xs text-gray-400 mt-0.5">По выручке за выбранный период</p>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">Менеджер</th>
                        <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Инвойсов</th>
                        <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Выручка</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($this->topManagers as $i => $row)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400 w-4">{{ $i + 1 }}</span>
                                <span class="font-medium text-gray-900">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-700">{{ $row['invoice_count'] }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            {{ $row['total_paid'] >= 1000000
                                ? number_format($row['total_paid'] / 1000000, 1) . 'M'
                                : number_format($row['total_paid'], 0, '.', ' ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-sm text-gray-400">
                            Данных за период нет
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Overdue Invoices --}}
    @if(count($this->overdueInvoices) > 0)
    <div class="bg-white rounded-lg border border-danger-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-danger-100 bg-danger-50">
            <h3 class="text-sm font-semibold text-danger-800">
                Просроченные инвойсы
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-700">
                    {{ count($this->overdueInvoices) }}
                </span>
            </h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">Номер</th>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-medium uppercase tracking-wide">Клиент</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Сумма</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Просрочен</th>
                    <th class="px-5 py-3 text-right text-xs text-gray-500 font-medium uppercase tracking-wide">Остаток</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($this->overdueInvoices as $invoice)
                @php $daysOverdue = \Carbon\Carbon::parse($invoice->due_date)->diffInDays(today()) @endphp
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 font-mono text-xs text-gray-700 font-semibold">{{ $invoice->number }}</td>
                    <td class="px-5 py-3 text-gray-900 font-medium">{{ $invoice->customer->name }}</td>
                    <td class="px-5 py-3 text-right text-gray-700">
                        {{ number_format($invoice->total, 0, '.', ' ') }}
                        <span class="text-xs text-gray-400">{{ $invoice->currency }}</span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <span class="text-danger-600 font-semibold">{{ $daysOverdue }} дн.</span>
                        <p class="text-xs text-gray-400">до {{ \Carbon\Carbon::parse($invoice->due_date)->format('d.m.Y') }}</p>
                    </td>
                    <td class="px-5 py-3 text-right font-semibold text-danger-700">
                        {{ number_format($invoice->remaining, 0, '.', ' ') }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.invoices.show', $invoice) }}"
                           class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                            Открыть →
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white rounded-lg border border-gray-200 p-8 text-center">
        <svg class="w-10 h-10 mx-auto mb-3 text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm font-medium text-gray-700">Просроченных инвойсов нет</p>
        <p class="text-xs text-gray-400 mt-1">Все инвойсы оплачены в срок</p>
    </div>
    @endif

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const monthlyData = @json($this->salesByMonth);

        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.label),
                datasets: [{
                    label: 'Выручка (UZS)',
                    data: monthlyData.map(d => d.value),
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3B82F6',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (v) {
                                return (v / 1000000).toFixed(1) + 'M';
                            }
                        },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
