<?php

namespace App\Livewire\Portal\Invoices;

use App\Models\Invoice\Invoice;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    public function mount(Invoice $invoice): void
    {
        $customer = auth()->user()->customers()->first();
        abort_unless($customer && $invoice->customer_id === $customer->id, 403);
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
