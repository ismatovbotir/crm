<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Добро пожаловать{{ $customer ? ', ' . $customer->name : '' }}!</h1>
        <p class="text-sm text-gray-500 mt-0.5">Обзор ваших документов и обращений</p>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <a href="/portal/quotes" class="block">
            <div class="bg-white rounded-lg border border-gray-200 shadow-card p-5 hover:shadow-md transition-shadow">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Открытых КП</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900">{{ $openQuotesCount }}</p>
                <p class="mt-1 text-xs text-primary-600">Просмотреть все →</p>
            </div>
        </a>
        <a href="/portal/invoices" class="block">
            <div class="bg-white rounded-lg border border-gray-200 shadow-card p-5 hover:shadow-md transition-shadow">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Неоплаченных счетов</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900">{{ $unpaidInvoicesCount }}</p>
                <p class="mt-1 text-xs text-warning-600">Просмотреть все →</p>
            </div>
        </a>
        <a href="/portal/tickets" class="block">
            <div class="bg-white rounded-lg border border-gray-200 shadow-card p-5 hover:shadow-md transition-shadow">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Открытых тикетов</p>
                <p class="mt-1.5 text-2xl font-bold text-gray-900">{{ $openTicketsCount }}</p>
                <p class="mt-1 text-xs text-danger-600">Просмотреть все →</p>
            </div>
        </a>
    </div>

    {{-- Recent quotes + recent tickets --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent quotes --}}
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Последние КП</h2>
                <a href="/portal/quotes" class="text-sm text-primary-600 hover:underline">Все</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentQuotes as $q)
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="/portal/quotes/{{ $q->id }}"
                               class="text-sm font-medium text-gray-900 hover:text-primary-600 block truncate">
                                {{ $q->number }}
                            </a>
                            <p class="text-xs text-gray-400">
                                {{ $q->created_at->format('d.m.Y') }}
                                · {{ number_format($q->total, 0, '.', ' ') }} {{ $q->currency }}
                            </p>
                        </div>
                        <x-quote-status-badge :status="$q->status" />
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">Нет коммерческих предложений</p>
                @endforelse
            </div>
        </x-card>

        {{-- Recent tickets --}}
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-gray-900">Последние тикеты</h2>
                <a href="/portal/tickets" class="text-sm text-primary-600 hover:underline">Все</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentTickets as $t)
                    <div class="py-2.5 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <a href="/portal/tickets/{{ $t->id }}"
                               class="text-sm font-medium text-gray-900 hover:text-primary-600 block truncate">
                                {{ $t->subject }}
                            </a>
                            <p class="text-xs text-gray-400">
                                {{ $t->number }} · {{ $t->created_at->format('d.m.Y') }}
                            </p>
                        </div>
                        <x-ticket-status-badge :status="$t->status" />
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">Нет обращений</p>
                @endforelse
            </div>
        </x-card>

    </div>
</div>
