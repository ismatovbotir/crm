<div>
    {{-- Flash messages --}}
    @if(session('serial_success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('serial_success') }}
    </div>
    @endif

    @if(session('serial_error'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ session('serial_error') }}
    </div>
    @endif

    @if($errors->has('importFile'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-danger-50 border border-danger-200 rounded-lg text-sm text-danger-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $errors->first('importFile') }}
    </div>
    @endif

    {{-- Toolbar --}}
    <div class="flex items-center gap-3 mb-4 flex-wrap">
        <div class="flex-1 min-w-48">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Поиск по серийному номеру..."
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
        </div>
        <select wire:model.live="statusFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            <option value="">Все статусы</option>
            <option value="available">Доступен</option>
            <option value="sold">Продан</option>
            <option value="returned">Возврат</option>
            <option value="in_repair">В ремонте</option>
        </select>
        <x-button wire:click="$set('showAddForm', true)" variant="secondary">+ Добавить</x-button>
    </div>

    {{-- Add form --}}
    @if($showAddForm)
    <x-card class="mb-4">
        <form wire:submit="addSerial" class="flex items-end gap-3 flex-wrap">
            <div class="flex-1 min-w-48">
                <x-input label="Серийный номер"
                         wire:model="newSerial"
                         :error="$errors->first('newSerial')"
                         required
                         placeholder="SN123456" />
            </div>
            <div class="flex-1 min-w-48">
                <x-input label="Примечание (опционально)" wire:model="newNotes" />
            </div>
            <div class="flex items-center gap-2 pb-0.5">
                <x-button type="submit">Сохранить</x-button>
                <x-button type="button" variant="secondary" wire:click="$set('showAddForm', false)">Отмена</x-button>
            </div>
        </form>
    </x-card>
    @endif

    {{-- Table --}}
    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/60">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Серийный номер</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Примечание</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Клиент / Продажа</th>
                    <th class="px-4 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($serials as $serial)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-4 py-2.5 font-mono text-gray-900">{{ $serial->serial_number }}</td>
                    <td class="px-4 py-2.5">
                        @php
                        $statusColor = match($serial->current_status) {
                            'available' => 'green',
                            'sold'      => 'blue',
                            'returned'  => 'yellow',
                            'in_repair' => 'gray',
                            default     => 'gray',
                        };
                        $statusLabel = match($serial->current_status) {
                            'available' => 'Доступен',
                            'sold'      => 'Продан',
                            'returned'  => 'Возврат',
                            'in_repair' => 'В ремонте',
                            default     => $serial->current_status,
                        };
                        @endphp
                        <x-badge :color="$statusColor">{{ $statusLabel }}</x-badge>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $serial->notes ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-500">
                        @if($serial->current_status === 'sold')
                            @if($serial->customer)
                            <div class="font-medium text-gray-700">{{ $serial->customer->name }}</div>
                            @endif
                            @if($serial->sellItem?->sell)
                            <span class="font-mono text-gray-500">{{ $serial->sellItem->sell->number }}</span>
                            @endif
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center gap-1">
                            <button type="button"
                                    wire:click="openHistory({{ $serial->id }})"
                                    title="История"
                                    class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0"/>
                                </svg>
                            </button>
                            @if($serial->current_status === 'available')
                            <button type="button"
                                    wire:click="deleteSerial({{ $serial->id }})"
                                    wire:confirm="Удалить серийный номер {{ $serial->serial_number }}?"
                                    class="p-1.5 text-gray-400 hover:text-danger-600 hover:bg-danger-50 rounded transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center">
                        <p class="text-sm text-gray-400">Серийные номера не добавлены</p>
                        <p class="text-xs text-gray-400 mt-1">Добавьте вручную или импортируйте CSV</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($serials->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $serials->links() }}
        </div>
        @endif
    </x-card>

    {{-- CSV Import --}}
    <div x-data="{ showImport: false }" class="mt-4">
        <button type="button"
                @click="showImport = !showImport"
                class="text-sm text-primary-600 hover:underline">
            Импорт из CSV
        </button>
        <div x-show="showImport" x-transition class="mt-3">
            <x-card>
                <form wire:submit="importCsv" class="flex items-center gap-3 flex-wrap">
                    <input type="file"
                           wire:model="importFile"
                           accept=".csv"
                           class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                    <x-button type="submit">Импортировать</x-button>
                    <p class="text-xs text-gray-400">CSV: одна колонка <code class="font-mono bg-gray-100 px-1 rounded">serial_number</code>, без заголовка или с заголовком — пропускается автоматически</p>
                </form>
            </x-card>
        </div>
    </div>

    {{-- Serial History Slide-over --}}
    @if($showHistory)
    <div class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-gray-900/40" wire:click="closeHistory"></div>
        <div class="absolute inset-y-0 right-0 flex max-w-full pointer-events-none">
            <div class="pointer-events-auto w-screen max-w-md bg-white shadow-xl flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900">История устройства</h2>
                        <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $historyData['serial_number'] ?? '' }}</p>
                    </div>
                    <button type="button" wire:click="closeHistory"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
                    {{-- Device info --}}
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $historyData['display_name'] ?? '' }}</p>
                            @if($historyData['owner_name'] ?? null)
                            <p class="text-xs text-gray-500 mt-0.5">Владелец: {{ $historyData['owner_name'] }}</p>
                            @endif
                        </div>
                        @php
                        $st = $historyData['current_status'] ?? 'available';
                        $stColor = match($st) {
                            'available' => 'green', 'sold' => 'blue',
                            'returned'  => 'yellow', 'in_repair' => 'orange',
                            default     => 'gray'
                        };
                        $stLabel = match($st) {
                            'available' => 'Доступен', 'sold' => 'Продан',
                            'returned'  => 'Возврат', 'in_repair' => 'В ремонте',
                            default     => $st
                        };
                        @endphp
                        <x-badge :color="$stColor">{{ $stLabel }}</x-badge>
                    </div>

                    {{-- Status history --}}
                    @if(!empty($historyData['statuses']))
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">История статусов</h3>
                        <div class="relative">
                            <div class="absolute left-2 top-0 bottom-0 w-px bg-gray-200"></div>
                            <div class="space-y-3">
                                @foreach($historyData['statuses'] as $entry)
                                <div class="flex gap-3 relative">
                                    <div class="w-4 h-4 rounded-full border-2 border-gray-300 bg-white flex-shrink-0 mt-0.5 z-10"></div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            @php
                                            $ec = match($entry['status']) {
                                                'available' => 'green', 'sold' => 'blue',
                                                'returned'  => 'yellow', 'in_repair' => 'orange',
                                                default     => 'gray'
                                            };
                                            $el = match($entry['status']) {
                                                'available' => 'Доступен', 'sold' => 'Продан',
                                                'returned'  => 'Возврат', 'in_repair' => 'В ремонте',
                                                default     => $entry['status']
                                            };
                                            @endphp
                                            <x-badge :color="$ec" class="text-xs">{{ $el }}</x-badge>
                                            <span class="text-xs text-gray-400">{{ $entry['created_at'] }}</span>
                                        </div>
                                        @if($entry['notes'])
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $entry['notes'] }}</p>
                                        @endif
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
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Тикеты</h3>
                        <div class="space-y-2">
                            @foreach($historyData['tickets'] as $ticket)
                            <a href="{{ route('admin.tickets.show', $ticket['id']) }}"
                               class="flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $ticket['subject'] }}</p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $ticket['number'] }}</p>
                                </div>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
