{{-- Logo --}}
<div class="h-16 flex items-center px-6 border-b border-gray-200">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">R</span>
        </div>
        <span class="font-semibold text-gray-900">RSG-CRM</span>
    </a>
</div>

{{-- Navigation --}}
<nav class="px-3 py-4 space-y-1 overflow-y-auto" style="height: calc(100vh - 4rem - 4rem);">

    @php
        $items = [
            ['label' => 'Дашборд',  'route' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'Лиды',     'route' => 'admin.leads.index', 'icon' => 'user-plus', 'badge' => 12],
            ['label' => 'Клиенты',  'route' => 'admin.customers.index', 'icon' => 'building-office'],
            ['label' => 'КП',       'route' => 'admin.quotes.index', 'icon' => 'document-text'],
            ['label' => 'Инвойсы',  'route' => 'admin.invoices.index', 'icon' => 'currency-dollar'],
            ['label' => 'Каталог',  'route' => 'admin.catalog.products.index', 'icon' => 'cube'],
            ['label' => 'Тикеты',   'route' => 'admin.tickets.index', 'icon' => 'lifebuoy', 'badge' => 3],
            ['label' => 'Отчёты',   'route' => 'admin.reports.index', 'icon' => 'chart-bar'],
        ];
    @endphp

    @foreach($items as $item)
        @php
            $isActive = request()->routeIs(str_replace('.index', '.*', $item['route']))
                     || request()->routeIs($item['route']);
            $hasRoute = \Route::has($item['route']);
        @endphp

        <a href="{{ $hasRoute ? route($item['route']) : '#' }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
                  {{ $isActive
                     ? 'bg-primary-50 text-primary-700 border-l-2 border-primary-600'
                     : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">

            {{-- Icon (heroicon outline) --}}
            <svg class="w-5 h-5 {{ $isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {{-- Иконки заменить на реальные heroicons.com SVG paths --}}
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M3 12l2-2m0 0l7-7 7 7m-9 2v8a2 2 0 002 2h2a2 2 0 002-2v-4a2 2 0 012-2h2.5"/>
            </svg>

            <span class="flex-1">{{ $item['label'] }}</span>

            @if(!empty($item['badge']))
                <span class="bg-primary-100 text-primary-700 text-xs font-medium px-2 py-0.5 rounded-full">
                    {{ $item['badge'] }}
                </span>
            @endif
        </a>
    @endforeach

    {{-- Divider --}}
    <div class="border-t border-gray-200 my-4"></div>

    {{-- Settings --}}
    <a href="#" class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span>Настройки</span>
    </a>
</nav>

{{-- User card at bottom --}}
<div class="absolute bottom-0 left-0 right-0 border-t border-gray-200 p-4 bg-white">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-medium">
            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name ?? 'Гость' }}</p>
            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? '' }}</p>
        </div>
    </div>
</div>
