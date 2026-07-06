<div>
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Продажи / Отгрузки</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление продажами и отгрузками</p>
        </div>
        @can('create', \App\Models\Sell\Sell::class)
        <x-button wire:click="$set('showCreateForm', true)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Создать отгрузку
        </x-button>
        @endcan
    </div>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>
    @endif

    <x-card class="mb-4" :padding="false">
        <div class="flex gap-3 p-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" placeholder="Поиск по номеру, клиенту..."
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="filterStatus"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все статусы</option>
                <option value="draft">Черновик</option>
                <option value="confirmed">Подтверждён</option>
                <option value="shipped">Отгружен</option>
                <option value="delivered">Доставлен</option>
                <option value="cancelled">Отменён</option>
            </select>
        </div>
    </x-card>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Номер</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Клиент</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Инвойс</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Дата продажи</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Сумма</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Менеджер</th>
                    <th class="px-4 py-3 w-20"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sells as $sell)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.sells.show', $sell) }}"
                           class="font-mono font-medium text-gray-900 hover:text-primary-600">{{ $sell->number }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $sell->customer->name }}</td>
                    <td class="px-4 py-3">
                        @if($sell->invoice)
                        <a href="{{ route('admin.invoices.show', $sell->invoice) }}"
                           class="font-mono text-primary-600 hover:text-primary-700 hover:underline">{{ $sell->invoice->number }}</a>
                        @else
                        <span class="text-xs text-gray-400">Прямая продажа</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">{{ $sell->sold_at?->format('d.m.Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                        {{ number_format($sell->total, 0, '.', ' ') }}
                        <span class="text-xs text-gray-400">{{ $sell->currency }}</span>
                    </td>
                    <td class="px-4 py-3"><x-sell-status-badge :status="$sell->status" /></td>
                    <td class="px-4 py-3 text-gray-600">{{ $sell->manager?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-0.5 justify-end">
                            <a href="{{ route('admin.sells.show', $sell) }}"
                               class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            @can('delete', $sell)
                            <button wire:click="deleteSell({{ $sell->id }})"
                                    wire:confirm="Удалить отгрузку «{{ $sell->number }}»?"
                                    class="p-1.5 rounded text-gray-400 hover:text-danger-600 hover:bg-danger-50 inline-flex transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Отгрузки не найдены</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($sells->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $sells->links() }}</div>@endif
    </x-card>

    @if($showCreateForm)
    <x-slide-over title="Новая отгрузка" form-id="sell-create-form" save-label="Сохранить">
        @livewire('admin.sells.create-form', key('create-sell'))
    </x-slide-over>
    @endif
</div>
