<?php

namespace App\Livewire\Admin\Catalog\Groups;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductGroup;
use Livewire\Component;

class Index extends Component
{
    // ── Inline editing ───────────────────────────────────────────────────

    public ?int $editingId = null;
    public string $editNameRu = '';
    public string $editNameUz = '';
    public string $editDescription = '';
    public string $editColor = 'gray';
    public int $editSortOrder = 0;
    public bool $editIsActive = true;

    // ── Create form ──────────────────────────────────────────────────────

    public bool $showCreateForm = false;
    public string $newNameRu = '';
    public string $newNameUz = '';
    public string $newDescription = '';
    public string $newColor = 'gray';
    public int $newSortOrder = 0;

    // ── Validation rules ─────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'editNameRu'    => 'required|string|max:100',
            'editNameUz'    => 'nullable|string|max:100',
            'editDescription' => 'nullable|string|max:1000',
            'editColor'     => 'required|in:gray,blue,green,orange,red,purple',
            'editSortOrder' => 'integer|min:0|max:999',
        ];
    }

    protected function createRules(): array
    {
        return [
            'newNameRu'      => 'required|string|max:100',
            'newNameUz'      => 'nullable|string|max:100',
            'newDescription' => 'nullable|string|max:1000',
            'newColor'       => 'required|in:gray,blue,green,orange,red,purple',
            'newSortOrder'   => 'integer|min:0|max:999',
        ];
    }

    protected function messages(): array
    {
        return [
            'editNameRu.required'  => 'Название (RU) обязательно.',
            'editNameRu.max'       => 'Не более 100 символов.',
            'editColor.in'         => 'Выберите допустимый цвет.',
            'editSortOrder.integer' => 'Порядок сортировки должен быть числом.',
            'newNameRu.required'   => 'Название (RU) обязательно.',
            'newNameRu.max'        => 'Не более 100 символов.',
            'newColor.in'          => 'Выберите допустимый цвет.',
            'newSortOrder.integer' => 'Порядок сортировки должен быть числом.',
        ];
    }

    // ── Lifecycle ────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    // ── Edit actions ─────────────────────────────────────────────────────

    public function startEdit(int $id): void
    {
        $group = ProductGroup::findOrFail($id);

        $this->editingId       = $id;
        $this->editNameRu      = $group->name_ru;
        $this->editNameUz      = $group->name_uz ?? '';
        $this->editDescription = $group->description ?? '';
        $this->editColor       = $group->color;
        $this->editSortOrder   = $group->sort_order;
        $this->editIsActive    = $group->is_active;
    }

    public function saveEdit(): void
    {
        $this->authorize('update', Product::class);

        $this->validate($this->rules(), $this->messages());

        ProductGroup::findOrFail($this->editingId)->update([
            'name_ru'     => trim($this->editNameRu),
            'name_uz'     => $this->editNameUz ? trim($this->editNameUz) : null,
            'description' => $this->editDescription ? trim($this->editDescription) : null,
            'color'       => $this->editColor,
            'sort_order'  => $this->editSortOrder,
            'is_active'   => $this->editIsActive,
        ]);

        $this->editingId = null;
        session()->flash('success', 'Группа обновлена.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->resetValidation();
    }

    // ── Create actions ───────────────────────────────────────────────────

    public function create(): void
    {
        $this->authorize('create', Product::class);

        $this->validate($this->createRules(), $this->messages());

        ProductGroup::create([
            'name_ru'     => trim($this->newNameRu),
            'name_uz'     => $this->newNameUz ? trim($this->newNameUz) : null,
            'description' => $this->newDescription ? trim($this->newDescription) : null,
            'color'       => $this->newColor,
            'sort_order'  => $this->newSortOrder,
            'is_active'   => true,
        ]);

        $this->reset('newNameRu', 'newNameUz', 'newDescription', 'newSortOrder');
        $this->newColor = 'gray';
        $this->showCreateForm = false;

        session()->flash('success', 'Группа создана.');
    }

    public function openCreate(): void
    {
        $this->showCreateForm = true;
    }

    public function cancelCreate(): void
    {
        $this->reset('newNameRu', 'newNameUz', 'newDescription', 'newSortOrder');
        $this->newColor = 'gray';
        $this->showCreateForm = false;
        $this->resetValidation();
    }

    // ── Toggle ───────────────────────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        $this->authorize('update', Product::class);

        $group = ProductGroup::findOrFail($id);
        $group->update(['is_active' => ! $group->is_active]);
    }

    // ── Render ───────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.catalog.groups.index', [
            'groups' => ProductGroup::withCount('categories')->orderBy('sort_order')->get(),
        ])->layout('layouts.admin')->section('content');
    }
}
