<div>
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Дашборд</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ now()->translatedFormat('F Y') }} · обновлено только что</p>
        </div>
        <div class="flex items-center gap-2">
            @can('leads.create')
            <button
                type="button"
                @click="$dispatch('open-lead-modal')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Новый лид
            </button>
            @endcan
            @can('quotes.create')
            <button
                type="button"
                @click="$dispatch('open-quote-modal')"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Новый КП
            </button>
            @endcan
        </div>
    </div>

    {{-- KPI Cards — 3 on mobile, 6 on desktop --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">

        {{-- Новые лиды --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Лиды</span>
                <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $newLeadsCount }}</p>
            <p class="text-xs mt-1.5 @if($leadsChange > 0) text-success-600 @elseif($leadsChange < 0) text-danger-600 @else text-gray-400 @endif">
                @if($leadsChange !== null)
                    {{ $leadsChange > 0 ? '▲ +' : '▼ ' }}{{ $leadsChange }}% к прошлому мес.
                @else
                    За текущий месяц
                @endif
            </p>
        </div>

        {{-- Клиенты --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Клиенты</span>
                <div class="w-8 h-8 bg-success-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $activeCustomers }}</p>
            <p class="text-xs mt-1.5 text-gray-400">Активных</p>
        </div>

        {{-- Выручка --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Выручка</span>
                <div class="w-8 h-8 bg-warning-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">
                @if($monthRevenue >= 1000000)
                    {{ number_format($monthRevenue / 1000000, 1) }}M
                @elseif($monthRevenue >= 1000)
                    {{ number_format($monthRevenue / 1000, 0) }}K
                @else
                    {{ number_format($monthRevenue, 0, '.', ' ') }}
                @endif
            </p>
            <p class="text-xs mt-1.5 @if($revenueChange > 0) text-success-600 @elseif($revenueChange < 0) text-danger-600 @else text-gray-400 @endif">
                @if($revenueChange !== null)
                    {{ $revenueChange > 0 ? '▲ +' : '▼ ' }}{{ $revenueChange }}% к прошлому мес.
                @else
                    UZS за месяц
                @endif
            </p>
        </div>

        {{-- Открытые КП --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">КП</span>
                <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $openQuotesCount }}</p>
            <p class="text-xs mt-1.5 text-gray-400">Отправлено / просмотрено</p>
        </div>

        {{-- Тикеты --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Тикеты</span>
                <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $openTicketsCount }}</p>
            <p class="text-xs mt-1.5 {{ $criticalTickets > 0 ? 'text-danger-600' : 'text-gray-400' }}">
                {{ $criticalTickets > 0 ? $criticalTickets . ' критичных' : 'Открытых' }}
            </p>
        </div>

        {{-- Просрочено --}}
        <div class="bg-white rounded-xl border border-{{ $overdueCount > 0 ? 'danger' : 'gray' }}-200 p-4 {{ $overdueCount > 0 ? 'bg-danger-50/30' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Просрочено</span>
                <div class="w-8 h-8 {{ $overdueCount > 0 ? 'bg-danger-100' : 'bg-gray-50' }} rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 {{ $overdueCount > 0 ? 'text-danger-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold {{ $overdueCount > 0 ? 'text-danger-600' : 'text-gray-900' }}">{{ $overdueCount }}</p>
            <p class="text-xs mt-1.5 {{ $overdueCount > 0 ? 'text-danger-500' : 'text-gray-400' }}">
                {{ $overdueCount > 0 ? 'инвойс(ов) просрочено' : 'Просрочек нет' }}
            </p>
        </div>

    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        <div class="bg-white rounded-xl border border-gray-200 p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Продажи по неделям</h3>
                <span class="text-xs text-gray-400">UZS, оплаченных</span>
            </div>
            <div class="h-52"><canvas id="salesChart"></canvas></div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Лиды по источникам</h3>
            </div>
            <div class="h-52"><canvas id="sourcesChart"></canvas></div>
        </div>

    </div>

    {{-- Recent leads --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Последние лиды</h3>
            <a href="{{ route('admin.leads.index') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                Все лиды →
            </a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50/60 border-b border-gray-100">
                <tr>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wide">Имя / Компания</th>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wide hidden lg:table-cell">Источник</th>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wide hidden md:table-cell">Менеджер</th>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wide">Статус</th>
                    <th class="px-5 py-3 text-left text-xs text-gray-500 font-semibold uppercase tracking-wide hidden md:table-cell">Дата</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentLeads as $lead)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-medium text-gray-900">{{ $lead->name }}</p>
                        @if($lead->company)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $lead->company }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs hidden lg:table-cell">
                        {{ $lead->source?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs hidden md:table-cell">
                        {{ $lead->manager?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3">
                        <x-lead-status-badge :status="$lead->status" />
                    </td>
                    <td class="px-5 py-3 text-gray-400 text-xs hidden md:table-cell">
                        {{ $lead->created_at->diffForHumans() }}
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.leads.show', $lead->id) }}"
                           class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-12 text-center text-gray-400 text-sm">Лидов пока нет</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modals --}}
    @can('leads.create')
    <x-modal title="Новый лид" open-event="open-lead-modal" close-event="close-lead-modal" save-event="lead-saved" form-id="lead-create-form" save-label="Создать" cancel-event="close-lead-modal">
        <livewire:admin.leads.create-form />
    </x-modal>
    @endcan

    @can('quotes.create')
    <x-modal title="Новое КП" open-event="open-quote-modal" close-event="close-quote-modal" save-event="quote-saved" width="max-w-7xl" form-id="quote-create-form" save-label="Создать КП" cancel-event="close-quote-modal">
        <livewire:admin.quotes.create-form />
    </x-modal>
    @endcan

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const weeklyData = @json($weeklyRevenue);
        const sourcesData = @json($leadSources);

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: weeklyData.map(d => d.label),
                datasets: [{
                    label: 'Продажи',
                    data: weeklyData.map(d => d.value),
                    backgroundColor: 'rgba(59,130,246,0.15)',
                    borderColor: '#3B82F6',
                    borderWidth: 2,
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: v => v >= 1000000
                                ? (v / 1000000).toFixed(1) + 'M'
                                : v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v
                        },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        new Chart(document.getElementById('sourcesChart'), {
            type: 'doughnut',
            data: {
                labels: sourcesData.map(d => d.label),
                datasets: [{
                    data: sourcesData.map(d => d.value),
                    backgroundColor: ['#3B82F6','#10B981','#F59E0B','#EF4444','#8B5CF6','#EC4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 11 }, padding: 10 }
                    }
                }
            }
        });
    });
</script>
@endpush
