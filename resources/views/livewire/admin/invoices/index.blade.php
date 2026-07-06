<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Инвойсы</h1>
            <p class="text-sm text-gray-500 mt-0.5">Счета и платежи</p>
        </div>
        <div class="flex items-center gap-2">
            @can('invoices.export')
            <a href="{{ route('admin.export.invoices') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV
            </a>
            @endcan
            @can('create', \App\Models\Invoice\Invoice::class)
            <x-button wire:click="$set('showCreateForm', true)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Новый инвойс
            </x-button>
            @endcan
        </div>
    </div>

    @if(session('success'))<div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>@endif

    <x-card class="mb-4" :padding="false">
        <div class="flex gap-3 p-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" placeholder="Поиск по номеру, клиенту..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все статусы</option>
                @foreach($statuses as $s)
                <option value="{{ $s }}">{{ match($s){'draft'=>'Черновик','sent'=>'Отправлен','partially_paid'=>'Частично','paid'=>'Оплачен','overdue'=>'Просрочен','cancelled'=>'Отменён',default=>$s} }}</option>
                @endforeach
            </select>
        </div>
    </x-card>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Номер</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Клиент</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Сумма</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Оплачено</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Отгрузка</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Срок</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $invoice)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="font-mono font-medium text-gray-900 hover:text-primary-600">{{ $invoice->number }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $invoice->customer->name }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                        {{ number_format($invoice->total,0,'.',' ') }}
                        <span class="text-xs text-gray-400">{{ $invoice->currency }}</span>
                    </td>
                    <td class="px-4 py-3 text-right text-success-700 font-medium">{{ number_format($invoice->paid_amount,0,'.',' ') }}</td>
                    <td class="px-4 py-3"><x-invoice-status-badge :status="$invoice->status" /></td>
                    <td class="px-4 py-3"><x-shipment-status-badge :status="$invoice->shipment_status ?? 'none'" /></td>
                    <td class="px-4 py-3 text-xs @if($invoice->due_date?->isPast() && !in_array($invoice->status,['paid','cancelled'])) text-danger-600 font-medium @else text-gray-500 @endif">
                        {{ $invoice->due_date?->format('d.m.Y') ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Инвойсы не найдены</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($invoices->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>@endif
    </x-card>

    @if($showCreateForm)
    <x-slide-over title="Новый инвойс" form-id="document-create-form" save-label="Создать инвойс" size="4xl">
        @livewire('admin.documents.create-form', ['type' => 'invoice'], key('create-invoice'))
    </x-slide-over>
    @endif
</div>
