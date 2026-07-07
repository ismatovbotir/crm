{{-- Logo --}}
<div class="h-16 flex items-center px-5 border-b border-gray-200" :class="sidebarCollapsed ? 'lg:px-0 lg:justify-center' : ''">
    <a href="{{ url('/admin') }}" class="flex items-center gap-2.5" title="RSG-CRM">
        <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
            <span class="text-white font-bold text-sm">R</span>
        </div>
        <div :class="sidebarCollapsed ? 'lg:hidden' : ''">
            <span class="font-semibold text-gray-900 text-sm leading-tight block">RSG-CRM</span>
            <span class="text-xs text-gray-400 leading-tight block">Торговое оборудование</span>
        </div>
    </a>
</div>

@php
    $icons = [
        'home'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'user-plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',
        'building'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'document'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'currency'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
        'truck'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1.5 9.5A2 2 0 008.5 19h7a2 2 0 001.985-1.5L19 8M10 12h4"/>',
        'cube'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'tag'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>',
        'ticket'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
        'inbox'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>',
        'chart'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'gear'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'tasks'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>',
        'return'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>',
        'layers'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>',
        'star'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
        'users'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 00-9.288 0M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'shield'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
    ];

    $currentUrl = request()->path(); // e.g. "admin" or "admin/leads/5"

    $navLink = function (string $url, string $label, string $icon, ?string $permission = null) use ($icons, $currentUrl) {
        if ($permission && ! \App\Helpers\Acl::can($permission)) return '';
        $path      = ltrim($url, '/');
        // exact match for dashboard, prefix match for everything else
        $isActive  = ($path === 'admin')
            ? $currentUrl === 'admin'
            : str_starts_with($currentUrl, $path);
        $base = 'group flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors w-full text-left';
        $cls  = $isActive
            ? $base . ' bg-primary-50 text-primary-700'
            : $base . ' text-gray-600 hover:bg-gray-100 hover:text-gray-900';
        $iconCls = $isActive ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-600';

        return '<a href="' . url($url) . '" class="' . $cls . '" title="' . e($label) . '" :class="sidebarCollapsed ? \'lg:justify-center lg:px-2\' : \'\'">'
            . '<svg class="w-5 h-5 flex-shrink-0 ' . $iconCls . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
            . ($icons[$icon] ?? '')
            . '</svg>'
            . '<span :class="sidebarCollapsed ? \'lg:hidden\' : \'\'">' . e($label) . '</span>'
            . '</a>';
    };

    $sectionLabel = function (string $label) {
        return '<p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400" :class="sidebarCollapsed ? \'lg:hidden\' : \'\'">' . e($label) . '</p>'
            . '<div class="hidden mx-3 mt-4 mb-1 border-t border-gray-100" :class="sidebarCollapsed ? \'lg:block\' : \'\'"></div>';
    };
@endphp

{{-- Navigation --}}
<nav class="px-3 py-3 overflow-y-auto flex flex-col gap-0.5" :class="sidebarCollapsed ? 'lg:px-2' : ''" style="height: calc(100vh - 4rem - 4.5rem);">

    {!! $navLink('/admin', 'Дашборд', 'home') !!}

    {{-- Management section --}}
    {!! $sectionLabel('Управление') !!}
    {!! $navLink('/admin/tasks', 'Задачи', 'tasks') !!}

    {{-- Sales section --}}
    {!! $sectionLabel('Продажи') !!}
    {!! $navLink('/admin/leads',     'Лиды',    'user-plus', 'leads.view') !!}
    {!! $navLink('/admin/customers', 'Клиенты', 'building',  'customers.view') !!}
    {!! $navLink('/admin/quotes',    'КП',      'document',  'quotes.view') !!}
    {!! $navLink('/admin/invoices',  'Инвойсы', 'currency',  'invoices.view') !!}
    {!! $navLink('/admin/sells',     'Продажи', 'truck',     'sells.view') !!}
    {!! $navLink('/admin/returns',   'Возвраты', 'return',   'returns.view') !!}

    {{-- Catalog section --}}
    @if(\App\Helpers\Acl::can('catalog.products.view'))
    {!! $sectionLabel('Каталог') !!}
    {!! $navLink('/admin/catalog/products',        'Товары',        'cube',   'catalog.products.view') !!}
    {!! $navLink('/admin/catalog/categories',      'Категории',     'tag',    'catalog.products.view') !!}
    {!! $navLink('/admin/catalog/groups',          'Группы',        'layers', 'catalog.products.view') !!}
    {!! $navLink('/admin/catalog/recommendations', 'Рекомендации',  'star',   'catalog.products.view') !!}
    @endif

    {{-- Support section --}}
    @if(\App\Helpers\Acl::can('tickets.view') || \App\Helpers\Acl::can('equipment-requests.view'))
    {!! $sectionLabel('Поддержка') !!}
    {!! $navLink('/admin/tickets',           'Тикеты',  'ticket', 'tickets.view') !!}
    {!! $navLink('/admin/equipment-requests','Заявки',  'inbox',  'equipment-requests.view') !!}
    @endif

    {{-- Analytics --}}
    @if(\App\Helpers\Acl::can('reports.sales'))
    {!! $sectionLabel('Аналитика') !!}
    {!! $navLink('/admin/reports', 'Отчёты', 'chart', 'reports.sales') !!}
    @endif

    {{-- Settings --}}
    @if(\App\Helpers\Acl::can('settings.users'))
    <div class="border-t border-gray-100 mt-3 pt-3">
        {!! $navLink('/admin/settings/users', 'Настройки', 'gear', 'settings.users') !!}
        {!! $navLink('/admin/settings/users', 'Пользователи', 'users', 'settings.users') !!}
        @if(auth()->user()->hasRole('super-admin'))
            {!! $navLink('/admin/settings/roles', 'Роли и права', 'shield') !!}
        @endif
    </div>
    @endif

</nav>

{{-- User card --}}
<div class="absolute bottom-0 left-0 right-0 border-t border-gray-200 px-4 py-3 bg-white" :class="sidebarCollapsed ? 'lg:px-2' : ''">
    @php
        $user      = auth()->user();
        $userName  = $user->name ?? 'Гость';
        $userRole  = $user?->getRoleNames()?->first();
        $roleLabel = match($userRole) {
            'super-admin'     => 'Супер-администратор',
            'sales-director'  => 'Директор продаж',
            'sales-manager'   => 'Менеджер',
            'tech-support'    => 'Тех. поддержка',
            'catalog-manager' => 'Каталог',
            'accountant'      => 'Бухгалтер',
            default           => $userRole ?? $user?->email ?? '',
        };
    @endphp
    <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'lg:justify-center' : ''">
        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-sm font-semibold flex-shrink-0"
             title="{{ $userName }} ({{ $roleLabel }})">
            {{ mb_strtoupper(mb_substr($userName, 0, 1)) }}
        </div>
        <div class="flex-1 min-w-0" :class="sidebarCollapsed ? 'lg:hidden' : ''">
            <p class="text-sm font-medium text-gray-900 truncate leading-tight">{{ $userName }}</p>
            <p class="text-xs text-gray-500 truncate leading-tight">{{ $roleLabel }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Выйти"
                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </button>
        </form>
    </div>
</div>
