<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Тикеты</h1>
            <p class="text-sm text-gray-500 mt-0.5">Техническая поддержка</p>
        </div>
        <x-button wire:click="$set('showCreateForm', true)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Новый тикет
        </x-button>
    </div>

    @if(session('success'))<div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">{{ session('success') }}</div>@endif

    <x-card class="mb-4" :padding="false">
        <div class="flex flex-wrap gap-3 p-4">
            <div class="flex-1 min-w-48">
                <input wire:model.live.debounce.300ms="search" placeholder="Поиск по номеру, теме, клиенту..." class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <select wire:model.live="statusFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все статусы</option>
                @foreach($statuses as $s)
                <option value="{{ $s }}">{{ match($s){'open'=>'Открыт','in_progress'=>'В работе','pending_customer'=>'Ждёт клиента','resolved'=>'Решён','closed'=>'Закрыт',default=>$s} }}</option>
                @endforeach
            </select>
            <select wire:model.live="priorityFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Все приоритеты</option>
                @foreach($priorities as $p)
                <option value="{{ $p }}">{{ match($p){'low'=>'Низкий','medium'=>'Средний','high'=>'Высокий','critical'=>'Критичный',default=>$p} }}</option>
                @endforeach
            </select>
        </div>
    </x-card>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50/50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Тема / Клиент</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Категория</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Приоритет</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Назначен</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Создан</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($tickets as $ticket)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-mono text-xs text-gray-400">{{ $ticket->number }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-900 truncate max-w-xs">{{ $ticket->subject }}</p>
                        <p class="text-xs text-gray-400">{{ $ticket->customer?->name ?? '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $ticket->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3"><x-ticket-priority-badge :priority="$ticket->priority" /></td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $ticket->assignee?->name ?? '—' }}</td>
                    <td class="px-4 py-3"><x-ticket-status-badge :status="$ticket->status" /></td>
                    <td class="px-4 py-3 text-xs text-gray-400">{{ $ticket->created_at->format('d.m.Y') }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="p-1.5 rounded text-gray-400 hover:text-primary-600 hover:bg-primary-50 inline-flex transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">Тикеты не найдены</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($tickets->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $tickets->links() }}</div>@endif
    </x-card>

    @if($showCreateForm)
    <x-slide-over title="Новый тикет" form-id="ticket-create-form" save-label="Создать тикет">
        @livewire('admin.tickets.create-form', key('ticket-create'))
    </x-slide-over>
    @endif
</div>
