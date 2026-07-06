{{-- Mobile menu toggle --}}
<button @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden p-2 -ml-2 rounded-md text-gray-500 hover:bg-gray-100">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>


<div class="flex-1 max-w-xl ml-2 lg:ml-0">
{{-- Global search
<div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="search"
               placeholder="Поиск по клиентам, лидам, КП..."
               class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm focus:bg-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500"/>
    </div>
 --}}
</div>

{{-- Right side actions --}}
<div class="flex items-right gap-1">

    <button class="relative p-2 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <span class="absolute top-1 right-1 w-2 h-2 bg-danger-500 rounded-full"></span>
    </button>

    <div class="relative" x-data="{ open: false }">
        @php
            $userName = auth()->user()->name ?? 'Гость';
        @endphp
        <button @click="open = !open"
                class="flex items-center gap-2 p-2 rounded-md hover:bg-gray-100">
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
             class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-40"
             style="display: none">

            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Профиль</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Настройки аккаунта</a>
            <div class="border-t border-gray-200 my-1"></div>
            <a href="#" class="block px-4 py-2 text-sm text-danger-600 hover:bg-gray-50">Выйти</a>
        </div>
    </div>
</div>
