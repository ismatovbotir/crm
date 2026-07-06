<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Обращения в поддержку</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ваши тикеты и их статус</p>
        </div>
        <x-button wire:click="openCreate">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Новое обращение
        </x-button>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter bar --}}
    <div class="mb-4">
        <select wire:model.live="statusFilter"
                class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
            <option value="">Все статусы</option>
            <option value="open">Открыт</option>
            <option value="in_progress">В работе</option>
            <option value="pending_customer">Ждёт ответа</option>
            <option value="resolved">Решён</option>
            <option value="closed">Закрыт</option>
        </select>
    </div>

    <x-card :padding="false">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-100 bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Номер</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Тема</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Категория</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden md:table-cell">Приоритет</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide">Статус</th>
                    <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wide hidden lg:table-cell">Дата</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($tickets as $ticket)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $ticket->number }}</td>
                        <td class="px-5 py-3 font-medium text-gray-900 max-w-xs">
                            <a href="/portal/tickets/{{ $ticket->id }}"
                               class="hover:text-primary-600 line-clamp-2">
                                {{ $ticket->subject }}
                            </a>
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden md:table-cell">
                            {{ $ticket->category?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-3 hidden md:table-cell">
                            <x-ticket-priority-badge :priority="$ticket->priority" />
                        </td>
                        <td class="px-5 py-3">
                            <x-ticket-status-badge :status="$ticket->status" />
                        </td>
                        <td class="px-5 py-3 text-gray-500 hidden lg:table-cell">
                            {{ $ticket->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="/portal/tickets/{{ $ticket->id }}"
                               class="text-primary-600 hover:text-primary-800 text-xs font-medium">
                                Открыть →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400 text-sm">
                            Обращений нет. <button wire:click="openCreate" class="text-primary-600 hover:underline">Создать первое</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($tickets->hasPages())
            <div class="px-5 py-3 border-t border-gray-100">
                {{ $tickets->links() }}
            </div>
        @endif
    </x-card>

    {{-- Create slide-over --}}
    @if($showCreateForm)
        <x-slide-over title="Новое обращение">
            @livewire('portal.tickets.create-form', key('portal-create-ticket'))
        </x-slide-over>
    @endif

</div>
