<?php

namespace App\Livewire\Portal\EquipmentRequests;

use App\Models\Support\EquipmentRequest;
use Livewire\Component;

class CreateForm extends Component
{
    public string $subject = '';
    public string $description = '';
    public string $budget = '';
    public string $needed_by = '';

    protected function rules(): array
    {
        return [
            'subject'     => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'budget'      => 'nullable|numeric|min:0',
            'needed_by'   => 'nullable|date',
        ];
    }

    protected function messages(): array
    {
        return [
            'subject.required' => 'Укажите, какое оборудование вам нужно.',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();
        $customer = auth()->user()->customers()->first();

        EquipmentRequest::create([
            'customer_id' => $customer?->id,
            'manager_id'  => null,
            'subject'     => $data['subject'],
            'description' => $data['description'] ?: null,
            'budget'      => $data['budget'] !== '' ? $data['budget'] : null,
            'needed_by'   => $data['needed_by'] ?: null,
            'status'      => 'submitted',
        ]);

        session()->flash('success', 'Заявка отправлена. Менеджер свяжется с вами в ближайшее время.');
        $this->dispatch('equipment-request-saved');
        $this->reset(['subject', 'description', 'budget', 'needed_by']);

        $this->redirect(route('portal.equipment-requests.index'), navigate: false);
    }

    public function render()
    {
        return view('livewire.portal.equipment-requests.create-form')
            ->layout('layouts.portal')
            ->section('content');
    }
}
