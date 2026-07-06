<div class="max-w-6xl mx-auto">
    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.tickets.index') }}" class="hover:text-primary-600">Тикеты</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">{{ $ticket->number }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
            <div class="flex items-center gap-2 mt-1">
                <x-ticket-priority-badge :priority="$ticket->priority" />
                <x-ticket-status-badge :status="$ticket->status" />
                @if($ticket->category)<span class="text-xs text-gray-400">{{ $ticket->category->name }}</span>@endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.returns.create', ['ticket_id' => $ticket->id]) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
                Оформить возврат
            </a>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-4">
            {{-- Original description --}}
            @if($ticket->description)
            <x-card>
                <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $ticket->description }}</p>
                <p class="text-xs text-gray-400 mt-3">{{ $ticket->creator->name }} · {{ $ticket->created_at->format('d.m.Y H:i') }}</p>
            </x-card>
            @endif

            {{-- Comments --}}
            <div class="space-y-3">
                @forelse($ticket->comments as $comment)
                <div @class(['rounded-xl border p-4 shadow-sm',
                    'border-gray-200 bg-white' => !$comment->is_internal,
                    'border-warning-200 bg-warning-50' => $comment->is_internal])>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ substr($comment->user->name,0,1) }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</span>
                            @if($comment->is_internal)<x-badge color="yellow">Внутр.</x-badge>@endif
                        </div>
                        <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-line leading-relaxed">{{ $comment->body }}</p>
                </div>
                @empty
                <div class="text-center py-6 text-sm text-gray-400">Комментариев пока нет</div>
                @endforelse
            </div>

            {{-- Add comment --}}
            <x-card title="Ответить">
                <form wire:submit="addComment" class="space-y-3">
                    <textarea wire:model="commentBody" rows="4"
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Введите ответ..."></textarea>
                    @error('commentBody')<p class="text-xs text-danger-600">{{ $message }}</p>@enderror
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                            <input type="checkbox" wire:model="isInternal" class="rounded border-gray-300 text-warning-500 focus:ring-warning-500">
                            Внутренняя заметка
                        </label>
                        <x-button type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove>Отправить</span><span wire:loading>Отправка...</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
        </div>

        <div class="space-y-4">
            <x-card title="Изменить статус">
                <div class="space-y-1.5">
                    @foreach(['open'=>'Открыт','in_progress'=>'В работе','pending_customer'=>'Ждёт клиента','resolved'=>'Решён','closed'=>'Закрыт'] as $s => $label)
                    <button wire:click="changeStatus('{{ $s }}')"
                            @class(['w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                                'bg-primary-50 text-primary-700 font-medium' => $ticket->status===$s,
                                'text-gray-600 hover:bg-gray-50' => $ticket->status!==$s])>{{ $label }}</button>
                    @endforeach
                </div>
            </x-card>

            <x-card title="Назначить">
                <x-select wire:model.live="assigneeId">
                    <option value="">— не назначен —</option>
                    @foreach($supportStaff as $staff)
                    <option value="{{ $staff->id }}" @selected($ticket->assignee_id == $staff->id)>{{ $staff->name }}</option>
                    @endforeach
                </x-select>
            </x-card>

            <x-card title="Детали">
                <dl class="space-y-2.5 text-sm">
                    @if($ticket->customer)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Клиент</dt>
                        <dd class="text-gray-900 mt-0.5">{{ $ticket->customer->name }}</dd>
                    </div>
                    @endif
                    @if($ticket->category)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Категория</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $ticket->category->name }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">SLA</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $ticket->category?->sla_hours ?? '—' }} ч</dd>
                    </div>
                    @if($ticket->assignee)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Назначен</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $ticket->assignee->name }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Создан</dt>
                        <dd class="text-gray-700 mt-0.5">{{ $ticket->created_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @if($ticket->resolved_at)
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium">Решён</dt>
                        <dd class="text-success-700 mt-0.5 font-medium">{{ $ticket->resolved_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            @if($ticket->serial)
            <x-card title="Устройство">
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">Серийный номер</p>
                        <p class="font-mono font-medium text-gray-900">{{ $ticket->serial->serial_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-0.5">Устройство</p>
                        <p class="text-gray-700">{{ $ticket->serial->display_name }}</p>
                        @if($ticket->serial->is_external)
                        <x-badge color="yellow" class="mt-1">Внешнее оборудование</x-badge>
                        @endif
                    </div>
                    @php
                    $st = $ticket->serial->current_status;
                    $stColor = match($st) { 'available'=>'green','sold'=>'blue','returned'=>'yellow','in_repair'=>'orange',default=>'gray' };
                    $stLabel = match($st) { 'available'=>'Доступен','sold'=>'Продан','returned'=>'Возврат','in_repair'=>'В ремонте',default=>$st };
                    @endphp
                    <div>
                        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-1">Статус</p>
                        <x-badge :color="$stColor">{{ $stLabel }}</x-badge>
                    </div>

                    {{-- Mini status history --}}
                    @if($ticket->serial->statusHistory->isNotEmpty())
                    <div class="pt-2 border-t border-gray-100">
                        <p class="text-xs text-gray-400 uppercase font-semibold tracking-wide mb-2">История</p>
                        <div class="space-y-1.5">
                            @foreach($ticket->serial->statusHistory->take(5) as $entry)
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-300 flex-shrink-0"></div>
                                <span class="text-xs text-gray-600">{{ match($entry->status) {
                                    'available'=>'Доступен','sold'=>'Продан',
                                    'returned'=>'Возврат','in_repair'=>'В ремонте',default=>$entry->status
                                } }}</span>
                                <span class="text-xs text-gray-400 ml-auto">{{ $entry->created_at?->format('d.m.Y') }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>
