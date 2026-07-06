<?php

namespace App\Livewire\Portal\Quotes;

use App\Models\Quote\Quote;
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

        $query = Quote::where('customer_id', $customer?->id)
            ->with('manager')
            ->latest();

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.portal.quotes.index', [
            'quotes' => $query->paginate(15),
        ]);
    }
}
