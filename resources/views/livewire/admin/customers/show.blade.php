<div
    x-data="{ activeTab: 'contacts' }"
    class="p-4 lg:p-6 max-w-6xl mx-auto"
>
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-gray-500 mb-2">
                <a href="{{ route('admin.customers.index') }}" class="hover:text-primary-600 transition-colors">Клиенты</a>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $customer->name }}</span>
            </nav>
            <h1 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h1>
            @if($customer->legal_name && $customer->legal_name !== $customer->name)
            <p class="text-sm text-gray-500 mt-0.5">{{ $customer->legal_name }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3 flex-shrink-0">
            <x-customer-status-badge :status="$customer->status" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main content: tabs --}}
        <div class="lg:col-span-2">

            {{-- Tab nav --}}
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Tabs">
                    <button type="button"
                        @click="activeTab = 'contacts'"
                        :class="activeTab === 'contacts'
                            ? 'border-primary-600 text-primary-700 bg-primary-50/60'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 px-4 py-2.5 border-b-2 text-sm font-medium transition-colors rounded-t-md whitespace-nowrap"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                        </svg>
                        Контакты
                        @if($customer->contacts->count())
                        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-1.5 py-0.5 rounded-full">{{ $customer->contacts->count() }}</span>
                        @endif
                    </button>
                    <button type="button"
                        @click="activeTab = 'leads'"
                        :class="activeTab === 'leads'
                            ? 'border-primary-600 text-primary-700 bg-primary-50/60'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 px-4 py-2.5 border-b-2 text-sm font-medium transition-colors rounded-t-md whitespace-nowrap"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Лиды
                        @if($customer->leads->count())
                        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-1.5 py-0.5 rounded-full">{{ $customer->leads->count() }}</span>
                        @endif
                    </button>
                    <button type="button"
                        @click="activeTab = 'quotes'"
                        :class="activeTab === 'quotes'
                            ? 'border-primary-600 text-primary-700 bg-primary-50/60'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 px-4 py-2.5 border-b-2 text-sm font-medium transition-colors rounded-t-md whitespace-nowrap"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        КП
                        @if($customer->quotes->count())
                        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-1.5 py-0.5 rounded-full">{{ $customer->quotes->count() }}</span>
                        @endif
                    </button>
                    <button type="button"
                        @click="activeTab = 'invoices'"
                        :class="activeTab === 'invoices'
                            ? 'border-primary-600 text-primary-700 bg-primary-50/60'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 px-4 py-2.5 border-b-2 text-sm font-medium transition-colors rounded-t-md whitespace-nowrap"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Инвойсы
                        @if($customer->invoices->count())
                        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-1.5 py-0.5 rounded-full">{{ $customer->invoices->count() }}</span>
                        @endif
                    </button>
                    <button type="button"
                        @click="activeTab = 'users'"
                        :class="activeTab === 'users'
                            ? 'border-primary-600 text-primary-700 bg-primary-50/60'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="flex items-center gap-2 px-4 py-2.5 border-b-2 text-sm font-medium transition-colors rounded-t-md whitespace-nowrap"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Пользователи
                        @if($customer->users->count())
                        <span class="bg-gray-100 text-gray-600 text-xs font-medium px-1.5 py-0.5 rounded-full">{{ $customer->users->count() }}</span>
                        @endif
                    </button>
                </nav>
            </div>

            {{-- Tab: Contacts --}}
            <div x-show="activeTab === 'contacts'" x-cloak>
                <x-card :padding="false">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Контактные лица</h3>
                    </div>
                    @forelse($customer->contacts as $contact)
                    <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-50 last:border-0">
                        <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-semibold text-gray-600">
                                {{ mb_strtoupper(mb_substr($contact->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-900">{{ $contact->name }}</p>
                                @if($contact->is_primary)
                                <x-badge color="blue">Основной</x-badge>
                                @endif
                            </div>
                            @if($contact->position)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $contact->position }}</p>
                            @endif
                            <div class="flex flex-wrap gap-3 mt-1">
                                @if($contact->phone)
                                <a href="tel:{{ $contact->phone }}" class="text-xs text-gray-600 hover:text-primary-600 flex items-center gap-1 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $contact->phone }}
                                </a>
                                @endif
                                @if($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="text-xs text-gray-600 hover:text-primary-600 flex items-center gap-1 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $contact->email }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <p class="text-sm text-gray-400">Контактные лица не добавлены</p>
                    </div>
                    @endforelse
                </x-card>
            </div>

            {{-- Tab: Leads --}}
            <div x-show="activeTab === 'leads'" x-cloak>
                <x-card :padding="false">
                    <div class="px-5 py-3.5 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Связанные лиды</h3>
                    </div>
                    @forelse($customer->leads as $lead)
                    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.leads.show', $lead) }}"
                               class="text-sm font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                {{ $lead->name }}
                            </a>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $lead->created_at->format('d.m.Y') }}</p>
                        </div>
                        <x-lead-status-badge :status="$lead->status" />
                        <a href="{{ route('admin.leads.show', $lead) }}"
                           class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <p class="text-sm text-gray-400">Лиды не найдены</p>
                    </div>
                    @endforelse
                </x-card>
            </div>

            {{-- Tab: Quotes --}}
            <div x-show="activeTab === 'quotes'" x-cloak>
                <x-card :padding="false">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Коммерческие предложения</h3>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.quotes.index') }}"
                               class="text-xs text-gray-400 hover:text-gray-600 font-medium transition-colors">
                                Все КП →
                            </a>
                            @can('create', \App\Models\Quote\Quote::class)
                            <x-button size="sm" wire:click="$set('showCreateQuoteForm', true)">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Новый КП
                            </x-button>
                            @endcan
                        </div>
                    </div>
                    @forelse($customer->quotes->sortByDesc('created_at') as $quote)
                    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.quotes.show', $quote) }}"
                               class="text-sm font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                КП {{ $quote->number }}
                            </a>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $quote->issue_date?->format('d.m.Y') ?? $quote->created_at->format('d.m.Y') }}</p>
                        </div>
                        <span class="text-sm text-gray-700 font-medium whitespace-nowrap">
                            {{ number_format($quote->total, 0, '.', ' ') }} {{ $quote->currency }}
                        </span>
                        <x-badge color="{{ match($quote->status) {
                            'accepted' => 'green',
                            'rejected' => 'red',
                            'sent'     => 'blue',
                            'viewed'   => 'blue',
                            'draft'    => 'gray',
                            'expired'  => 'red',
                            default    => 'gray'
                        } }}">{{ match($quote->status) {
                            'draft'    => 'Черновик',
                            'sent'     => 'Отправлен',
                            'viewed'   => 'Просмотрен',
                            'accepted' => 'Принят',
                            'rejected' => 'Отклонён',
                            'expired'  => 'Истёк',
                            default    => $quote->status
                        } }}</x-badge>
                        <a href="{{ route('admin.quotes.show', $quote) }}"
                           class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm text-gray-400">КП не найдены</p>
                    </div>
                    @endforelse
                </x-card>
            </div>

            {{-- Quote create slide-over --}}
            @if($showCreateQuoteForm)
            <x-slide-over title="Новое КП" form-id="document-create-form" save-label="Создать КП" size="4xl">
                @livewire('admin.documents.create-form', ['type' => 'quote', 'customerId' => $customer->id], key('create-quote-'.$customer->id))
            </x-slide-over>
            @endif

            {{-- Tab: Invoices --}}
            <div x-show="activeTab === 'invoices'" x-cloak>
                <x-card :padding="false">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Инвойсы</h3>
                        @can('viewAny', \App\Models\Invoice\Invoice::class)
                        <a href="{{ route('admin.invoices.index') }}"
                           class="text-xs text-primary-600 hover:text-primary-700 font-medium transition-colors">
                            Все инвойсы →
                        </a>
                        @endcan
                    </div>
                    @forelse($customer->invoices->sortByDesc('created_at') as $invoice)
                    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50/60 transition-colors">
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('admin.invoices.show', $invoice) }}"
                               class="text-sm font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                Инвойс {{ $invoice->number }}
                            </a>
                            @if($invoice->due_date)
                            <p class="text-xs text-gray-500 mt-0.5">до {{ $invoice->due_date->format('d.m.Y') }}</p>
                            @endif
                        </div>
                        <span class="text-sm text-gray-700 font-medium whitespace-nowrap">
                            {{ number_format($invoice->total, 0, '.', ' ') }} {{ $invoice->currency }}
                        </span>
                        <x-badge color="{{ match($invoice->status) {
                            'paid'          => 'green',
                            'partially_paid'=> 'blue',
                            'overdue'       => 'red',
                            'sent'          => 'blue',
                            'draft'         => 'gray',
                            'cancelled'     => 'red',
                            default         => 'gray'
                        } }}">{{ match($invoice->status) {
                            'draft'          => 'Черновик',
                            'sent'           => 'Отправлен',
                            'partially_paid' => 'Частично',
                            'paid'           => 'Оплачен',
                            'overdue'        => 'Просрочен',
                            'cancelled'      => 'Отменён',
                            default          => $invoice->status
                        } }}</x-badge>
                        <a href="{{ route('admin.invoices.show', $invoice) }}"
                           class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-sm text-gray-400">Инвойсы не найдены</p>
                    </div>
                    @endforelse
                </x-card>
            </div>

            {{-- Tab: Users --}}
            <div x-show="activeTab === 'users'" x-cloak>
                <x-card :padding="false">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Пользователи компании</h3>
                        @can('update', $customer)
                        <x-button type="button" size="sm" variant="ghost" wire:click="$set('showAddUserModal', true)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Добавить
                        </x-button>
                        @endcan
                    </div>
                    @forelse($customer->users as $portalUser)
                    <div class="flex items-center gap-4 px-5 py-3.5 border-b border-gray-50 last:border-0">
                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-xs font-semibold text-primary-700">
                                {{ mb_strtoupper(mb_substr($portalUser->name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $portalUser->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $portalUser->email }}</p>
                        </div>
                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                            {{ $portalUser->pivot->role === 'client-admin' ? 'Администратор' : 'Пользователь' }}
                        </span>
                        @can('update', $customer)
                        <button type="button"
                                wire:click="detachUser({{ $portalUser->id }})"
                                wire:confirm="Отвязать пользователя «{{ $portalUser->name }}» от этой компании?"
                                class="p-1.5 text-gray-400 hover:text-danger-600 hover:bg-danger-50 rounded transition-colors flex-shrink-0"
                                title="Отвязать">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        @endcan
                    </div>
                    @empty
                    <div class="px-5 py-10 text-center">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p class="text-sm text-gray-400">Пользователи портала не привязаны</p>
                        <p class="text-xs text-gray-400 mt-1">Добавьте пользователей, чтобы они могли видеть данные этой компании</p>
                    </div>
                    @endforelse
                </x-card>
            </div>
        </div>

        {{-- Right sidebar --}}
        <div class="space-y-4">

            {{-- Metrics --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ $customer->leads->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Лидов</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ $customer->quotes->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">КП</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 p-3 text-center">
                    <p class="text-xl font-bold text-gray-900">{{ $customer->invoices->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Инвойсов</p>
                </div>
            </div>

            {{-- Company details (inline editable) --}}
            @can('update', $customer)
            <div
                x-data="{
                    editing: null,
                    doSave(field) {
                        let f = this.editing;
                        this.editing = null;
                        $wire.saveField(f);
                    }
                }"
                @field-saved.window="editing = null"
            >
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Реквизиты</h3>
                        <span class="text-xs text-gray-400 italic">двойной клик — редактировать</span>
                    </div>
                    <dl class="divide-y divide-gray-50 px-4 py-2 space-y-0 text-sm">

                        {{-- Name --}}
                        <div @dblclick="editing = 'name'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Название</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'name'" class="text-gray-900 block">{{ $customer->name }}</span>
                                <input x-show="editing === 'name'" wire:model="editName"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'name') doSave('name')"
                                       @keydown.enter.prevent="doSave('name')"
                                       @keydown.escape="editing = null">
                                @error('name')<p class="text-xs text-danger-600 mt-0.5">{{ $message }}</p>@enderror
                            </dd>
                        </div>

                        {{-- Legal name --}}
                        <div @dblclick="editing = 'legal_name'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Юр. название</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'legal_name'" class="text-gray-900 block">{{ $customer->legal_name ?? '—' }}</span>
                                <input x-show="editing === 'legal_name'" wire:model="editLegalName"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'legal_name') doSave('legal_name')"
                                       @keydown.enter.prevent="doSave('legal_name')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- INN --}}
                        <div @dblclick="editing = 'inn'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">ИНН</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'inn'" class="text-gray-900 font-mono block">{{ $customer->inn ?? '—' }}</span>
                                <input x-show="editing === 'inn'" wire:model="editInn"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'inn') doSave('inn')"
                                       @keydown.enter.prevent="doSave('inn')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- OKED --}}
                        <div @dblclick="editing = 'oked'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">ОКЭД</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'oked'" class="text-gray-900 font-mono block">{{ $customer->oked ?? '—' }}</span>
                                <input x-show="editing === 'oked'" wire:model="editOked"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'oked') doSave('oked')"
                                       @keydown.enter.prevent="doSave('oked')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Business type --}}
                        <div @dblclick="editing = 'business_type_id'; $nextTick(() => $el.querySelector('select')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Тип бизнеса</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'business_type_id'" class="text-gray-900 block">{{ $customer->businessType?->name ?? '—' }}</span>
                                <select x-show="editing === 'business_type_id'" wire:model="editBusinessTypeId"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"
                                        @change="doSave('business_type_id')"
                                        @blur="if(editing === 'business_type_id') doSave('business_type_id')"
                                        @keydown.escape="editing = null">
                                    <option value="">— не выбрано —</option>
                                    @foreach($businessTypes as $bt)
                                    <option value="{{ $bt->id }}">{{ $bt->name }}</option>
                                    @endforeach
                                </select>
                            </dd>
                        </div>

                        {{-- Segment --}}
                        <div @dblclick="editing = 'segment'; $nextTick(() => $el.querySelector('select')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Сегмент</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'segment'">
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
                                </span>
                                <select x-show="editing === 'segment'" wire:model="editSegment"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"
                                        @change="doSave('segment')"
                                        @blur="if(editing === 'segment') doSave('segment')"
                                        @keydown.escape="editing = null">
                                    <option value="">— не выбрано —</option>
                                    <option value="A">A — крупный</option>
                                    <option value="B">B — средний</option>
                                    <option value="C">C — малый</option>
                                </select>
                            </dd>
                        </div>

                        {{-- Status --}}
                        <div @dblclick="editing = 'status'; $nextTick(() => $el.querySelector('select')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Статус</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'status'">
                                    <x-customer-status-badge :status="$customer->status" />
                                </span>
                                <select x-show="editing === 'status'" wire:model="editStatus"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"
                                        @change="doSave('status')"
                                        @blur="if(editing === 'status') doSave('status')"
                                        @keydown.escape="editing = null">
                                    <option value="active">Активный</option>
                                    <option value="vip">VIP</option>
                                    <option value="inactive">Неактивен</option>
                                    <option value="blocked">Заблокирован</option>
                                </select>
                            </dd>
                        </div>

                        {{-- Phone --}}
                        <div @dblclick="editing = 'phone'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Телефон</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'phone'" class="block">
                                    @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-gray-900 hover:text-primary-600" @click.stop>{{ $customer->phone }}</a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </span>
                                <input x-show="editing === 'phone'" wire:model="editPhone" type="tel"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'phone') doSave('phone')"
                                       @keydown.enter.prevent="doSave('phone')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Email --}}
                        <div @dblclick="editing = 'email'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'email'" class="block">
                                    @if($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="text-gray-900 hover:text-primary-600 break-all" @click.stop>{{ $customer->email }}</a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </span>
                                <input x-show="editing === 'email'" wire:model="editEmail" type="email"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'email') doSave('email')"
                                       @keydown.enter.prevent="doSave('email')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Website --}}
                        <div @dblclick="editing = 'website'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Сайт</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'website'" class="block">
                                    @if($customer->website)
                                    <a href="{{ $customer->website }}" target="_blank" rel="noopener" class="text-primary-600 hover:text-primary-700 break-all" @click.stop>{{ $customer->website }}</a>
                                    @else
                                    <span class="text-gray-400">—</span>
                                    @endif
                                </span>
                                <input x-show="editing === 'website'" wire:model="editWebsite"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'website') doSave('website')"
                                       @keydown.enter.prevent="doSave('website')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Region --}}
                        <div @dblclick="editing = 'region'; $nextTick(() => $el.querySelector('select')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Регион</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'region'" class="text-gray-900 block">{{ $customer->region ?? '—' }}</span>
                                <select x-show="editing === 'region'" wire:model="editRegion"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"
                                        @change="doSave('region')"
                                        @blur="if(editing === 'region') doSave('region')"
                                        @keydown.escape="editing = null">
                                    <option value="">— не выбрано —</option>
                                    @foreach($regions as $r)
                                    <option value="{{ $r }}">{{ $r }}</option>
                                    @endforeach
                                </select>
                            </dd>
                        </div>

                        {{-- City --}}
                        <div @dblclick="editing = 'city'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Город</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'city'" class="text-gray-900 block">{{ $customer->city ?? '—' }}</span>
                                <input x-show="editing === 'city'" wire:model="editCity"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'city') doSave('city')"
                                       @keydown.enter.prevent="doSave('city')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Address --}}
                        <div @dblclick="editing = 'address'; $nextTick(() => $el.querySelector('textarea')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Адрес</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'address'" class="text-gray-900 text-xs block whitespace-pre-wrap">{{ $customer->address ?? '—' }}</span>
                                <textarea x-show="editing === 'address'" wire:model="editAddress" rows="2"
                                          class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500 resize-none"
                                          @blur="if(editing === 'address') doSave('address')"
                                          @keydown.escape="editing = null"></textarea>
                            </dd>
                        </div>

                    </dl>
                </div>
            </div>

            {{-- Financial terms (inline editable) --}}
            <div
                x-data="{
                    editing: null,
                    doSave(field) {
                        let f = this.editing;
                        this.editing = null;
                        $wire.saveField(f);
                    }
                }"
                @field-saved.window="editing = null"
            >
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Финансовые условия</h3>
                        <span class="text-xs text-gray-400 italic">двойной клик — редактировать</span>
                    </div>
                    <dl class="divide-y divide-gray-50 px-4 py-2 text-sm">

                        {{-- Bank --}}
                        <div @dblclick="editing = 'bank_id'; $nextTick(() => $el.querySelector('select')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Банк</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'bank_id'" class="text-gray-900 block">{{ $customer->bank?->name ?? '—' }}</span>
                                <select x-show="editing === 'bank_id'" wire:model="editBankId"
                                        class="w-full border border-gray-300 rounded px-2 py-1 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-primary-500"
                                        @change="doSave('bank_id')"
                                        @blur="if(editing === 'bank_id') doSave('bank_id')"
                                        @keydown.escape="editing = null">
                                    <option value="">— не выбрано —</option>
                                    @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </dd>
                        </div>

                        {{-- Bank account --}}
                        <div @dblclick="editing = 'bank_account'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Расчётный счёт</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'bank_account'" class="text-gray-900 font-mono text-xs block">{{ $customer->bank_account ?? '—' }}</span>
                                <input x-show="editing === 'bank_account'" wire:model="editBankAccount" maxlength="20"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm font-mono focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'bank_account') doSave('bank_account')"
                                       @keydown.enter.prevent="doSave('bank_account')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Credit limit --}}
                        <div @dblclick="editing = 'credit_limit'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Кредитный лимит</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'credit_limit'" class="text-gray-900 block">
                                    {{ $customer->credit_limit ? number_format($customer->credit_limit, 0, '.', ' ') . ' UZS' : '—' }}
                                </span>
                                <input x-show="editing === 'credit_limit'" wire:model="editCreditLimit" type="number" min="0"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'credit_limit') doSave('credit_limit')"
                                       @keydown.enter.prevent="doSave('credit_limit')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        {{-- Payment terms --}}
                        <div @dblclick="editing = 'payment_terms_days'; $nextTick(() => $el.querySelector('input')?.focus())"
                             class="py-2.5 cursor-text rounded transition-colors hover:bg-gray-50 -mx-4 px-4">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Отсрочка платежа</dt>
                            <dd class="mt-0.5">
                                <span x-show="editing !== 'payment_terms_days'" class="text-gray-900 block">
                                    {{ $customer->payment_terms_days ? $customer->payment_terms_days . ' дней' : '—' }}
                                </span>
                                <input x-show="editing === 'payment_terms_days'" wire:model="editPaymentTermsDays" type="number" min="0"
                                       class="w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       @blur="if(editing === 'payment_terms_days') doSave('payment_terms_days')"
                                       @keydown.enter.prevent="doSave('payment_terms_days')"
                                       @keydown.escape="editing = null">
                            </dd>
                        </div>

                        @if($customer->customer_since)
                        <div class="py-2.5">
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Клиент с</dt>
                            <dd class="mt-0.5 text-gray-900">{{ \Carbon\Carbon::parse($customer->customer_since)->format('d.m.Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Notes (inline editable) --}}
            <div
                x-data="{
                    editing: false,
                    doSave() { this.editing = false; $wire.saveField('notes'); }
                }"
                @field-saved.window="editing = false"
            >
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900">Заметки</h3>
                        <button type="button" @click="editing = !editing"
                                class="text-xs text-gray-400 hover:text-primary-600 transition-colors">
                            <span x-show="!editing">Изменить</span>
                            <span x-show="editing">Сохранить</span>
                        </button>
                    </div>
                    <div class="px-4 py-3">
                        <p x-show="!editing" class="text-sm text-gray-700 whitespace-pre-wrap">{{ $customer->notes ?: 'Нет заметок' }}</p>
                        <textarea x-show="editing" wire:model="editNotes" rows="4"
                                  class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500 resize-none"
                                  @keydown.escape="editing = false"
                                  placeholder="Внутренние заметки..."></textarea>
                        <div x-show="editing" class="flex justify-end mt-2 gap-2">
                            <button type="button" @click="editing = false"
                                    class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1 rounded border border-gray-300 hover:bg-gray-50 transition-colors">
                                Отмена
                            </button>
                            <button type="button" @click="doSave()"
                                    class="text-xs text-white bg-primary-600 hover:bg-primary-700 px-2 py-1 rounded transition-colors">
                                Сохранить
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @else
            {{-- Read-only sidebar for users without update permission --}}
            <x-card title="Реквизиты">
                <dl class="space-y-3 text-sm">
                    @if($customer->inn)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">ИНН</dt>
                        <dd class="mt-0.5 text-gray-900 font-mono">{{ $customer->inn }}</dd>
                    </div>
                    @endif
                    @if($customer->businessType)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Тип бизнеса</dt>
                        <dd class="mt-0.5 text-gray-900">{{ $customer->businessType->name }}</dd>
                    </div>
                    @endif
                    @if($customer->phone)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Телефон</dt>
                        <dd class="mt-0.5"><a href="tel:{{ $customer->phone }}" class="text-gray-900 hover:text-primary-600">{{ $customer->phone }}</a></dd>
                    </div>
                    @endif
                    @if($customer->email)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                        <dd class="mt-0.5"><a href="mailto:{{ $customer->email }}" class="text-gray-900 hover:text-primary-600">{{ $customer->email }}</a></dd>
                    </div>
                    @endif
                </dl>
            </x-card>
            @endcan

            <p class="text-xs text-gray-400 text-center">
                Создан {{ $customer->created_at->format('d.m.Y H:i') }}
            </p>
        </div>
    </div>

    {{-- Add User Modal --}}
    <div
        x-data="{ show: @entangle('showAddUserModal') }"
        @keydown.escape.window="if(show) $wire.set('showAddUserModal', false)"
    >
        <div x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/50" @click="$wire.set('showAddUserModal', false)"></div>
            <div class="flex min-h-full items-start justify-center p-4 pt-16">
                <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-md z-10" @click.stop>
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-900">Добавить пользователя</h2>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="$wire.set('showAddUserModal', false)"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                                Отмена
                            </button>
                            <button type="button" wire:click="attachUser"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                                Привязать
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-gray-500">Введите email существующего пользователя для привязки к этой компании. Пользователь получит доступ к клиентскому порталу.</p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email пользователя <span class="text-danger-500">*</span></label>
                            <input type="email" wire:model="addUserEmail"
                                   placeholder="user@example.com"
                                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   @keydown.enter.prevent="$wire.attachUser()">
                            @error('addUserEmail')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Роль</label>
                            <select wire:model="addUserRole"
                                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                <option value="client-user">Пользователь портала</option>
                                <option value="client-admin">Администратор компании</option>
                            </select>
                            @error('addUserRole')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
