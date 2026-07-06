<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Коммерческие предложения</h1>
            <p class="text-sm text-gray-500 mt-0.5">КП для клиентов RSG</p>
        </div>
        @can('create', \App\Models\Quote\Quote::class)
        <x-button wire:click="$set('showCreateForm', true)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Новое КП
        </x-button>
        @endcan
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
                <option value="{{ $s }}">{{ match($s){'draft'=>'Черновик','sent'=>'Отправлен','viewed'=>'Просмотрен','accepted'=>'Принят','rejected'=>'Отклонён','expired'=>'Истёк',default=>$s} }}</option>
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
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Менеджер</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Сумма</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">До</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($quotes as $quote)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.quotes.show', $quote) }}" class="font-mono font-medium text-gray-900 hover:text-primary-600">{{ $quote->number }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $quote->customer->name }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $quote->manager?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                        {{ number_format($quote->total,0,'.',' ') }}
                        <span class="text-xs text-gray-400">{{ $quote->currency }}</span>
                    </td>
                    <td class="px-4 py-3"><x-quote-status-badge :status="$quote->status" /></td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $quote->valid_until?->format('d.m.Y') ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.quotes.show', $quote) }}" class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">КП не найдены</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($quotes->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $quotes->links() }}</div>@endif
    </x-card>

    @if($showCreateForm)
    <x-slide-over title="Новое КП" form-id="document-create-form" save-label="Создать КП" size="4xl">
        @livewire('admin.documents.create-form', ['type' => 'quote'], key('create-quote'))
    </x-slide-over>
    @endif

</div>
