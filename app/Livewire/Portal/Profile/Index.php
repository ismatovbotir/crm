<?php

namespace App\Livewire\Portal\Profile;

use App\Models\Customer\Customer;
use Livewire\Component;

class Index extends Component
{
    public ?Customer $customer = null;

    public function mount(): void
    {
        $this->customer = auth()->user()->customers()->with(['contacts', 'businessType'])->first();
    }

    public function render()
    {
        return view('livewire.portal.profile.index', [
            'customer' => $this->customer,
            'contacts' => $this->customer?->contacts ?? collect(),
            'users'    => $this->customer?->users ?? collect(),
        ]);
    }
}
