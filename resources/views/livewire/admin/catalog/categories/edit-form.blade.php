<form wire:submit="save" class="space-y-4">
    <x-input label="Название (RU)" wire:model="name_ru" :error="$errors->first('name_ru')" required />
    <x-input label="Название (UZ)" wire:model="name_uz" />
    <x-input label="Slug" wire:model="slug" :error="$errors->first('slug')" required hint="Генерируется из названия" />
    <x-select label="Родительская категория" wire:model="parent_id">
        <option value="">— корневая —</option>
        @foreach($parents as $p)
        <option value="{{ $p->id }}">{{ $p->name_ru }}</option>
        @endforeach
    </x-select>
    <div class="grid grid-cols-2 gap-4">
        <x-input label="Иконка" wire:model="icon" placeholder="📦" hint="Эмодзи" />
        <x-input label="Порядок" type="number" wire:model="sort_order" min="0" />
    </div>
    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
        <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        Активна
    </label>
    <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
        <x-button type="button" variant="secondary" wire:click="$parent.closeForm">Отмена</x-button>
        <x-button type="submit" wire:loading.attr="disabled">
            <span wire:loading.remove>Сохранить</span><span wire:loading>Сохранение...</span>
        </x-button>
    </div>
</form>
