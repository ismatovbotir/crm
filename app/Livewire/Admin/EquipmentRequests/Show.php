<?php

namespace App\Livewire\Admin\EquipmentRequests;

use App\Models\Quote\Quote;
use App\Models\Support\EquipmentRequest;
use App\Models\Support\EquipmentRequestComment;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public EquipmentRequest $request;

    public string $notes = '';
    public ?int $assignManagerId = null;
    public string $commentBody = '';
    public bool $isInternal = false;

    public function mount(EquipmentRequest $equipmentRequest): void
    {
        abort_unless(auth()->user()->can('equipment-requests.view'), 403);
        $this->request = $equipmentRequest->load(['customer', 'manager', 'quote', 'comments.user']);
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

    public function addComment(): void
    {
        $this->validate(['commentBody' => 'required|string|max:10000']);

        $this->request->comments()->create([
            'user_id'     => auth()->id(),
            'body'        => $this->commentBody,
            'is_internal' => $this->isInternal,
        ]);

        $this->commentBody = '';
        $this->isInternal = false;
        $this->request->load('comments.user');
    }

    public function convertToQuote(): void
    {
        if ($this->request->quote()->exists()) {
            $this->redirect(route('admin.quotes.edit', $this->request->quote()->first()), navigate: true);

            return;
        }

        $year  = now()->year;
        $count = Quote::whereYear('created_at', $year)->withTrashed()->count() + 1;
        $number = 'КП-' . $year . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);

        $summaryParts = ['Заявка на оборудование: ' . $this->request->subject];

        if ($this->request->description) {
            $summaryParts[] = $this->request->description;
        }

        $summaryParts[] = 'Бюджет клиента: ' . ($this->request->budget
            ? number_format((float) $this->request->budget, 0, '.', ' ') . ' UZS'
            : 'не указан');

        $summaryParts[] = 'Желаемый срок: ' . ($this->request->needed_by?->format('d.m.Y') ?? 'не указан');

        $quote = Quote::create([
            'number'               => $number,
            'customer_id'          => $this->request->customer_id,
            'manager_id'           => $this->request->manager_id ?: auth()->id(),
            'equipment_request_id' => $this->request->id,
            'currency'             => 'UZS',
            'exchange_rate'        => 1,
            'issue_date'           => now(),
            'status'               => 'draft',
            'notes'                => implode("\n\n", $summaryParts),
        ]);

        $this->request->update(['status' => 'quoted']);
        $this->request->refresh();

        session()->flash('success', 'КП создано на основе заявки.');

        $this->redirect(route('admin.quotes.edit', $quote), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.equipment-requests.show');
    }
}
