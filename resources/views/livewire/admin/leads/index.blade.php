<div>
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Лиды</h1>
            <p class="text-sm text-gray-500 mt-0.5">Управление потенциальными клиентами</p>
        </div>
        <div class="flex items-center gap-2">
            @can('create', \App\Models\Lead\Lead::class)
            <x-button @click="$dispatch('open-lead-modal')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Новый лид
            </x-button>
            @endcan
        </div>
    </div>

    {{-- Flash message --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <x-card class="mb-4" :padding="false">
        <div class="flex flex-wrap gap-3 p-4">
            <div class="flex-1 min-w-52">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Поиск по имени, компании, телефону..."
                        class="w-full rounded-md border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
            </div>
            <select
                wire:model.live="statusFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
            >
                <option value="">Все статусы</option>
                @foreach($statuses as $s)
                <option value="{{ $s }}">{{ match($s) {
                    'new'            => 'Новый',
                    'qualified'      => 'Квалифицирован',
                    'contacted'      => 'Контакт',
                    'in_negotiation' => 'Переговоры',
                    'won'            => 'Успех',
                    'lost'           => 'Проигран',
                    'client'         => 'Конвертирован',
                    default          => $s
                } }}</option>
                @endforeach
            </select>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false">
        <div class="overflow-auto" style="height: calc(100vh - 26rem); min-height: 20rem;">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10">
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Имя / Компания</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Телефон</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Источник</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Статус</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Менеджер</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Автор</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap bg-gray-50">Создан</th>
                        <th class="px-4 py-3 w-10 bg-gray-50"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leads as $lead)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.leads.show', $lead) }}"
                               class="font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                {{ $lead->name }}
                            </a>
                            @if($lead->company)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $lead->company }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $lead->phone }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $lead->source?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-lead-status-badge :status="$lead->status" />
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $lead->manager?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $lead->creator?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                            {{ $lead->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.leads.show', $lead) }}"
                               title="Открыть"
                               class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors inline-flex">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-14 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                            </svg>
                            <p class="text-sm text-gray-400">Лиды не найдены</p>
                            @if($search || $statusFilter)
                            <p class="text-xs text-gray-400 mt-1">Попробуйте изменить фильтры</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->total() > 0)
        <div class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center justify-between gap-4 flex-wrap">

                {{-- Info + per-page --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">
                        Показано <span class="font-medium text-gray-700">{{ $leads->firstItem() }}–{{ $leads->lastItem() }}</span>
                        из <span class="font-medium text-gray-700">{{ $leads->total() }}</span> лидов
                    </span>
                    <select
                        wire:model.live="perPage"
                        class="border border-gray-300 rounded-md px-2 py-1 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-600"
                    >
                        <option value="15">15 / стр.</option>
                        <option value="25">25 / стр.</option>
                        <option value="50">50 / стр.</option>
                    </select>
                </div>

                {{-- Page navigation --}}
                @if($leads->hasPages())
                <nav class="flex items-center gap-0.5">
                    @if($leads->onFirstPage())
                    <span class="p-1.5 text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                    @else
                    <button wire:click="previousPage" type="button" class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    @endif

                    @foreach($leads->getUrlRange(max(1, $leads->currentPage() - 2), min($leads->lastPage(), $leads->currentPage() + 2)) as $page => $url)
                        @if($page == $leads->currentPage())
                        <span class="px-3 py-1 text-sm font-medium bg-primary-600 text-white rounded-md">{{ $page }}</span>
                        @else
                        <button wire:click="gotoPage({{ $page }})" type="button" class="px-3 py-1 text-sm text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-md transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach

                    @if($leads->hasMorePages())
                    <button wire:click="nextPage" type="button" class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                    @else
                    <span class="p-1.5 text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                    @endif
                </nav>
                @endif

            </div>
        </div>
        @endif
    </x-card>

    {{-- Create modal --}}
    <x-modal title="Новый лид" open-event="open-lead-modal" close-event="close-lead-modal" save-event="lead-saved" form-id="lead-create-form" save-label="Создать" cancel-event="close-lead-modal">
        <livewire:admin.leads.create-form />
    </x-modal>
</div>
