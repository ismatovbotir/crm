<div>
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Возвраты</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление возвратами товаров</p>
        </div>
        <a href="{{ route('admin.returns.create') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Создать возврат
        </a>
    </div>

    {{-- Flash message --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <x-card class="mb-4" :padding="false">
        <div class="flex flex-wrap gap-3 p-4">
            <div class="flex-1 min-w-52">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Поиск по номеру, клиенту..."
                        class="w-full rounded-md border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
            </div>
            <select
                wire:model.live="statusFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
            >
                <option value="">Все статусы</option>
                <option value="draft">Черновик</option>
                <option value="approved">Подтверждён</option>
                <option value="refunded">Возврат выполнен</option>
                <option value="cancelled">Отменён</option>
            </select>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Номер</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Клиент</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Отгрузка</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Причина</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Сумма</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Статус</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Дата</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($returns as $return)
                    @php
                    $statusColor = match($return->status) {
                        'draft'      => 'gray',
                        'approved'   => 'blue',
                        'refunded'   => 'green',
                        'cancelled'  => 'red',
                        default      => 'gray',
                    };
                    $statusLabel = match($return->status) {
                        'draft'      => 'Черновик',
                        'approved'   => 'Подтверждён',
                        'refunded'   => 'Возврат выполнен',
                        'cancelled'  => 'Отменён',
                        default      => $return->status,
                    };
                    $reasonLabel = match($return->reason) {
                        'warranty'     => 'Гарантия',
                        'defect'       => 'Брак',
                        'changed_mind' => 'Передумал',
                        'other'        => 'Другое',
                        default        => $return->reason,
                    };
                    @endphp
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.returns.show', $return) }}"
                               class="font-mono text-sm font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                {{ $return->number }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $return->customer?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($return->sell)
                            <span class="font-mono text-xs text-gray-500">{{ $return->sell->number }}</span>
                            @else
                            <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $reasonLabel }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 whitespace-nowrap">
                            {{ number_format($return->refund_amount, 0, '.', ' ') }}
                            <span class="text-xs text-gray-400 font-normal ml-0.5">{{ $return->currency }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge :color="$statusColor">{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                            {{ $return->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.returns.show', $return) }}"
                               title="Открыть"
                               class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors inline-flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-14 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                            </svg>
                            <p class="text-sm text-gray-400">Возвраты не найдены</p>
                            @if($search || $statusFilter)
                            <p class="text-xs text-gray-400 mt-1">Попробуйте изменить фильтры</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($returns->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $returns->links() }}
        </div>
        @endif
    </x-card>
</div>
