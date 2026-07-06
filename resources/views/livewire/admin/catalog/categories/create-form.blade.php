<form id="category-create-form" wire:submit="save" class="space-y-4">
    <x-input label="Название (RU)" wire:model="name_ru" :error="$errors->first('name_ru')" required placeholder="POS-системы" />
    <x-input label="Название (UZ)" wire:model="name_uz" placeholder="POS-tizimlar" />
    <x-input label="Slug" wire:model="slug" :error="$errors->first('slug')" required hint="Генерируется автоматически из названия" />
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
</form>
