<div>
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Клиенты</h1>
            <p class="text-sm text-gray-500 mt-0.5">База компаний-клиентов RSG</p>
        </div>
        <div class="flex items-center gap-2">
            @can('customers.export')
            <a href="{{ route('admin.export.customers') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV
            </a>
            @endcan
            @can('create', \App\Models\Customer\Customer::class)
            <x-button type="button" @click="$dispatch('open-customer-modal')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Новый клиент
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
                        placeholder="Поиск по названию, ИНН, телефону..."
                        class="w-full rounded-md border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
            </div>
            <select
                wire:model.live="statusFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
            >
                <option value="">Все статусы</option>
                <option value="active">Активный</option>
                <option value="vip">VIP</option>
                <option value="inactive">Неактивен</option>
                <option value="blocked">Заблокирован</option>
            </select>
            <select
                wire:model.live="segmentFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
            >
                <option value="">Все сегменты</option>
                <option value="A">Сегмент A</option>
                <option value="B">Сегмент B</option>
                <option value="C">Сегмент C</option>
            </select>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Компания</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">ИНН</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Тип бизнеса</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Сегмент</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Статус</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Телефон</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                {{ $customer->name }}
                            </a>
                            @if($customer->legal_name && $customer->legal_name !== $customer->name)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $customer->legal_name }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $customer->inn ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $customer->businessType?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($customer->segment)
                            <span @class([
                                'inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold',
                                'bg-success-100 text-success-700' => $customer->segment === 'A',
                                'bg-primary-100 text-primary-700' => $customer->segment === 'B',
                                'bg-gray-100 text-gray-600'       => $customer->segment === 'C',
                            ])>{{ $customer->segment }}</span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-customer-status-badge :status="$customer->status" />
                        </td>
                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.customers.show', $customer) }}"
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
                        <td colspan="7" class="px-4 py-14 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="text-sm text-gray-400">Клиенты не найдены</p>
                            @if($search || $statusFilter || $segmentFilter)
                            <p class="text-xs text-gray-400 mt-1">Попробуйте изменить фильтры</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->total() > 0)
        <div class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center justify-between gap-4 flex-wrap">

                {{-- Info + per-page --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">
                        Показано <span class="font-medium text-gray-700">{{ $customers->firstItem() }}–{{ $customers->lastItem() }}</span>
                        из <span class="font-medium text-gray-700">{{ $customers->total() }}</span> клиентов
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
                @if($customers->hasPages())
                <nav class="flex items-center gap-0.5">
                    @if($customers->onFirstPage())
                    <span class="p-1.5 text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                    @else
                    <button wire:click="previousPage" type="button" class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    @endif

                    @foreach($customers->getUrlRange(max(1, $customers->currentPage() - 2), min($customers->lastPage(), $customers->currentPage() + 2)) as $page => $url)
                        @if($page == $customers->currentPage())
                        <span class="px-3 py-1 text-sm font-medium bg-primary-600 text-white rounded-md">{{ $page }}</span>
                        @else
                        <button wire:click="gotoPage({{ $page }})" type="button" class="px-3 py-1 text-sm text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-md transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach

                    @if($customers->hasMorePages())
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
    <x-modal title="Новый клиент" open-event="open-customer-modal" close-event="close-customer-modal" save-event="customer-saved" form-id="customer-create-form" save-label="Создать" cancel-event="close-customer-modal" width="max-w-2xl">
        <livewire:admin.customers.create-form />
    </x-modal>
</div>
