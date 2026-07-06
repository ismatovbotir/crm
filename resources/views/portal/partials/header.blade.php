{{-- Mobile menu toggle --}}
<button @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

{{-- Company name --}}
<div class="flex-1 ml-2 lg:ml-0">
    @php $companyName = auth()->user()?->customers()?->first()?->name ?? 'Личный кабинет'; @endphp
    <p class="text-sm font-medium text-gray-700 hidden md:block">{{ $companyName }}</p>
</div>

{{-- Right side: user dropdown --}}
<div class="flex items-center gap-2 ml-4 relative" x-data="{ open: false }">
    @php $userName = auth()->user()->name ?? 'Клиент'; @endphp

    <button @click="open = !open"
            class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100 transition-colors">
        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-sm font-medium">
            {{ mb_substr($userName, 0, 1) }}
        </div>
        <span class="hidden md:block text-sm font-medium text-gray-700">{{ $userName }}</span>
        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 top-12 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-50"
         style="display: none">

        <a href="/portal/profile"
           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Профиль компании</a>

        <div class="border-t border-gray-200 my-1"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full text-left px-4 py-2 text-sm text-danger-600 hover:bg-gray-50">
                Выйти
            </button>
        </form>
    </div>
</div>
