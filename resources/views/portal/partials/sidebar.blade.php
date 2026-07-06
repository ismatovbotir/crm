{{-- Logo --}}
<div class="h-16 flex items-center px-6 border-b border-gray-200">
    <a href="{{ url('/portal') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">R</span>
        </div>
        <div>
            <span class="font-semibold text-gray-900 block leading-tight">RSG</span>
            <span class="text-xs text-gray-400 leading-tight">Личный кабинет</span>
        </div>
    </a>
</div>

{{-- Navigation --}}
<nav class="px-3 py-4 space-y-1 overflow-y-auto" style="height: calc(100vh - 4rem - 5rem);">

    @php
        $items = [
            ['label' => 'Главная',        'url' => '/portal',           'icon' => 'home'],
            ['label' => 'Мои КП',         'url' => '/portal/quotes',    'icon' => 'document'],
            ['label' => 'Инвойсы',        'url' => '/portal/invoices',  'icon' => 'currency'],
            ['label' => 'Тикеты',         'url' => '/portal/tickets',   'icon' => 'lifebuoy'],
            ['label' => 'Мои устройства', 'url' => '/portal/equipment', 'icon' => 'device'],
            ['label' => 'Каталог',        'url' => '/portal/catalog',   'icon' => 'cube'],
            ['label' => 'Профиль',        'url' => '/portal/profile',   'icon' => 'building'],
        ];

        $icons = [
            'home'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7m-9 2v8a2 2 0 002 2h2a2 2 0 002-2v-4a2 2 0 012-2h2.5"/>',
            'document' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
            'currency' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.7 0-3 1.3-3 3s1.3 3 3 3 3 1.3 3 3-1.3 3-3 3m0-12V5m0 14v-2m0-12a9 9 0 110 18 9 9 0 010-18z"/>',
            'lifebuoy' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.4 5.6a9 9 0 11-12.7 0M12 13V3"/>',
            'cube'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
            'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
            'device'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>',
        ];
    @endphp

    @foreach($items as $item)
        @php
            $isActive = request()->is(ltrim($item['url'], '/'))
                     || request()->is(ltrim($item['url'], '/').'/*');
        @endphp

        <a href="{{ $item['url'] }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
                  {{ $isActive
                     ? 'bg-primary-50 text-primary-700 border-l-2 border-primary-600'
                     : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">

            <svg class="w-5 h-5 {{ $isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icons[$item['icon']] ?? '' !!}
            </svg>

            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach

</nav>

{{-- User card at bottom --}}
<div class="absolute bottom-0 left-0 right-0 border-t border-gray-200 p-4 bg-white">
    @php
        $userName    = auth()->user()->name ?? 'Клиент';
        $companyName = auth()->user()?->customers()?->first()?->name ?? auth()->user()?->email ?? '';
    @endphp
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 font-medium text-sm">
            {{ mb_substr($userName, 0, 1) }}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">{{ $userName }}</p>
            @if($companyName)
                <p class="text-xs text-gray-500 truncate">{{ $companyName }}</p>
            @endif
        </div>
    </div>
</div>
