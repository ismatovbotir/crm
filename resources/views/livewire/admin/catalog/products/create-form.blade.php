<form id="product-create-form" wire:submit="save" class="space-y-5">
    <div>
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Основное</h3>
        <div class="space-y-3">
            <div class="grid grid-cols-2 gap-3">
                <x-input label="SKU / Артикул" wire:model="sku" :error="$errors->first('sku')" required placeholder="POS-001" />
                <x-select label="Категория" wire:model="category_id">
                    <option value="">— выберите —</option>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name_ru }}</option>@endforeach
                </x-select>
            </div>
            <x-input label="Название (RU)" wire:model="name_ru" :error="$errors->first('name_ru')" required />
            <x-input label="Название (UZ)" wire:model="name_uz" />
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Бренд" wire:model="brand" placeholder="Epson" />
                <x-input label="Модель" wire:model="model_number" placeholder="TM-T88VI" />
            </div>
            <x-input label="Единица" wire:model="unit" placeholder="шт" />
        </div>
    </div>
    <div>
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Цены</h3>
        <x-select label="Валюта" wire:model="currency" class="mb-3">
            <option value="UZS">UZS (сум)</option>
            <option value="USD">USD</option>
        </x-select>
        <div class="grid grid-cols-3 gap-3">
            <x-input label="Розничная" type="number" wire:model="retail_price" placeholder="0" />
            <x-input label="Оптовая" type="number" wire:model="wholesale_price" placeholder="0" />
            <x-input label="Себестоимость" type="number" wire:model="cost_price" placeholder="0" hint="Скрыто от менеджеров" />
        </div>
    </div>
    <div>
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Описание</h3>
        <textarea wire:model="description_ru" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Краткое описание товара..."></textarea>
    </div>
    <div>
        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Видимость</h3>
        <div class="space-y-2">
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"> Активен
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" wire:model="is_visible_portal" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"> Виден в клиентском кабинете
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" wire:model="is_serial" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"> Серийный товар (учёт по серийным номерам)
            </label>
        </div>
    </div>
</form>
