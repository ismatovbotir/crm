@props([
    'selectedId'   => null,
    'selectedName' => '',
    'results'      => [],
    'query'        => '',
    'error'        => null,
])

<div
    x-data="{ open: false }"
    x-on:click.outside="open = false"
    class="relative"
>
    <label class="block text-sm font-medium text-gray-700 mb-1">
        Клиент <span class="text-danger-500">*</span>
    </label>

    @if($selectedId)
    {{-- Selected state --}}
    <div class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm">
        <svg class="w-4 h-4 text-success-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
        </svg>
        <span class="flex-1 font-medium text-gray-900 truncate">{{ $selectedName }}</span>
        <button type="button" wire:click="clearCustomer"
                class="flex-shrink-0 text-gray-400 hover:text-danger-500 transition-colors"
                title="Изменить клиента">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
    @else
    {{-- Search input --}}
    <div class="relative">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
            </svg>
        </div>
        <input
            wire:model.live.debounce.300ms="customerQuery"
            x-on:focus="open = true"
            x-on:input="open = true"
            x-on:keydown.escape="open = false"
            type="text"
            autocomplete="off"
            placeholder="Введите название или ИНН..."
            class="w-full rounded-lg border {{ $error ? 'border-danger-500' : 'border-gray-300' }} pl-9 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
    </div>

    {{-- Dropdown --}}
    @if(count($results) > 0)
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="absolute left-0 right-0 top-full mt-1 z-40 bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden"
        x-cloak
    >
        @foreach($results as $c)
        <button
            type="button"
            wire:click="selectCustomer({{ $c['id'] }}, @js($c['name']))"
            x-on:click="open = false"
            class="flex items-center justify-between w-full px-4 py-2.5 text-sm text-left hover:bg-primary-50 transition-colors border-b border-gray-50 last:border-0"
        >
            <span class="font-medium text-gray-900">{{ $c['name'] }}</span>
            @if($c['inn'])
            <span class="text-xs text-gray-400 font-mono ml-3">ИНН {{ $c['inn'] }}</span>
            @endif
        </button>
        @endforeach
    </div>
    @elseif(strlen($query ?? '') >= 2)
    <div
        x-show="open"
        class="absolute left-0 right-0 top-full mt-1 z-40 bg-white rounded-xl shadow-xl border border-gray-200 px-4 py-3 text-sm text-gray-400"
        x-cloak
    >
        Клиенты не найдены
    </div>
    @endif

    @if($error)
    <p class="mt-1 text-xs text-danger-600">{{ $error }}</p>
    @endif
    @endif
</div>
