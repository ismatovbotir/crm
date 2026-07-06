<div>
    @php
        $statusColors = [
            'submitted'    => 'bg-primary-100 text-primary-700',
            'under_review' => 'bg-warning-100 text-warning-700',
            'quoted'       => 'bg-blue-100 text-blue-700',
            'closed'       => 'bg-gray-100 text-gray-500',
        ];
        $statusLabels = [
            'submitted'    => 'Новая',
            'under_review' => 'На рассмотрении',
            'quoted'       => 'КП отправлено',
            'closed'       => 'Закрыта',
        ];
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $equipmentRequest->subject }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">Заявка от {{ $equipmentRequest->created_at->format('d.m.Y') }}</p>
        </div>
        <a href="{{ route('portal.equipment-requests.index') }}" class="text-sm text-primary-600 hover:underline">
            ← К списку заявок
        </a>
    </div>

    <x-card>
        <div class="flex items-center gap-2 mb-4">
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$equipmentRequest->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $statusLabels[$equipmentRequest->status] ?? $equipmentRequest->status }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Бюджет</dt>
                <dd class="text-gray-900 font-medium">
                    {{ $equipmentRequest->budget ? number_format($equipmentRequest->budget, 0, '.', ' ') : '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Нужно к дате</dt>
                <dd class="text-gray-900 font-medium">
                    {{ $equipmentRequest->needed_by?->format('d.m.Y') ?? '—' }}
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Ответственный менеджер</dt>
                <dd class="text-gray-900 font-medium">{{ $equipmentRequest->manager?->name ?? 'Пока не назначен' }}</dd>
            </div>
        </dl>

        @if($equipmentRequest->description)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <dt class="text-gray-500 text-sm mb-1">Описание</dt>
                <dd class="text-gray-800 text-sm whitespace-pre-line">{{ $equipmentRequest->description }}</dd>
            </div>
        @endif

        @if($equipmentRequest->quote)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <dt class="text-gray-500 text-sm mb-1">Коммерческое предложение</dt>
                <dd class="text-sm">
                    <a href="{{ route('portal.quotes.show', $equipmentRequest->quote) }}"
                       class="inline-flex items-center gap-1.5 font-medium text-primary-600 hover:text-primary-700">
                        {{ $equipmentRequest->quote->number }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                </dd>
            </div>
        @endif
    </x-card>

    {{-- Comments --}}
    <div class="mt-6">
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
    @if($equipmentRequest->status !== 'closed')
        <x-card class="mt-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Добавить комментарий</h3>
            <form wire:submit="addComment">
                <textarea
                    wire:model="commentBody"
                    rows="4"
                    placeholder="Напишите ваш вопрос или уточнение..."
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
    @endif
</div>
