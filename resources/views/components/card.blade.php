@props([
    'title'   => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-lg border border-gray-200 shadow-card']) }}>
    @if($title)
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
    </div>
    @endif

    <div @class(['px-5 py-4' => $padding])>
        {{ $slot }}
    </div>
</div>
