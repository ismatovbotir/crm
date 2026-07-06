<div class="w-full max-w-md">
    <div class="bg-white rounded-lg shadow-lg p-8">

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            <div class="w-12 h-12 bg-primary-600 rounded-lg flex items-center justify-center mb-3">
                <span class="text-white font-bold text-xl">R</span>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">RSG-CRM</h1>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-900">Войти в систему</h2>
            <p class="text-sm text-gray-500 mt-1">Введите учётные данные</p>
        </div>

        @if($errors->has('email'))
            <div class="mb-4 p-3 bg-danger-50 border border-danger-100 rounded-md text-sm text-danger-700">
                {{ $errors->first('email') }}
            </div>
        @endif

        <form wire:submit="login" class="space-y-4">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    type="email"
                    id="email"
                    wire:model="email"
                    placeholder="user@rsg.uz"
                    autocomplete="email"
                    autofocus
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"/>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-sm font-medium text-gray-700">Пароль</label>
                    <a href="#" class="text-xs text-primary-600 hover:text-primary-700">Забыли?</a>
                </div>
                <input
                    type="password"
                    id="password"
                    wire:model="password"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"/>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" wire:model="remember"
                       class="h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500"/>
                <label for="remember" class="ml-2 text-sm text-gray-700">Запомнить меня</label>
            </div>

            <button type="submit"
                    class="w-full bg-primary-600 text-white py-2 px-4 rounded-md font-medium hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition disabled:opacity-50"
                    wire:loading.attr="disabled"
                    wire:target="login">
                <span wire:loading.remove wire:target="login">Войти</span>
                <span wire:loading wire:target="login">Вход...</span>
            </button>

        </form>
    </div>

    @if(app()->environment('local'))
    <div class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4">
        <p class="text-xs font-semibold text-amber-700 uppercase tracking-wide mb-3">DEV — Демо аккаунты</p>
        <div class="grid grid-cols-2 gap-1.5">
            @foreach([
                ['email' => 'admin@rsg.uz',      'label' => 'Super Admin'],
                ['email' => 'director@rsg.uz',   'label' => 'Director'],
                ['email' => 'manager@rsg.uz',    'label' => 'Manager'],
                ['email' => 'support@rsg.uz',    'label' => 'Support'],
                ['email' => 'catalog@rsg.uz',    'label' => 'Catalog'],
                ['email' => 'accountant@rsg.uz', 'label' => 'Accountant'],
            ] as $demo)
            <button type="button"
                    wire:click="fillDemo('{{ $demo['email'] }}')"
                    class="text-left px-2.5 py-1.5 bg-white border border-amber-200 rounded text-xs text-gray-700 hover:bg-amber-100 hover:border-amber-300 transition truncate">
                <span class="font-medium">{{ $demo['label'] }}</span>
                <span class="text-gray-400 block truncate">{{ $demo['email'] }}</span>
            </button>
            @endforeach
        </div>
        <p class="text-xs text-amber-600 mt-2">Пароль для всех: <code class="font-mono bg-amber-100 px-1 rounded">password</code></p>
    </div>
    @endif
</div>
