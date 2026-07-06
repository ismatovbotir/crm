<div class="max-w-6xl mx-auto">

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.leads.index') }}" class="hover:text-primary-600 transition-colors">Лиды</a>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $lead->name }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $lead->name }}</h1>
            @if($lead->company)
            <p class="text-sm text-gray-500 mt-0.5">{{ $lead->company }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <x-lead-status-badge :status="$lead->status" />
            @if($lead->customer_id)
                <a href="{{ route('admin.customers.show', $lead->customer) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-success-700 bg-success-50 border border-success-200 rounded-lg hover:bg-success-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Клиент: {{ $lead->customer->name }}
                </a>
            @endif
            @can('update', $lead)
                @if(! $lead->customer_id && $lead->status !== 'won')
                <x-button variant="secondary" size="sm" wire:click="openConvertForm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Конвертировать в клиента
                </x-button>
                @endif
            <x-button variant="secondary" size="sm" wire:click="$set('showEditForm', true)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Редактировать
            </x-button>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left column: Activity --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Add note --}}
            @can('update', $lead)
            <x-card title="Добавить заметку">
                <form wire:submit="addNote" class="space-y-3">
                    <div>
                        <textarea
                            wire:model="noteText"
                            rows="3"
                            placeholder="Введите заметку о взаимодействии..."
                            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"
                        ></textarea>
                        @error('noteText')
                        <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end">
                        <x-button type="submit" size="sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="addNote">Добавить заметку</span>
                            <span wire:loading wire:target="addNote">Сохранение...</span>
                        </x-button>
                    </div>
                </form>
            </x-card>
            @endcan

            {{-- Activity timeline --}}
            <x-card title="История активности" :padding="false">
                @forelse($lead->activities()->latest()->get() as $activity)
                <div class="flex gap-3 px-5 py-4 border-b border-gray-50 last:border-0">
                    {{-- Avatar --}}
                    <div class="w-7 h-7 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <span class="text-xs font-bold text-primary-700">
                            {{ mb_strtoupper(mb_substr($activity->user?->name ?? '?', 0, 1)) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <span class="text-sm font-medium text-gray-900">{{ $activity->user?->name ?? 'Система' }}</span>
                            @if($activity->type)
                            <x-badge color="gray">{{ match($activity->type) {
                                'call'          => 'Звонок',
                                'email'         => 'Email',
                                'meeting'       => 'Встреча',
                                'note'          => 'Заметка',
                                'status_change' => 'Статус',
                                'quote_sent'    => 'КП отправлено',
                                default         => $activity->type
                            } }}</x-badge>
                            @endif
                            <span class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                        </div>
                        @if($activity->title)
                        <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $activity->title }}</p>
                        @endif
                        @if($activity->description)
                        <p class="text-sm text-gray-600 mt-0.5">{{ $activity->description }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-5 py-12 text-center">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <p class="text-sm text-gray-400">Активностей пока нет</p>
                </div>
                @endforelse
            </x-card>
        </div>

        {{-- Right column: Info + Status --}}
        <div class="space-y-4">

            {{-- Status change --}}
            <x-card title="Статус">
                @if($lead->status === 'client')
                <div class="mb-4 flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-md">
                    <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-green-700">Конвертирован в клиента</span>
                </div>
                @elsecan('update', $lead)
                <div class="flex gap-2 mb-4">
                    <select
                        wire:model="newStatus"
                        class="flex-1 rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
                    >
                        <option value="">— выберите статус —</option>
                        @foreach(['new','qualified','contacted','in_negotiation','won','lost'] as $s)
                        <option value="{{ $s }}" @selected($lead->status === $s)>{{ match($s) {
                            'new'            => 'Новый',
                            'qualified'      => 'Квалифицирован',
                            'contacted'      => 'Контакт',
                            'in_negotiation' => 'Переговоры',
                            'won'            => 'Успех',
                            'lost'           => 'Проигран',
                        } }}</option>
                        @endforeach
                    </select>
                    <button
                        type="button"
                        wire:click="applyStatus"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-md bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50 transition-colors"
                    >
                        Добавить
                    </button>
                </div>
                @endif

                {{-- Last 5 status changes --}}
                @php
                    $statusHistory = $lead->activities()->where('type', 'status_change')->latest()->take(5)->get();
                @endphp
                @if($statusHistory->isNotEmpty())
                <div class="space-y-2">
                    @foreach($statusHistory as $entry)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ match(data_get($entry->meta, 'to', '')) {
                            'new'            => 'Новый',
                            'qualified'      => 'Квалифицирован',
                            'contacted'      => 'Контакт',
                            'in_negotiation' => 'Переговоры',
                            'won'            => 'Успех',
                            'lost'           => 'Проигран',
                            default          => $entry->description,
                        } }}</span>
                        <span class="text-xs text-gray-400 whitespace-nowrap ml-2">{{ $entry->created_at->format('d.m H:i') }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-gray-400">История изменений пуста</p>
                @endif
            </x-card>

            {{-- Lead details --}}
            <x-card title="Информация о лиде">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Телефон</dt>
                        <dd class="mt-0.5">
                            <a href="tel:{{ $lead->phone }}" class="text-gray-900 hover:text-primary-600">
                                {{ $lead->phone }}
                            </a>
                        </dd>
                    </div>
                    @if($lead->email)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                        <dd class="mt-0.5">
                            <a href="mailto:{{ $lead->email }}" class="text-gray-900 hover:text-primary-600 break-all">
                                {{ $lead->email }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    @if($lead->source)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Источник</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $lead->source->name }}</dd>
                    </div>
                    @endif
                    @if($lead->businessType)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Тип бизнеса</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $lead->businessType->name }}</dd>
                    </div>
                    @endif
                    @if($lead->manager)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Менеджер</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $lead->manager->name }}</dd>
                    </div>
                    @endif
                    @if($lead->region)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Регион</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $lead->region }}</dd>
                    </div>
                    @endif
                    @if($lead->budget)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Бюджет</dt>
                        <dd class="mt-0.5 text-gray-900">{{ number_format($lead->budget, 0, '.', ' ') }} UZS</dd>
                    </div>
                    @endif
                    @if($lead->score)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Оценка</dt>
                        <dd class="mt-0.5 text-gray-900 flex items-center gap-1">
                            {{ $lead->score }}/10
                            <div class="flex gap-0.5 ml-1">
                                @for($i = 1; $i <= 10; $i++)
                                <div @class([
                                    'w-2 h-2 rounded-full',
                                    'bg-primary-500' => $i <= $lead->score,
                                    'bg-gray-200' => $i > $lead->score,
                                ])></div>
                                @endfor
                            </div>
                        </dd>
                    </div>
                    @endif
                    @if($lead->notes)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Заметки</dt>
                        <dd class="mt-0.5 text-gray-900 text-sm whitespace-pre-wrap">{{ $lead->notes }}</dd>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-gray-100">
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Создан</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $lead->created_at->format('d.m.Y H:i') }}</dd>
                    </div>
                    @if($lead->converted_at)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Конвертирован</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $lead->converted_at->format('d.m.Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            {{-- Linked customer --}}
            @if($lead->customer)
            <x-card title="Клиент">
                <a href="{{ route('admin.customers.show', $lead->customer) }}"
                   class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-primary-700">
                            {{ mb_strtoupper(mb_substr($lead->customer->name, 0, 1)) }}
                        </span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 group-hover:text-primary-600 truncate transition-colors">
                            {{ $lead->customer->name }}
                        </p>
                        <p class="text-xs text-gray-500">Перейти к клиенту</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </x-card>
            @endif
        </div>
    </div>

    {{-- Convert to customer slide-over --}}
    @if($showConvertForm)
    <x-slide-over title="Конвертировать в клиента">
        <div class="space-y-4">
            <p class="text-sm text-gray-500">Будет создан новый клиент и контактное лицо на основе данных лида.</p>
            <x-input
                label="Название компании"
                wire:model="convertName"
                :error="$errors->first('convertName')"
                required
                placeholder="ООО Ромашка"
            />
            <x-input
                label="Телефон"
                wire:model="convertPhone"
                :error="$errors->first('convertPhone')"
                placeholder="+998 90 000 00 00"
            />
            <x-input
                label="Email"
                type="email"
                wire:model="convertEmail"
                :error="$errors->first('convertEmail')"
                placeholder="info@company.uz"
            />
            <x-input
                label="Регион"
                wire:model="convertRegion"
                :error="$errors->first('convertRegion')"
                placeholder="Ташкент"
            />
            <div class="flex justify-end gap-3 pt-2">
                <x-button variant="secondary" wire:click="$set('showConvertForm', false)">Отмена</x-button>
                <x-button wire:click="convertToCustomer" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="convertToCustomer">Создать клиента</span>
                    <span wire:loading wire:target="convertToCustomer">Создание...</span>
                </x-button>
            </div>
        </div>
    </x-slide-over>
    @endif

    {{-- Edit slide-over --}}
    @if($showEditForm)
    <x-slide-over title="Редактировать лид" formId="lead-edit-form" saveLabel="Сохранить">
        @livewire('admin.leads.edit-form', ['leadId' => $lead->id], key('edit-show-'.$lead->id))
    </x-slide-over>
    @endif

</div>
