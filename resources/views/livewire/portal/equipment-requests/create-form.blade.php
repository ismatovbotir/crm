<div>
    <div class="mb-6">
        <h1 class="text-xl font-bold text-gray-900">Новая заявка на оборудование</h1>
        <p class="text-sm text-gray-500 mt-0.5">Опишите, какое оборудование вам нужно — менеджер подготовит коммерческое предложение</p>
    </div>

    <x-card>
        <form wire:submit="save" class="space-y-4">

            <x-input
                label="Что нужно"
                wire:model="subject"
                :error="$errors->first('subject')"
                required
                placeholder="Например: 3 кассовых аппарата с фискальным модулем"
            />

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Описание <span class="text-gray-400 font-normal">(необязательно)</span>
                </label>
                <textarea
                    wire:model="description"
                    rows="5"
                    placeholder="Тех. требования, количество, особенности..."
                    class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-900 placeholder-gray-400
                           focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent
                           transition-colors resize-none"
                ></textarea>
                @error('description')
                    <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-input
                    label="Бюджет"
                    type="number"
                    step="0.01"
                    wire:model="budget"
                    :error="$errors->first('budget')"
                    placeholder="Ориентировочно, в UZS"
                />

                <x-input
                    label="Нужно к дате"
                    type="date"
                    wire:model="needed_by"
                    :error="$errors->first('needed_by')"
                />
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('portal.equipment-requests.index') }}">
                    <x-button type="button" variant="secondary">Отмена</x-button>
                </a>
                <x-button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">Отправить заявку</span>
                    <span wire:loading wire:target="save">Отправка...</span>
                </x-button>
            </div>

        </form>
    </x-card>
</div>
