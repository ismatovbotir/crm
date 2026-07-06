<div
    x-data="{}"
    x-init="
        const sn = new URLSearchParams(window.location.search).get('serial_number');
        if (sn) {
            $wire.set('serial_number', sn);
            $wire.lookupSerial();
        }
    ">
    <form wire:submit="save" class="space-y-4">

        <x-select label="Категория" wire:model="category_id" :error="$errors->first('category_id')">
            <option value="">— выберите —</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </x-select>

        <x-select label="Приоритет" wire:model="priority" :error="$errors->first('priority')" required>
            <option value="low">Низкий</option>
            <option value="medium">Средний</option>
            <option value="high">Высокий</option>
            <option value="critical">Критичный</option>
        </x-select>

        <x-input
            label="Тема"
            wire:model="subject"
            :error="$errors->first('subject')"
            required
            placeholder="Кратко опишите проблему"
        />

        {{-- Serial number --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Серийный номер устройства <span class="text-gray-400 font-normal">(необязательно)</span>
            </label>
            <div class="flex gap-2">
                <input type="text"
                       wire:model="serial_number"
                       placeholder="Например: SN12345678"
                       class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                <button type="button" wire:click="lookupSerial"
                        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                    Проверить
                </button>
            </div>

            @if($foundSerial)
            <div class="mt-2 flex items-center gap-2 px-3 py-2 bg-success-50 border border-success-200 rounded-lg">
                <svg class="w-4 h-4 text-success-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-medium text-success-800">{{ $foundSerial['display_name'] }}</span>
            </div>
            @endif

            @if($showExternalForm)
            <div class="mt-2 p-4 bg-blue-50 border border-blue-200 rounded-lg space-y-3">
                <p class="text-sm font-medium text-blue-800">Устройство не найдено. Укажите данные:</p>
                <div class="grid grid-cols-2 gap-3">
                    <x-input label="Бренд" wire:model="ext_brand" placeholder="Epson..." />
                    <x-input label="Модель" wire:model="ext_model" placeholder="TM-T88VI..." />
                </div>
            </div>
            @endif

            @error('serial_number')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Описание
            </label>
            <textarea
                wire:model="description"
                rows="5"
                placeholder="Подробное описание — что случилось, шаги для воспроизведения..."
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                       focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                       transition-colors resize-none"
            ></textarea>
            @error('description')
                <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
            <x-button
                type="button"
                variant="secondary"
                wire:click="$parent.closeForm">
                Отмена
            </x-button>
            <x-button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">Создать тикет</span>
                <span wire:loading wire:target="save">Создание...</span>
            </x-button>
        </div>

    </form>
</div>
