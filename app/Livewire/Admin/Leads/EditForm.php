<?php

namespace App\Livewire\Admin\Leads;

use App\Models\BusinessType;
use App\Models\Lead\Lead;
use App\Models\Lead\LeadSource;
use App\Models\User;
use Livewire\Component;

class EditForm extends Component
{
    public Lead $lead;
    public int $leadId;

    public string $name = '';
    public string $company = '';
    public string $phone = '';
    public string $email = '';
    public string $status = 'new';
    public ?int $source_id = null;
    public ?int $manager_id = null;
    public ?int $business_type_id = null;
    public string $region = '';
    public ?int $score = null;
    public ?string $budget = null;
    public ?string $won_amount = null;
    public ?string $lost_reason = null;
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'company'          => 'nullable|string|max:255',
            'phone'            => 'required|string|max:20',
            'email'            => 'nullable|email|max:255',
            'status'           => 'required|in:new,qualified,contacted,in_negotiation,won,lost',
            'source_id'        => 'nullable|exists:lead_sources,id',
            'manager_id'       => 'required|exists:users,id',
            'business_type_id' => 'nullable|exists:business_types,id',
            'region'           => 'nullable|string|max:100',
            'score'            => 'nullable|integer|min:1|max:10',
            'budget'           => 'nullable|numeric|min:0',
            'won_amount'       => 'nullable|numeric|min:0',
            'lost_reason'      => 'nullable|string|max:255',
            'notes'            => 'nullable|string|max:5000',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'       => 'Укажите имя контакта.',
            'phone.required'      => 'Укажите телефон.',
            'email.email'         => 'Некорректный email.',
            'score.min'           => 'Оценка от 1 до 10.',
            'score.max'           => 'Оценка от 1 до 10.',
            'manager_id.required' => 'Выберите менеджера.',
        ];
    }

    public function mount(int $leadId): void
    {
        $this->lead = Lead::findOrFail($leadId);
        $this->authorize('update', $this->lead);

        abort_if($this->lead->status === 'client', 403, 'Лид уже конвертирован в клиента и недоступен для редактирования.');

        $this->fill($this->lead->only([
            'name', 'company', 'phone', 'email', 'status',
            'source_id', 'manager_id', 'business_type_id',
            'region', 'score', 'budget', 'won_amount', 'lost_reason', 'notes',
        ]));
    }

    public function save(): void
    {
        $this->authorize('update', $this->lead);
        $data = $this->validate();

        $data['budget'] = $data['budget'] === '' ? null : $data['budget'];
        $data['won_amount'] = $data['won_amount'] === '' ? null : $data['won_amount'];

        $this->lead->update($data);

        session()->flash('success', 'Лид обновлён.');
        $this->dispatch('lead-saved');
    }

    public function render()
    {
        return view('livewire.admin.leads.edit-form', [
            'sources'       => LeadSource::active()->get(),
            'managers'      => User::role(['sales-manager', 'sales-director'])->active()->get(),
            'businessTypes' => BusinessType::active()->get(),
            'statuses'      => ['new', 'qualified', 'contacted', 'in_negotiation', 'won', 'lost'],
            'regions'       => ['Ташкент', 'Самарканд', 'Бухара', 'Андижан', 'Фергана', 'Наманган', 'Другой'],
        ]);
    }
}
