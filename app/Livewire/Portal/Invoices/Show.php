<?php

namespace App\Livewire\Portal\Invoices;

use App\Models\Invoice\Invoice;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        // Check that this invoice belongs to ANY of the user's companies (not just the first one).
        abort_unless(
            auth()->user()->customers()->where('customers.id', $invoice->customer_id)->exists(),
            403
        );

        $this->invoice = $invoice;
    }

    public function render()
    {
        return view('livewire.portal.invoices.show', [
            'items'    => $this->invoice->items()->get(),
            'payments' => $this->invoice->payments()->orderBy('paid_at')->get(),
        ]);
    }
}
