<div>
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Категории</h1>
            <p class="text-sm text-gray-500 mt-0.5">Иерархия товарных категорий (синхронизируется из 1С)</p>
        </div>
        <a href="{{ route('admin.catalog.products.index') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Все товары
        </a>
    </div>

    {{-- Search --}}
    <x-card class="mb-4" :padding="false">
        <div class="p-4">
            <div class="relative max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Поиск по названию..."
                    class="w-full rounded-md border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
            </div>
        </div>
    </x-card>

    {{-- Categories list --}}
    <x-card :padding="false">
        <div class="divide-y divide-gray-50">
            @forelse($categories as $cat)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50/80 transition-colors">
                <span class="text-2xl w-9 text-center flex-shrink-0">{{ $cat->icon ?? '📦' }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900">{{ $cat->name_ru }}</p>
                    @if($cat->name_uz)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $cat->name_uz }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-sm text-gray-500 flex-shrink-0">
                    @if($cat->children_count > 0)
                    <span class="text-xs text-gray-400">{{ $cat->children_count }} подкатег.</span>
                    @endif
                    <span class="text-xs font-medium text-gray-600">{{ $cat->products_count }} товаров</span>
                    @if($cat->is_active)
                    <x-badge color="green">Активна</x-badge>
                    @else
                    <x-badge color="gray">Откл.</x-badge>
                    @endif
                </div>
                <a href="{{ route('admin.catalog.products.index', ['categoryFilter' => $cat->id]) }}"
                   title="Смотреть товары"
                   class="p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors inline-flex flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @empty
            <div class="py-14 text-center">
                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-sm text-gray-400">Категории не найдены</p>
                @if($search)
                <p class="text-xs text-gray-400 mt-1">Попробуйте изменить запрос</p>
                @endif
            </div>
            @endforelse
        </div>
    </x-card>
</div>
