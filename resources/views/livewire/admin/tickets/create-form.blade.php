<form id="ticket-create-form" wire:submit="save" class="space-y-4">
    <x-select label="Клиент" wire:model="customer_id">
        <option value="">— без клиента —</option>
        @foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
    </x-select>
    <div class="grid grid-cols-2 gap-4">
        <x-select label="Категория" wire:model="category_id">
            <option value="">— выберите —</option>
            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
        </x-select>
        <x-select label="Приоритет" wire:model="priority" required>
            <option value="low">Низкий</option>
            <option value="medium">Средний</option>
            <option value="high">Высокий</option>
            <option value="critical">Критичный</option>
        </x-select>
    </div>
    <x-input label="Тема" wire:model="subject" :error="$errors->first('subject')" required placeholder="Кратко опишите проблему" />
    {{-- Serial number lookup --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Серийный номер устройства <span class="text-gray-400 font-normal">(опционально)</span>
        </label>
        <div class="flex gap-2">
            <input type="text"
                   wire:model="serial_number"
                   placeholder="Введите серийный номер..."
                   class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button type="button" wire:click="lookupSerial"
                    class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors whitespace-nowrap">
                Найти
            </button>
        </div>

        {{-- Found: show device info --}}
        @if($foundSerial)
        <div class="mt-2 flex items-center gap-2 px-3 py-2 bg-success-50 border border-success-200 rounded-lg">
            <svg class="w-4 h-4 text-success-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <div class="text-sm">
                <span class="font-medium text-success-800">{{ $foundSerial['display_name'] }}</span>
                @if($foundSerial['owner_name'])
                <span class="text-success-600"> · {{ $foundSerial['owner_name'] }}</span>
                @endif
            </div>
        </div>
        @endif

        {{-- Not found: external equipment form --}}
        @if($showExternalForm)
        <div class="mt-2 p-4 bg-warning-50 border border-warning-200 rounded-lg space-y-3">
            <p class="text-sm font-medium text-warning-800">
                Серийный номер не найден в системе. Зарегистрировать как внешнее оборудование?
            </p>
            <div class="grid grid-cols-2 gap-3">
                <x-input label="Бренд" wire:model="ext_brand" placeholder="Epson, Zebra..." />
                <x-input label="Модель" wire:model="ext_model" placeholder="TM-T88VI..." />
            </div>
            <p class="text-xs text-warning-600">При сохранении тикета устройство будет зарегистрировано в системе со статусом "В ремонте"</p>
        </div>
        @endif

        @error('serial_number')<p class="mt-1 text-xs text-danger-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
        <textarea wire:model="description" rows="4" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500" placeholder="Подробное описание..."></textarea>
    </div>
</form>
