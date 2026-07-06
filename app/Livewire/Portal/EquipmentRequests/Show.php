<?php

namespace App\Livewire\Portal\EquipmentRequests;

use App\Models\Support\EquipmentRequest;
use App\Models\Support\EquipmentRequestComment;
use Livewire\Component;

class Show extends Component
{
    public EquipmentRequest $equipmentRequest;
    public string $commentBody = '';

    public function mount(EquipmentRequest $equipmentRequest): void
    {
        // Check that this request belongs to ANY of the user's companies (not just the first one).
        abort_unless(
            auth()->user()->customers()->where('customers.id', $equipmentRequest->customer_id)->exists(),
            403
        );

        $this->equipmentRequest = $equipmentRequest->load('quote');
    }

    protected function rules(): array
    {
        return ['commentBody' => 'required|string|max:5000'];
    }

    public function addComment(): void
    {
        $this->validate();

        EquipmentRequestComment::create([
            'equipment_request_id' => $this->equipmentRequest->id,
            'user_id'               => auth()->id(),
            'body'                  => $this->commentBody,
            'is_internal'           => false,
        ]);

        $this->commentBody = '';
        $this->equipmentRequest->refresh();
    }

    public function render()
    {
        return view('livewire.portal.equipment-requests.show', [
            'comments' => $this->equipmentRequest->publicComments()->with('user')->get(),
        ])
            ->layout('layouts.portal')
            ->section('content');
    }
}
