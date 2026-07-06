<div class="max-w-lg mx-auto mt-16">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="px-8 py-6 border-b border-gray-100 bg-primary-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-primary-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-lg">R</span>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">RSG-CRM — Первоначальная настройка</h1>
                    <p class="text-sm text-gray-500">Инициализация ролей и прав доступа</p>
                </div>
            </div>
        </div>

        <div class="px-8 py-6">

            @if(!$done)
                <p class="text-sm text-gray-600 mb-6">
                    В базе данных нет ролей. Нажмите кнопку ниже — система создаст все роли, права доступа
                    и назначит вам роль <strong>Super Admin</strong>.
                </p>

                <div class="bg-gray-50 rounded-lg px-4 py-3 mb-6 text-xs text-gray-500 space-y-1">
                    <p>Будут созданы роли:</p>
                    @foreach(config('permissions.roles') as $slug => $role)
                        <p class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-400 inline-block"></span>
                            <strong>{{ $role['label'] }}</strong> — {{ $role['description'] }}
                        </p>
                    @endforeach
                </div>

                <x-button wire:click="initialize" wire:loading.attr="disabled" class="w-full justify-center">
                    <span wire:loading.remove>Инициализировать систему</span>
                    <span wire:loading>Создание ролей...</span>
                </x-button>

            @else
                <div class="flex items-start gap-3 mb-6">
                    <div class="w-8 h-8 bg-success-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">Система инициализирована</p>
                        <pre class="mt-2 text-xs text-gray-500 whitespace-pre-wrap">{{ $log }}</pre>
                    </div>
                </div>

                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex w-full justify-center items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 transition-colors">
                    Перейти к дашборду
                </a>
            @endif

        </div>
    </div>
</div>
