<?php

namespace App\Livewire\Admin\Customers;

use App\Models\BusinessType;
use App\Models\Customer\Bank;
use App\Models\Customer\Customer;
use Livewire\Component;

class EditForm extends Component
{
    public Customer $customer;
    public int $customerId;

    public string $name = '';
    public string $legal_name = '';
    public string $inn = '';
    public string $oked = '';
    public ?int $business_type_id = null;
    public string $segment = 'B';
    public string $status = 'active';
    public string $region = '';
    public string $city = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public string $website = '';
    public ?int $bank_id = null;
    public string $bank_account = '';
    public string $notes = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'legal_name'       => 'nullable|string|max:255',
            'inn'              => 'nullable|string|max:20|unique:customers,inn,' . $this->customer->id,
            'oked'             => 'nullable|string|max:20',
            'business_type_id' => 'nullable|exists:business_types,id',
            'segment'          => 'required|in:A,B,C',
            'status'           => 'required|in:active,vip,inactive,blocked',
            'region'           => 'nullable|string|max:100',
            'city'             => 'nullable|string|max:100',
            'address'          => 'nullable|string|max:1000',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255|unique:customers,email,' . $this->customer->id,
            'website'          => 'nullable|url|max:255',
            'bank_id'          => 'nullable|exists:banks,id',
            'bank_account'     => 'nullable|string|size:20',
            'notes'            => 'nullable|string|max:5000',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'     => 'Укажите название компании.',
            'inn.unique'        => 'Клиент с таким ИНН уже существует.',
            'email.email'       => 'Некорректный email.',
            'email.unique'      => 'Клиент с таким email уже существует.',
            'website.url'       => 'Некорректный адрес сайта.',
            'bank_account.size' => 'Расчётный счёт должен содержать ровно 20 символов.',
        ];
    }

    public function mount(int $customerId): void
    {
        $this->customer = Customer::findOrFail($customerId);
        $this->authorize('update', $this->customer);

        $this->fill($this->customer->only([
            'name', 'legal_name', 'inn', 'oked',
            'business_type_id', 'segment', 'status',
            'region', 'city', 'address',
            'phone', 'email', 'website',
            'bank_id', 'bank_account', 'notes',
        ]));

        // Convert nulls to empty strings for string fields
        foreach (['legal_name', 'inn', 'oked', 'region', 'city', 'address', 'phone', 'email', 'website', 'bank_account', 'notes'] as $field) {
            if ($this->$field === null) {
                $this->$field = '';
            }
        }
    }

    public function save(): void
    {
        $this->authorize('update', $this->customer);
        $data = $this->validate();

        // Convert empty strings to null for nullable fields
        foreach (['inn', 'oked', 'legal_name', 'phone', 'email', 'website', 'bank_account', 'address', 'city', 'region', 'notes'] as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        $this->customer->update($data);

        session()->flash('success', 'Клиент обновлён.');
        $this->dispatch('customer-saved');
    }

    public function render()
    {
        return view('livewire.admin.customers.edit-form', [
            'businessTypes' => BusinessType::active()->get(),
            'banks'         => Bank::active()->get(),
            'regions'       => ['Ташкент', 'Самарканд', 'Бухара', 'Андижан', 'Фергана', 'Наманган'],
        ]);
    }
}
