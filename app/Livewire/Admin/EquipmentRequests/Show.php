<?php

namespace App\Livewire\Admin\EquipmentRequests;

use App\Models\Support\EquipmentRequest;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public EquipmentRequest $request;

    public string $notes = '';
    public ?int $assignManagerId = null;

    public function mount(EquipmentRequest $equipmentRequest): void
    {
        abort_unless(auth()->user()->can('equipment-requests.view'), 403);
        $this->request = $equipmentRequest->load(['customer', 'manager']);
        $this->notes = $equipmentRequest->notes ?? '';
        $this->assignManagerId = $equipmentRequest->manager_id;
    }

    #[Computed]
    public function managers()
    {
        return User::managers()->orderBy('name')->get(['id', 'name']);
    }

    public function changeStatus(string $status): void
    {
        $allowed = ['submitted', 'under_review', 'quoted', 'closed'];
        abort_unless(in_array($status, $allowed), 422);

        $this->request->update(['status' => $status]);
        $this->request->refresh();
        session()->flash('success', 'Статус обновлён.');
    }

    public function assignManager(): void
    {
        $this->request->update(['manager_id' => $this->assignManagerId ?: null]);
        $this->request->refresh()->load('manager');
        session()->flash('success', 'Менеджер назначен.');
    }

    public function saveNotes(): void
    {
        $this->validate(['notes' => 'nullable|string|max:5000']);
        $this->request->update(['notes' => $this->notes]);
        session()->flash('success', 'Заметки сохранены.');
    }

    public function convertToQuote(): string
    {
        return redirect()->route('admin.quotes.index', [
            'customer_id' => $this->request->customer_id,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.equipment-requests.show');
    }
}
