<div class="max-w-5xl mx-auto">
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.equipment-requests.index') }}" class="hover:text-primary-600">Заявки</a>
                <span class="mx-1">/</span>
                <span class="text-gray-900">#{{ $request->id }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $request->subject }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $request->customer?->name ?? '—' }}</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $statusColors = ['submitted'=>'bg-primary-100 text-primary-700','under_review'=>'bg-warning-100 text-warning-700','quoted'=>'bg-blue-100 text-blue-700','closed'=>'bg-gray-100 text-gray-500'];
                $statusLabels = ['submitted'=>'Новая','under_review'=>'На рассмотрении','quoted'=>'КП отправлено','closed'=>'Закрыта'];
            @endphp
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-medium {{ $statusColors[$request->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $statusLabels[$request->status] ?? $request->status }}
            </span>
            <x-button wire:click="convertToQuote" variant="secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Создать КП
            </x-button>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        {{-- Main content --}}
        <div class="col-span-2 space-y-4">

            {{-- Request details --}}
            <x-card title="Детали заявки">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase font-medium mb-1">Описание</dt>
                        <dd class="text-gray-700">{{ $request->description ?: '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-50">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase font-medium mb-1">Бюджет</dt>
                            <dd class="font-medium text-gray-900">
                                {{ $request->budget ? number_format($request->budget, 0, '.', ' ') . ' UZS' : '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase font-medium mb-1">Срок поставки</dt>
                            <dd class="font-medium {{ $request->needed_by?->isPast() ? 'text-danger-600' : 'text-gray-900' }}">
                                {{ $request->needed_by?->format('d.m.Y') ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase font-medium mb-1">Создана</dt>
                            <dd class="text-gray-700">{{ $request->created_at->format('d.m.Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400 uppercase font-medium mb-1">Обновлена</dt>
                            <dd class="text-gray-700">{{ $request->updated_at->format('d.m.Y H:i') }}</dd>
                        </div>
                    </div>
                </dl>
            </x-card>

            {{-- Notes --}}
            <x-card title="Внутренние заметки">
                <form wire:submit="saveNotes" class="space-y-3">
                    <textarea wire:model="notes"
                              rows="4"
                              placeholder="Заметки для внутреннего использования..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                    <div class="flex justify-end">
                        <x-button type="submit" variant="secondary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Сохранить</span>
                            <span wire:loading>Сохранение...</span>
                        </x-button>
                    </div>
                </form>
            </x-card>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            {{-- Status --}}
            <x-card title="Изменить статус">
                <div class="space-y-1.5">
                    @foreach(['submitted'=>'Новая','under_review'=>'На рассмотрении','quoted'=>'КП отправлено','closed'=>'Закрыта'] as $s => $label)
                        <button wire:click="changeStatus('{{ $s }}')"
                                @class(['w-full text-left px-3 py-2 rounded-lg text-sm transition-colors',
                                    'bg-primary-50 text-primary-700 font-medium' => $request->status === $s,
                                    'text-gray-600 hover:bg-gray-50' => $request->status !== $s])>
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </x-card>

            {{-- Assign manager --}}
            <x-card title="Назначить менеджера">
                <div class="space-y-3">
                    <select wire:model="assignManagerId"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">— Не назначен —</option>
                        @foreach($this->managers as $mgr)
                            <option value="{{ $mgr->id }}">{{ $mgr->name }}</option>
                        @endforeach
                    </select>
                    <x-button wire:click="assignManager" variant="secondary" class="w-full justify-center">
                        Сохранить
                    </x-button>
                </div>
            </x-card>

            {{-- Customer info --}}
            @if($request->customer)
                <x-card title="Клиент">
                    <dl class="space-y-2 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400 uppercase font-medium">Компания</dt>
                            <dd class="mt-0.5">
                                <a href="{{ route('admin.customers.show', $request->customer) }}"
                                   class="font-medium text-primary-600 hover:text-primary-700">
                                    {{ $request->customer->name }}
                                </a>
                            </dd>
                        </div>
                        @if($request->customer->phone)
                            <div>
                                <dt class="text-xs text-gray-400 uppercase font-medium">Телефон</dt>
                                <dd class="text-gray-700 mt-0.5">{{ $request->customer->phone }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>
            @endif

        </div>
    </div>
</div>
