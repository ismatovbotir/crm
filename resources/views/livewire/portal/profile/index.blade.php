<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Профиль компании</h1>
        <p class="text-sm text-gray-500 mt-0.5">Реквизиты, контакты и пользователи</p>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
            {{ session('success') }}
        </div>
    @endif

    @if($customer)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Company info --}}
            <div class="lg:col-span-2 space-y-6">
                <x-card title="Информация о компании">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Название</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900">{{ $customer->name }}</dd>
                        </div>
                        @if($customer->legal_name)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Юридическое название</dt>
                                <dd class="mt-1 text-sm text-gray-700">{{ $customer->legal_name }}</dd>
                            </div>
                        @endif
                        @if($customer->inn)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">ИНН</dt>
                                <dd class="mt-1 text-sm font-mono text-gray-700">{{ $customer->inn }}</dd>
                            </div>
                        @endif
                        @if($customer->phone)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Телефон</dt>
                                <dd class="mt-1 text-sm text-gray-700">{{ $customer->phone }}</dd>
                            </div>
                        @endif
                        @if($customer->email)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Email</dt>
                                <dd class="mt-1 text-sm text-gray-700">{{ $customer->email }}</dd>
                            </div>
                        @endif
                        @if($customer->address)
                            <div class="sm:col-span-2">
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Адрес</dt>
                                <dd class="mt-1 text-sm text-gray-700">{{ $customer->address }}</dd>
                            </div>
                        @endif
                        @if($customer->website)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Сайт</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="{{ $customer->website }}" target="_blank"
                                       class="text-primary-600 hover:underline">
                                        {{ $customer->website }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-card>

                {{-- Contacts --}}
                <x-card title="Контактные лица">
                    @if($contacts->isEmpty())
                        <p class="text-sm text-gray-400 py-2 text-center">Контактные лица не добавлены</p>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($contacts as $contact)
                                <div class="py-3 flex items-start gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 text-sm font-medium flex-shrink-0">
                                        {{ mb_substr($contact->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $contact->name }}</p>
                                        @if($contact->position)
                                            <p class="text-xs text-gray-500">{{ $contact->position }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-3 mt-1">
                                            @if($contact->phone)
                                                <a href="tel:{{ $contact->phone }}"
                                                   class="text-xs text-gray-500 hover:text-primary-600">
                                                    {{ $contact->phone }}
                                                </a>
                                            @endif
                                            @if($contact->email)
                                                <a href="mailto:{{ $contact->email }}"
                                                   class="text-xs text-gray-500 hover:text-primary-600">
                                                    {{ $contact->email }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Portal users sidebar --}}
            <div class="space-y-6">
                <x-card title="Пользователи портала">
                    @if($users->isEmpty())
                        <p class="text-sm text-gray-400 py-2 text-center">Нет активных пользователей</p>
                    @else
                        <div class="space-y-3">
                            @foreach($users as $user)
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-xs font-medium flex-shrink-0">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                    </div>
                                    @php
                                        $userRole = $user->getRoleNames()->first();
                                    @endphp
                                    @if($userRole)
                                        <x-badge color="{{ $userRole === 'client-admin' ? 'blue' : 'gray' }}">
                                            {{ $userRole === 'client-admin' ? 'Админ' : 'Пользователь' }}
                                        </x-badge>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>

                {{-- My account --}}
                <x-card title="Мой аккаунт">
                    @php $me = auth()->user(); @endphp
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Имя</dt>
                            <dd class="mt-1 text-gray-900">{{ $me->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Email</dt>
                            <dd class="mt-1 text-gray-700">{{ $me->email }}</dd>
                        </div>
                        @if($me->phone)
                            <div>
                                <dt class="text-xs text-gray-500 uppercase font-medium tracking-wide">Телефон</dt>
                                <dd class="mt-1 text-gray-700">{{ $me->phone }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>
            </div>

        </div>
    @else
        <x-card>
            <div class="py-8 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-sm text-gray-500">Ваш аккаунт не привязан к компании.</p>
                <p class="text-sm text-gray-400 mt-1">Обратитесь к менеджеру RSG для настройки.</p>
            </div>
        </x-card>
    @endif
</div>
