@props([
    'label' => '',
    'value' => '',
    'icon'  => null,
    'trend' => null,
    'color' => 'blue',
])

@php
$colors = [
    'blue'   => ['bg' => 'bg-primary-50',  'icon' => 'text-primary-600'],
    'green'  => ['bg' => 'bg-success-50',  'icon' => 'text-success-600'],
    'yellow' => ['bg' => 'bg-warning-50',  'icon' => 'text-warning-600'],
    'purple' => ['bg' => 'bg-purple-50',   'icon' => 'text-purple-600'],
    'red'    => ['bg' => 'bg-danger-50',   'icon' => 'text-danger-600'],
];
$c = $colors[$color] ?? $colors['blue'];
@endphp

<div class="bg-white rounded-lg border border-gray-200 shadow-card p-5">
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide truncate">{{ $label }}</p>
            <p class="mt-1.5 text-2xl font-bold text-gray-900">{{ $value }}</p>
            @if($trend)
            <p class="mt-1 text-xs text-gray-500">{{ $trend }}</p>
            @endif
        </div>
        @if($icon)
        <div class="ml-4 flex-shrink-0 p-2.5 rounded-lg {{ $c['bg'] }}">
            <span class="text-xl leading-none {{ $c['icon'] }}">{{ $icon }}</span>
        </div>
        @endif
    </div>
</div>
