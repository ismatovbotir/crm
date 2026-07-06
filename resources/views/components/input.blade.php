@props([
    'label'    => null,
    'error'    => null,
    'hint'     => null,
    'required' => false,
])

<div {{ $attributes->only('class') }}>
    @if($label)
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}@if($required)<span class="text-danger-500 ml-0.5">*</span>@endif
    </label>
    @endif

    <input
        {{ $attributes->except(['class', 'label', 'error', 'hint', 'required'])->merge([
            'class' => 'w-full rounded-md border px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:border-transparent transition-colors '
                . ($error
                    ? 'border-danger-400 focus:ring-danger-400'
                    : 'border-gray-300 focus:ring-primary-500')
        ]) }}
    >

    @if($error)
        <p class="mt-1 text-xs text-danger-600">{{ $error }}</p>
    @elseif($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
