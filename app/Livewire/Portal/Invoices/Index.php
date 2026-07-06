<?php

namespace App\Livewire\Portal\Invoices;

use App\Models\Invoice\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $customer = auth()->user()->customers()->first();

        $query = Invoice::where('customer_id', $customer?->id)
            ->with('manager')
            ->latest();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.portal.invoices.index', [
            'invoices' => $query->paginate(15),
        ]);
    }
}
