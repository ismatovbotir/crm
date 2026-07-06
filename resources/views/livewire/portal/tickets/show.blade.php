<div class="max-w-3xl mx-auto">

    {{-- Page header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="/portal/tickets"
           class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <p class="text-xs text-gray-400 mb-0.5">
                <a href="/portal/tickets" class="hover:text-primary-600">Тикеты</a>
                <span class="mx-1">/</span>
                {{ $ticket->number }}
            </p>
            <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ $ticket->subject }}</h1>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <x-ticket-priority-badge :priority="$ticket->priority" />
            <x-ticket-status-badge :status="$ticket->status" />
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Ticket meta --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Номер</p>
            <p class="font-mono text-sm font-medium text-gray-900">{{ $ticket->number }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Категория</p>
            <p class="font-medium text-gray-900 text-sm">{{ $ticket->category?->name ?? '—' }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Исполнитель</p>
            <p class="font-medium text-gray-900 text-sm">{{ $ticket->assignee?->name ?? 'Не назначен' }}</p>
        </x-card>
        <x-card>
            <p class="text-xs text-gray-500 mb-1">Создан</p>
            <p class="font-medium text-gray-900 text-sm">{{ $ticket->created_at->format('d.m.Y') }}</p>
        </x-card>
    </div>

    {{-- Description --}}
    <x-card class="mb-6">
        <div class="mb-3">
            <h3 class="text-sm font-semibold text-gray-900">Описание</h3>
        </div>
        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
    </x-card>

    {{-- Comments --}}
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">
            Переписка
            @if($comments->isNotEmpty())
                <span class="text-gray-400 font-normal">({{ $comments->count() }})</span>
            @endif
        </h3>

        @if($comments->isEmpty())
            <div class="bg-white rounded-lg border border-gray-200 shadow-card px-5 py-8 text-center">
                <p class="text-sm text-gray-400">Пока нет сообщений. Напишите первый комментарий.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($comments as $comment)
                    @php
                        $isOwn = $comment->user_id === auth()->id();
                    @endphp
                    <div class="bg-white rounded-lg border border-gray-200 shadow-card px-5 py-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-xs font-medium">
                                    {{ mb_substr($comment->user?->name ?? '?', 0, 1) }}
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $comment->user?->name ?? 'Неизвестно' }}
                                </span>
                                @if($isOwn)
                                    <x-badge color="blue">Вы</x-badge>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400">
                                {{ $comment->created_at->format('d.m.Y H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->body }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Add comment --}}
    @if(!in_array($ticket->status, ['resolved', 'closed']))
        <x-card>
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Добавить комментарий</h3>
            <form wire:submit="addComment">
                <textarea
                    wire:model="commentBody"
                    rows="4"
                    placeholder="Напишите ваш ответ или уточнение..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           transition-colors resize-none mb-3"
                ></textarea>
                @error('commentBody')
                    <p class="mb-2 text-xs text-danger-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-end">
                    <x-button type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="addComment">Отправить</span>
                        <span wire:loading wire:target="addComment">Отправка...</span>
                    </x-button>
                </div>
            </form>
        </x-card>
    @else
        <div class="bg-gray-50 rounded-lg border border-gray-200 px-5 py-4 text-center">
            <p class="text-sm text-gray-500">
                Тикет закрыт. Если у вас остались вопросы, создайте новое обращение.
            </p>
        </div>
    @endif

</div>
