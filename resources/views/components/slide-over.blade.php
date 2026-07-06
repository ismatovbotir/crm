@props([
    'title'          => '',
    'wireCloseEvent' => null,
    'formId'         => '',
    'saveLabel'      => 'Сохранить',
    'size'           => 'lg',
])

@php
$closeAction = $wireCloseEvent
    ? "\$dispatch('{$wireCloseEvent}')"
    : 'closeForm';
@endphp

<div
    class="fixed inset-0 z-50 overflow-hidden"
    x-data="{ show: false }"
    x-init="$nextTick(() => { show = true })"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-gray-900/40"
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="{{ $closeAction }}"
    ></div>

    {{-- Panel --}}
    <div class="absolute inset-y-0 right-0 flex max-w-full pointer-events-none">
        <div
            class="pointer-events-auto w-screen {{ $size === '4xl' ? 'max-w-4xl' : ($size === '3xl' ? 'max-w-3xl' : ($size === '2xl' ? 'max-w-2xl' : ($size === 'xl' ? 'max-w-xl' : 'max-w-lg'))) }} bg-white shadow-xl flex flex-col"
            x-show="show"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
                <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                <div class="flex items-center gap-2">
                    @if($formId)
                    <button type="button" wire:click="{{ $closeAction }}"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                        Отмена
                    </button>
                    <button type="submit" form="{{ $formId }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 transition-colors">
                        {{ $saveLabel }}
                    </button>
                    @endif
                </div>
            </div>

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto px-6 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
