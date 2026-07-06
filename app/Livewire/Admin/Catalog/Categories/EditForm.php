<?php

namespace App\Livewire\Admin\Catalog\Categories;

use App\Models\Catalog\Category;
use Illuminate\Support\Str;
use Livewire\Component;

class EditForm extends Component
{
    public ?Category $category = null;
    public int $categoryId;

    public string $name_ru = '';
    public string $name_uz = '';
    public string $slug = '';
    public ?int $parent_id = null;
    public string $icon = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public function mount(int $categoryId): void
    {
        $this->categoryId = $categoryId;
        $this->category = Category::findOrFail($categoryId);
        $this->authorize('update', $this->category);
        $this->fill($this->category->only([
            'name_ru', 'name_uz', 'slug', 'parent_id', 'icon',
            'sort_order', 'is_active',
        ]));
    }

    protected function rules(): array
    {
        return [
            'name_ru'    => 'required|string|max:255',
            'name_uz'    => 'nullable|string|max:255',
            'slug'       => "required|string|max:255|unique:categories,slug,{$this->categoryId}",
            'parent_id'  => 'nullable|exists:categories,id',
            'icon'       => 'nullable|string|max:100',
            'sort_order' => 'integer|min:0',
            'is_active'  => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'name_ru.required' => 'Название (RU) обязательно.',
            'slug.required'    => 'Slug обязателен.',
            'slug.unique'      => 'Такой slug уже занят.',
        ];
    }

    public function updatedNameRu(string $value): void
    {
        $this->slug = Str::slug($value);
    }

    public function save(): void
    {
        $this->authorize('update', $this->category);
        $data = $this->validate();

        $this->category->update([
            'name_ru'    => $data['name_ru'],
            'name_uz'    => $data['name_uz'] ?: null,
            'slug'       => $data['slug'],
            'parent_id'  => $data['parent_id'],
            'icon'       => $data['icon'] ?: null,
            'sort_order' => $data['sort_order'],
            'is_active'  => $data['is_active'],
        ]);

        session()->flash('success', 'Категория обновлена.');
        $this->dispatch('category-saved');
    }

    public function render()
    {
        return view('livewire.admin.catalog.categories.edit-form', [
            'parents' => Category::whereNull('parent_id')
                ->where('is_active', true)
                ->where('id', '!=', $this->categoryId)
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
