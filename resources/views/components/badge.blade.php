@props(['color' => 'gray'])

@php
$colors = [
    'blue'   => 'bg-primary-100 text-primary-700',
    'green'  => 'bg-success-100 text-success-700',
    'yellow' => 'bg-warning-100 text-warning-700',
    'red'    => 'bg-danger-100 text-danger-700',
    'gray'   => 'bg-gray-100 text-gray-600',
    'purple' => 'bg-purple-100 text-purple-700',
];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {$colors[$color]}"]) }}>
    {{ $slot }}
</span>
