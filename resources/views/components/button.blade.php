@props([
    'variant'  => 'primary',
    'size'     => 'md',
    'type'     => 'button',
    'disabled' => false,
])

@php
$base = 'inline-flex items-center font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed';

$sizes = [
    'sm' => 'px-3 py-1.5 text-xs gap-1.5',
    'md' => 'px-4 py-2 text-sm gap-2',
];

$variants = [
    'primary'   => 'bg-primary-600 text-white hover:bg-primary-700 focus:ring-primary-500',
    'secondary' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-400',
    'danger'    => 'bg-danger-600 text-white hover:bg-danger-700 focus:ring-danger-500',
    'ghost'     => 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:ring-gray-400',
];
@endphp

<button
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => "$base {$sizes[$size]} {$variants[$variant]}"]) }}
>
    {{ $slot }}
</button>
