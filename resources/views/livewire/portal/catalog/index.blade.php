<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Каталог оборудования</h1>
            <p class="text-sm text-gray-500 mt-0.5">Ознакомьтесь с ассортиментом RSG</p>
        </div>
    </div>

    {{-- Search + category filter --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Поиск по названию, SKU..."
                class="w-full pl-9 pr-4 py-2 text-sm bg-white border border-gray-300 rounded-lg
                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            >
        </div>
        <select
            wire:model.live="categoryFilter"
            class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white sm:w-56">
            <option value="">Все категории</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name_ru ?? $cat->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Product grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($products as $product)
            <div class="bg-white rounded-lg border border-gray-200 shadow-card overflow-hidden hover:shadow-md transition-shadow group">

                {{-- Product image --}}
                <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
                    @if($product->primaryImage)
                        <img src="{{ Storage::url($product->primaryImage->path) }}"
                             alt="{{ $product->name_ru }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                    @else
                        <svg class="w-16 h-16 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    @endif
                </div>

                {{-- Product info --}}
                <div class="p-4">
                    {{-- Category badge --}}
                    @if($product->category)
                        <x-badge color="gray" class="mb-2">{{ $product->category->name_ru ?? $product->category->name }}</x-badge>
                    @endif

                    <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-2">
                        {{ $product->name_ru ?? $product->name }}
                    </h3>

                    @if($product->sku)
                        <p class="text-xs text-gray-400 font-mono mb-2">{{ $product->sku }}</p>
                    @endif

                    @if($product->brand)
                        <p class="text-xs text-gray-500 mb-2">{{ $product->brand }}</p>
                    @endif

                    {{-- Price --}}
                    @php
                        $retailPrice = $product->prices?->firstWhere('type', 'retail');
                    @endphp
                    @if($retailPrice)
                        <p class="text-sm font-semibold text-primary-700 mt-2">
                            {{ number_format($retailPrice->price, 0, '.', ' ') }}
                            <span class="text-xs font-normal text-gray-400">{{ $retailPrice->currency }}</span>
                        </p>
                    @endif

                    {{-- Inquiry button --}}
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <a href="/portal/tickets?subject={{ urlencode('Запрос по товару: ' . ($product->name_ru ?? $product->name)) }}"
                           class="block text-center text-xs font-medium text-primary-600 hover:text-primary-800 py-1.5 rounded-md hover:bg-primary-50 transition-colors">
                            Запросить
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm text-gray-400">
                    @if($search || $categoryFilter)
                        Товары не найдены. <button wire:click="$set('search', ''); $set('categoryFilter', '')" class="text-primary-600 hover:underline">Сбросить фильтры</button>
                    @else
                        Каталог пуст
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
</div>
