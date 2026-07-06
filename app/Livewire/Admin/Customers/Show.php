<?php

namespace App\Livewire\Admin\Customers;

use App\Models\BusinessType;
use App\Models\Customer\Bank;
use App\Models\Customer\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class Show extends Component
{
    public Customer $customer;
    public string $activeTab = 'contacts';

    // ── Inline editable fields ──────────────────────────────────────────────
    public string $editName = '';
    public string $editLegalName = '';
    public string $editInn = '';
    public string $editOked = '';
    public string $editPhone = '';
    public string $editEmail = '';
    public string $editWebsite = '';
    public string $editRegion = '';
    public string $editCity = '';
    public string $editAddress = '';
    public string $editNotes = '';
    public string $editSegment = '';
    public string $editStatus = '';
    public string $editBankAccount = '';
    public string $editCreditLimit = '';
    public string $editPaymentTermsDays = '';
    public ?int $editBusinessTypeId = null;
    public ?int $editBankId = null;

    // ── Create quote ────────────────────────────────────────────────────────
    public bool $showCreateQuoteForm = false;

    public function closeForm(): void
    {
        $this->showCreateQuoteForm = false;
    }

    #[\Livewire\Attributes\On('quote-saved')]
    public function onQuoteSaved(): void
    {
        $this->showCreateQuoteForm = false;
        $this->customer->refresh()->load(self::LOAD);
    }

    // ── User management ─────────────────────────────────────────────────────
    public bool $showAddUserModal = false;
    public string $addUserEmail = '';
    public string $addUserRole = 'client-user';

    // ── Relations to eager-load everywhere ──────────────────────────────────
    private const LOAD = [
        'businessType',
        'bank',
        'contacts',
        'users',
        'leads',
        'quotes',
        'invoices',
    ];

    public function mount(Customer $customer): void
    {
        $this->authorize('view', $customer);
        $this->customer = $customer->load(self::LOAD);
        $this->syncEditFields();
    }

    public function setTab(string $tab): void
    {
        $allowedTabs = ['contacts', 'leads', 'quotes', 'invoices', 'users'];
        if (in_array($tab, $allowedTabs)) {
            $this->activeTab = $tab;
        }
    }

    // ── Inline field saving ─────────────────────────────────────────────────

    public function saveField(string $field): void
    {
        $this->authorize('update', $this->customer);

        $rules = $this->fieldRules();

        if (! array_key_exists($field, $rules)) {
            return;
        }

        $propMap = [
            'name'                => 'editName',
            'legal_name'          => 'editLegalName',
            'inn'                 => 'editInn',
            'oked'                => 'editOked',
            'phone'               => 'editPhone',
            'email'               => 'editEmail',
            'website'             => 'editWebsite',
            'region'              => 'editRegion',
            'city'                => 'editCity',
            'address'             => 'editAddress',
            'notes'               => 'editNotes',
            'segment'             => 'editSegment',
            'status'              => 'editStatus',
            'bank_account'        => 'editBankAccount',
            'credit_limit'        => 'editCreditLimit',
            'payment_terms_days'  => 'editPaymentTermsDays',
            'business_type_id'    => 'editBusinessTypeId',
            'bank_id'             => 'editBankId',
        ];

        $prop  = $propMap[$field];
        $value = $this->{$prop};

        Validator::make(
            [$field => $value],
            [$field => $rules[$field]]
        )->validate();

        $this->customer->update([$field => ($value !== '' && $value !== null) ? $value : null]);

        $this->customer->refresh()->load(self::LOAD);
        $this->syncEditFields();

        $this->dispatch('field-saved');
    }

    private function fieldRules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'legal_name'         => 'nullable|string|max:255',
            'inn'                => 'nullable|string|max:20',
            'oked'               => 'nullable|string|max:20',
            'phone'              => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:255',
            'website'            => 'nullable|url|max:255',
            'region'             => 'nullable|string|max:100',
            'city'               => 'nullable|string|max:100',
            'address'            => 'nullable|string|max:1000',
            'notes'              => 'nullable|string|max:5000',
            'segment'            => 'nullable|in:A,B,C',
            'status'             => 'required|in:active,vip,inactive,blocked',
            'bank_account'       => 'nullable|string|max:20',
            'credit_limit'       => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'business_type_id'   => 'nullable|exists:business_types,id',
            'bank_id'            => 'nullable|exists:banks,id',
        ];
    }

    private function syncEditFields(): void
    {
        $c = $this->customer;

        $this->editName              = $c->name ?? '';
        $this->editLegalName         = $c->legal_name ?? '';
        $this->editInn               = $c->inn ?? '';
        $this->editOked              = $c->oked ?? '';
        $this->editPhone             = $c->phone ?? '';
        $this->editEmail             = $c->email ?? '';
        $this->editWebsite           = $c->website ?? '';
        $this->editRegion            = $c->region ?? '';
        $this->editCity              = $c->city ?? '';
        $this->editAddress           = $c->address ?? '';
        $this->editNotes             = $c->notes ?? '';
        $this->editSegment           = $c->segment ?? '';
        $this->editStatus            = $c->status ?? '';
        $this->editBankAccount       = $c->bank_account ?? '';
        $this->editCreditLimit       = $c->credit_limit !== null ? (string) $c->credit_limit : '';
        $this->editPaymentTermsDays  = $c->payment_terms_days !== null ? (string) $c->payment_terms_days : '';
        $this->editBusinessTypeId    = $c->business_type_id;
        $this->editBankId            = $c->bank_id;
    }

    // ── User management ─────────────────────────────────────────────────────

    public function attachUser(): void
    {
        $this->authorize('update', $this->customer);

        $this->validate([
            'addUserEmail' => 'required|email|exists:users,email',
            'addUserRole'  => 'required|in:client-admin,client-user',
        ]);

        $user = User::where('email', $this->addUserEmail)->first();

        if ($this->customer->users()->where('users.id', $user->id)->exists()) {
            $this->addError('addUserEmail', 'Пользователь уже привязан к этому клиенту.');
            return;
        }

        if (! $user->hasAnyRole(['client-admin', 'client-user'])) {
            $user->assignRole($this->addUserRole);
        }

        $this->customer->users()->attach($user->id, ['role' => $this->addUserRole]);

        $this->showAddUserModal = false;
        $this->addUserEmail     = '';

        $this->customer->refresh()->load(self::LOAD);
        $this->syncEditFields();
    }

    public function detachUser(int $userId): void
    {
        $this->authorize('update', $this->customer);

        $this->customer->users()->detach($userId);

        $this->customer->refresh()->load(self::LOAD);
        $this->syncEditFields();
    }

    // ── Render ──────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.customers.show', [
            'businessTypes' => BusinessType::active()->get(),
            'banks'         => Bank::active()->get(),
            'regions'       => [
                'Ташкент',
                'Самарканд',
                'Бухара',
                'Андижан',
                'Фергана',
                'Наманган',
            ],
        ])->layout('layouts.admin', ['mainClass' => '']);
    }
}
