<form id="sell-create-form" wire:submit="save" class="space-y-4">

    <x-select label="Клиент" wire:model.live="customer_id" :error="$errors->first('customer_id')" :required="true">
        <option value="">— выберите клиента —</option>
        @foreach($customers as $customer)
        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
        @endforeach
    </x-select>

    <x-select label="Инвойс (опционально)" wire:model.live="invoice_id" :error="$errors->first('invoice_id')">
        <option value="">Без инвойса / прямая продажа</option>
        @foreach($invoices as $invoice)
        <option value="{{ $invoice->id }}">{{ $invoice->number }} — {{ number_format($invoice->total, 0, '.', ' ') }} {{ $invoice->currency }}</option>
        @endforeach
    </x-select>

    <div class="grid grid-cols-2 gap-4">
        <x-input label="Дата продажи" type="date" wire:model="sold_at" :error="$errors->first('sold_at')" :required="true" />
        <x-select label="Валюта" wire:model="currency">
            <option value="UZS">UZS</option>
            <option value="USD">USD</option>
        </x-select>
    </div>

    {{-- Items --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">Позиции</label>
            <button type="button" wire:click="addItem"
                    class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Добавить позицию
            </button>
        </div>

        <div class="space-y-2">
            @foreach($items as $i => $item)
            <div class="grid grid-cols-12 gap-2 items-start p-3 bg-gray-50 rounded-lg border border-gray-200">
                <div class="col-span-4">
                    <select wire:model.live="items.{{ $i }}.product_id"
                            class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">— товар —</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error("items.{$i}.product_id")<p class="text-xs text-danger-600 mt-0.5">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <input type="number" wire:model.live="items.{{ $i }}.quantity"
                           min="0.001" step="0.001" placeholder="Кол."
                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error("items.{$i}.quantity")<p class="text-xs text-danger-600 mt-0.5">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-3">
                    <input type="number" wire:model.live="items.{{ $i }}.unit_price"
                           min="0" step="1" placeholder="Цена"
                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error("items.{$i}.unit_price")<p class="text-xs text-danger-600 mt-0.5">{{ $message }}</p>@enderror
                </div>
                <div class="col-span-2">
                    <input type="number" wire:model.live="items.{{ $i }}.discount_percent"
                           min="0" max="100" placeholder="Скидка%"
                           class="w-full rounded-md border border-gray-300 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="col-span-1 flex justify-center pt-1">
                    <button type="button" wire:click="removeItem({{ $i }})"
                            class="p-1 text-gray-400 hover:text-danger-600 hover:bg-danger-50 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            @endforeach

            @if(empty($items))
            <div class="px-4 py-6 border-2 border-dashed border-gray-200 rounded-lg text-center">
                <p class="text-sm text-gray-400">Нажмите «Добавить позицию»</p>
            </div>
            @endif
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Примечание</label>
        <textarea wire:model="notes" rows="3" placeholder="Дополнительная информация..."
                  class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
    </div>

</form>
