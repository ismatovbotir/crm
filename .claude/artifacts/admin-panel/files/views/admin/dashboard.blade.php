@extends('layouts.admin')

@section('title', 'Дашборд')

@section('content')

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Дашборд</h1>
        <p class="text-sm text-gray-500 mt-1">Обзор ключевых метрик за текущий месяц</p>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Лиды --}}
        <div class="bg-white rounded-lg shadow-card p-6">
            <div class="flex items-center gap-4">
                <div class="bg-primary-100 text-primary-600 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Новые лиды</p>
                    <p class="text-3xl font-bold text-gray-900">42</p>
                </div>
            </div>
            <p class="text-sm text-success-600 mt-3">▲ +12% к прошлому месяцу</p>
        </div>

        {{-- Конверсия --}}
        <div class="bg-white rounded-lg shadow-card p-6">
            <div class="flex items-center gap-4">
                <div class="bg-success-100 text-success-600 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Конверсия</p>
                    <p class="text-3xl font-bold text-gray-900">28%</p>
                </div>
            </div>
            <p class="text-sm text-success-600 mt-3">▲ +5% к прошлому месяцу</p>
        </div>

        {{-- Продажи --}}
        <div class="bg-white rounded-lg shadow-card p-6">
            <div class="flex items-center gap-4">
                <div class="bg-warning-100 text-warning-600 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 8c-1.7 0-3 1.3-3 3s1.3 3 3 3 3 1.3 3 3-1.3 3-3 3m0-12V5m0 14v-2m0-12a9 9 0 110 18 9 9 0 010-18z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Продажи</p>
                    <p class="text-3xl font-bold text-gray-900">12.4M</p>
                </div>
            </div>
            <p class="text-sm text-danger-600 mt-3">▼ -3% к прошлому месяцу</p>
        </div>

        {{-- Тикеты --}}
        <div class="bg-white rounded-lg shadow-card p-6">
            <div class="flex items-center gap-4">
                <div class="bg-danger-100 text-danger-600 p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M18.4 5.6a9 9 0 11-12.7 0M12 13V3"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-500">Открытые тикеты</p>
                    <p class="text-3xl font-bold text-gray-900">8</p>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-3">2 critical · 6 normal</p>
        </div>

    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

        {{-- Sales chart --}}
        <div class="bg-white rounded-lg shadow-card p-6 lg:col-span-2">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Продажи по неделям</h3>
            <div class="h-64">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Sources pie --}}
        <div class="bg-white rounded-lg shadow-card p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Лиды по источникам</h3>
            <div class="h-64">
                <canvas id="sourcesChart"></canvas>
            </div>
        </div>

    </div>

    {{-- Recent leads --}}
    <div class="bg-white rounded-lg shadow-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Последние лиды</h3>
            <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium">Все лиды →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Имя</th>
                        <th class="px-6 py-3 text-left font-medium">Компания</th>
                        <th class="px-6 py-3 text-left font-medium">Источник</th>
                        <th class="px-6 py-3 text-left font-medium">Статус</th>
                        <th class="px-6 py-3 text-left font-medium">Создан</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach([
                        ['name'=>'Иван Иванов',  'company'=>'ООО "Магнит"',   'source'=>'Сайт',     'status'=>'Quoted',         'badge'=>'info',    'date'=>'сегодня'],
                        ['name'=>'Анна Белова',  'company'=>'Кафе "Лето"',    'source'=>'Звонок',   'status'=>'New',            'badge'=>'primary', 'date'=>'сегодня'],
                        ['name'=>'Пётр Сергеев', 'company'=>'ИП Сергеев',     'source'=>'Сайт',     'status'=>'Negotiation',    'badge'=>'warning', 'date'=>'вчера'],
                        ['name'=>'Мария Орлова', 'company'=>'Аптека "Здоровье"','source'=>'Реклама','status'=>'Won',             'badge'=>'success', 'date'=>'вчера'],
                        ['name'=>'Игорь Кузнецов','company'=>'Ресторан "Восток"','source'=>'Звонок','status'=>'Lost',           'badge'=>'danger',  'date'=>'2 дня'],
                    ] as $lead)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $lead['name'] }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $lead['company'] }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $lead['source'] }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            bg-{{ $lead['badge'] }}-100 text-{{ $lead['badge'] }}-700">
                                    {{ $lead['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $lead['date'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales chart
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: ['W1', 'W2', 'W3', 'W4', 'W5'],
            datasets: [{
                label: 'Продажи (млн UZS)',
                data: [2.1, 3.4, 2.8, 4.2, 3.9],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // Sources pie
    new Chart(document.getElementById('sourcesChart'), {
        type: 'doughnut',
        data: {
            labels: ['Сайт', 'Звонок', 'Реклама'],
            datasets: [{
                data: [45, 30, 25],
                backgroundColor: ['#3B82F6', '#10B981', '#F59E0B'],
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endpush
