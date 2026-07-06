<div x-data="{ showImport: false }">
    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Товары</h1>
            <p class="text-sm text-gray-500 mt-0.5">Каталог оборудования RSG (синхронизируется из 1С)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.catalog.categories.index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Категории
            </a>
            @can('catalog.import')
            <button type="button" @click="showImport = !showImport"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Импорт 1С
            </button>
            @endcan
            @can('update', \App\Models\Catalog\Product::class)
            <x-button wire:click="openCreate">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Создать товар
            </x-button>
            @endcan
        </div>
    </div>

    {{-- Import panel --}}
    @can('catalog.import')
    <div x-show="showImport" x-transition class="mb-4 bg-blue-50 border border-blue-200 rounded-lg px-5 py-4">
        <form action="{{ route('admin.import.catalog') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3 flex-wrap">
            @csrf
            <input type="file" name="file" accept=".csv"
                   class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
            <button type="submit"
                    class="px-3 py-1.5 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors">
                Импортировать
            </button>
            <a href="{{ route('admin.import.catalog.template') }}"
               class="text-sm text-primary-600 hover:underline">
                Скачать шаблон CSV
            </a>
            <button type="button" @click="showImport = false" class="text-sm text-gray-500 hover:text-gray-700">Отмена</button>
        </form>
    </div>
    @endcan

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 px-4 py-3 bg-success-50 border border-success-200 rounded-lg text-sm text-success-700">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
    <div class="mb-4 px-4 py-3 bg-warning-50 border border-warning-200 rounded-lg text-sm text-warning-700">
        <p class="font-medium mb-1">Ошибки при импорте:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach(session('import_errors') as $err)<li>{{ $err }}</li>@endforeach
        </ul>
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
                        placeholder="Поиск по названию, SKU, бренду..."
                        class="w-full rounded-md border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                    >
                </div>
            </div>
            <select
                wire:model.live="categoryFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
            >
                <option value="">Все категории</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name_ru }}</option>
                @endforeach
            </select>
            <select
                wire:model.live="activeFilter"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white"
            >
                <option value="">Все статусы</option>
                <option value="1">Активные</option>
                <option value="0">Отключённые</option>
            </select>
        </div>
    </x-card>

    {{-- Table --}}
    <x-card :padding="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/60">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Товар</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">SKU</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Категория</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Розн. цена</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Остаток</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap">Статус</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.catalog.products.show', $product) }}"
                               class="font-medium text-gray-900 hover:text-primary-600 transition-colors">
                                {{ $product->name_ru }}
                            </a>
                            @if($product->brand)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $product->brand }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $product->sku ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $product->category?->name_ru ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-900 whitespace-nowrap">
                            @php $price = $product->prices->where('type', 'retail')->where('is_active', true)->first() @endphp
                            @if($price)
                                {{ number_format($price->amount, 0, '.', ' ') }}
                                <span class="text-xs text-gray-400">{{ $price->currency }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($product->stock)
                                <span @class([
                                    'font-medium',
                                    'text-success-700' => $product->stock->available > 0,
                                    'text-danger-600'  => $product->stock->available <= 0,
                                ])>{{ $product->stock->available }}</span>
                                <span class="text-xs text-gray-400">шт</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($product->is_active)
                            <x-badge color="green">Активен</x-badge>
                            @else
                            <x-badge color="gray">Откл.</x-badge>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.catalog.products.show', $product) }}"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <p class="text-sm text-gray-400">Товары не найдены</p>
                            @if($search || $categoryFilter || $activeFilter !== '')
                            <p class="text-xs text-gray-400 mt-1">Попробуйте изменить фильтры</p>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->total() > 0)
        <div class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center justify-between gap-4 flex-wrap">

                {{-- Info + per-page --}}
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-500">
                        Показано <span class="font-medium text-gray-700">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
                        из <span class="font-medium text-gray-700">{{ $products->total() }}</span> товаров
                    </span>
                    <select
                        wire:model.live="perPage"
                        class="border border-gray-300 rounded-md px-2 py-1 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-gray-600"
                    >
                        <option value="20">20 / стр.</option>
                        <option value="50">50 / стр.</option>
                        <option value="100">100 / стр.</option>
                    </select>
                </div>

                {{-- Page navigation --}}
                @if($products->hasPages())
                <nav class="flex items-center gap-0.5">
                    @if($products->onFirstPage())
                    <span class="p-1.5 text-gray-300 cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                    @else
                    <button wire:click="previousPage" type="button" class="p-1.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    @endif

                    @foreach($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                        @if($page == $products->currentPage())
                        <span class="px-3 py-1 text-sm font-medium bg-primary-600 text-white rounded-md">{{ $page }}</span>
                        @else
                        <button wire:click="gotoPage({{ $page }})" type="button" class="px-3 py-1 text-sm text-gray-600 hover:text-primary-600 hover:bg-primary-50 rounded-md transition-colors">{{ $page }}</button>
                        @endif
                    @endforeach

                    @if($products->hasMorePages())
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

    @if($showCreate)
    <x-slide-over title="Новый товар" formId="product-create-form">
        <livewire:admin.catalog.products.create-form />
    </x-slide-over>
    @endif
</div>
