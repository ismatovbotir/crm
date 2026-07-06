<div>
    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-xl text-sm text-success-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Мои устройства</h1>
            <p class="text-sm text-gray-500 mt-0.5">Оборудование вашей компании для отслеживания и сервиса</p>
        </div>
        <button type="button" wire:click="$set('showAddForm', true)"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Добавить устройство
        </button>
    </div>

    {{-- Add Device Form --}}
    @if($showAddForm)
    <div class="mb-6 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Добавить устройство</h2>

        {{-- Step 1: Serial lookup --}}
        <div class="flex gap-2 mb-3">
            <input type="text"
                   wire:model="serialNumber"
                   placeholder="Введите серийный номер..."
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button type="button" wire:click="lookupSerial"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                Найти
            </button>
        </div>
        @error('serialNumber')<p class="text-xs text-danger-600 mb-3">{{ $message }}</p>@enderror

        {{-- Found: RSG device --}}
        @if($foundSerial)
        <div class="mb-3 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-xl">
            <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-success-800">{{ $foundSerial['display_name'] }}</p>
                <p class="text-xs text-success-600 mt-0.5">Устройство найдено в системе RSG</p>
            </div>
        </div>
        @endif

        {{-- Not found: external device fields --}}
        @if($showExtFields)
        <div class="mb-3 p-4 bg-blue-50 border border-blue-200 rounded-xl">
            <p class="text-sm font-medium text-blue-800 mb-3">
                Серийный номер не найден в базе RSG. Укажите данные устройства:
            </p>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Бренд</label>
                    <input type="text" wire:model="extBrand" placeholder="Epson, Zebra, Honeywell..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Модель</label>
                    <input type="text" wire:model="extModel" placeholder="TM-T88VI..."
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <p class="text-xs text-blue-600 mt-2">Устройство будет добавлено в вашу базу для отслеживания сервисных обращений</p>
        </div>
        @endif

        {{-- Form actions --}}
        @if($foundSerial || $showExtFields)
        <div class="flex items-center gap-2">
            <button type="button" wire:click="addDevice"
                    class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                Добавить
            </button>
            <button type="button" wire:click="$set('showAddForm', false)"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Отмена
            </button>
        </div>
        @else
        <button type="button" wire:click="$set('showAddForm', false)"
                class="text-sm text-gray-500 hover:text-gray-700">Отмена</button>
        @endif
    </div>
    @endif

    {{-- Device Grid --}}
    @if($devices->isEmpty())
    <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
        </svg>
        <p class="text-gray-500 font-medium">Устройств пока нет</p>
        <p class="text-sm text-gray-400 mt-1">Добавьте своё оборудование для отслеживания сервисных обращений</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($devices as $device)
        @php
        $stColor = match($device->current_status) {
            'available' => 'bg-success-100 text-success-700',
            'in_repair' => 'bg-orange-100 text-orange-700',
            'returned'  => 'bg-warning-100 text-warning-700',
            default     => 'bg-gray-100 text-gray-600',
        };
        $stLabel = match($device->current_status) {
            'available' => 'Доступен',
            'in_repair' => 'В ремонте',
            'returned'  => 'Возврат',
            'sold'      => 'Продан',
            default     => $device->current_status,
        };
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:border-primary-200 hover:shadow-sm transition-all">
            {{-- Device icon + status --}}
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/>
                    </svg>
                </div>
                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $stColor }}">{{ $stLabel }}</span>
            </div>

            {{-- Name --}}
            <h3 class="text-sm font-semibold text-gray-900 mb-0.5">{{ $device->display_name }}</h3>
            <p class="text-xs font-mono text-gray-500 mb-3">{{ $device->serial_number }}</p>

            @if($device->is_external)
            <span class="inline-block text-xs text-gray-400 bg-gray-50 border border-gray-200 px-2 py-0.5 rounded-full mb-3">
                Внешнее оборудование
            </span>
            @endif

            {{-- Actions --}}
            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                <button type="button" wire:click="openHistory({{ $device->id }})"
                        class="flex-1 text-center text-xs font-medium text-gray-600 hover:text-primary-600 py-1.5 rounded-lg hover:bg-primary-50 transition-colors">
                    История
                </button>
                <a href="{{ route('portal.tickets.create') }}?serial_number={{ urlencode($device->serial_number) }}"
                   class="flex-1 text-center text-xs font-medium text-primary-600 hover:text-primary-700 py-1.5 rounded-lg hover:bg-primary-50 transition-colors">
                    Создать тикет
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- History Slide-over --}}
    @if($showHistory)
    <div class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-gray-900/40" wire:click="closeHistory"></div>
        <div class="absolute inset-y-0 right-0 flex max-w-full pointer-events-none">
            <div class="pointer-events-auto w-screen max-w-md bg-white shadow-xl flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">{{ $historyData['display_name'] ?? 'Устройство' }}</h2>
                        <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $historyData['serial_number'] ?? '' }}</p>
                    </div>
                    <button type="button" wire:click="closeHistory"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                    {{-- Status history timeline --}}
                    @if(!empty($historyData['statuses']))
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">История статусов</h3>
                        <div class="relative">
                            <div class="absolute left-[7px] top-2 bottom-2 w-px bg-gray-200"></div>
                            <div class="space-y-4">
                                @foreach($historyData['statuses'] as $entry)
                                <div class="flex gap-4">
                                    <div class="w-3.5 h-3.5 rounded-full bg-white border-2 border-primary-400 flex-shrink-0 mt-0.5 z-10"></div>
                                    <div class="flex-1 pb-1">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ match($entry['status']) {
                                                'available' => 'Доступен / На складе',
                                                'sold'      => 'Продан',
                                                'in_repair' => 'Принят на сервис',
                                                'returned'  => 'Возврат',
                                                default     => $entry['status']
                                            } }}
                                        </p>
                                        @if($entry['notes'])
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $entry['notes'] }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400 mt-1">{{ $entry['created_at'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Linked tickets --}}
                    @if(!empty($historyData['tickets']))
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Обращения в поддержку</h3>
                        <div class="space-y-2">
                            @foreach($historyData['tickets'] as $ticket)
                            <a href="{{ route('portal.tickets.show', $ticket['id']) }}"
                               class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $ticket['subject'] }}</p>
                                    <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $ticket['number'] }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if(empty($historyData['statuses']) && empty($historyData['tickets']))
                    <p class="text-sm text-gray-400 text-center py-8">История пуста</p>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-gray-200">
                    <a href="{{ route('portal.tickets.create') }}?serial_number={{ urlencode($historyData['serial_number'] ?? '') }}"
                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-primary-600 rounded-xl hover:bg-primary-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Создать тикет для этого устройства
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
