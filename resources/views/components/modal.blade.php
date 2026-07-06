@props([
    'title'      => '',
    'width'      => 'max-w-xl',
    'openEvent'  => 'open-modal',
    'closeEvent' => 'close-modal',
    'saveEvent'  => '',
    'formId'     => '',
    'saveLabel'  => 'Сохранить',
    'cancelEvent' => '',
])

<div
    x-data="{ show: false }"
    x-on:{{ $openEvent }}.window="show = true"
    x-on:{{ $closeEvent }}.window="show = false"
    @if($saveEvent) x-on:{{ $saveEvent }}.window="show = false" @endif
    @keydown.escape.window="show = false"
>
    <div
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-900/50" @click="show = false"></div>

        {{-- Panel --}}
        <div class="flex min-h-full items-start justify-center p-4 pt-10">
            <div
                class="relative bg-white rounded-xl shadow-2xl w-full {{ $width }} z-10"
                @click.stop
            >
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
                    <h2 class="text-base font-semibold text-gray-900">{{ $title }}</h2>
                    <div class="flex items-center gap-2">
                        @if($formId)
                            @if($cancelEvent)
                            <button type="button" @click="$dispatch('{{ $cancelEvent }}'); show = false"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                Отмена
                            </button>
                            @else
                            <button type="button" @click="show = false"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none transition-colors">
                                Отмена
                            </button>
                            @endif
                            <button type="submit" form="{{ $formId }}"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-1 transition-colors">
                                {{ $saveLabel }}
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Scrollable body --}}
                <div class="px-6 py-5 overflow-y-auto max-h-[calc(100vh-14rem)]">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</div>
