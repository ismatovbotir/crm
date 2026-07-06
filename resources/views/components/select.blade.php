@props([
    'label'    => null,
    'error'    => null,
    'required' => false,
])

<div {{ $attributes->only('class') }}>
    @if($label)
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}@if($required)<span class="text-danger-500 ml-0.5">*</span>@endif
    </label>
    @endif

    <select
        {{ $attributes->except(['class', 'label', 'error', 'required'])->merge([
            'class' => 'w-full rounded-md border px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:border-transparent bg-white transition-colors '
                . ($error
                    ? 'border-danger-400 focus:ring-danger-400'
                    : 'border-gray-300 focus:ring-primary-500')
        ]) }}
    >
        {{ $slot }}
    </select>

    @if($error)
        <p class="mt-1 text-xs text-danger-600">{{ $error }}</p>
    @endif
</div>
