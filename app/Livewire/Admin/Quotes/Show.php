<?php

namespace App\Livewire\Admin\Quotes;

use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Show extends Component
{
    public Quote $quote;

    public function mount(Quote $quote): void
    {
        $this->authorize('view', $quote);
        $this->quote = $quote->load(['customer', 'manager', 'items.product', 'versions.creator', 'invoice']);
    }

    public function changeStatus(string $status): void
    {
        $this->authorize('update', $this->quote);
        $allowed = ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired'];
        if (! in_array($status, $allowed)) return;

        $updates = ['status' => $status];
        if ($status === 'sent') $updates['sent_at'] = now();
        if ($status === 'viewed') $updates['viewed_at'] = now();

        $this->quote->update($updates);
        $this->quote->refresh()->load(['customer', 'manager', 'items.product', 'versions.creator', 'invoice']);
    }

    public function convertToInvoice(): void
    {
        $this->authorize('update', $this->quote);

        if ($this->quote->invoice) {
            session()->flash('error', 'Инвойс уже создан.');
            return;
        }
        if ($this->quote->status !== 'accepted') {
            session()->flash('error', 'КП должно быть принято клиентом.');
            return;
        }

        $paymentTermsDays = Customer::where('id', $this->quote->customer_id)->value('payment_terms_days') ?? 30;

        $invoice = DB::transaction(function () use ($paymentTermsDays) {
            // Use the auto-increment ID as the number to avoid race conditions on concurrent creates.
            $invoice = Invoice::create([
                'number'        => 'tmp-' . microtime(true),
                'quote_id'      => $this->quote->id,
                'customer_id'   => $this->quote->customer_id,
                'manager_id'    => $this->quote->manager_id,
                'currency'      => $this->quote->currency,
                'exchange_rate' => $this->quote->exchange_rate,
                'status'        => 'draft',
                'due_date'      => now()->addDays($paymentTermsDays)->toDateString(),
                'subtotal'      => $this->quote->subtotal,
                'tax_rate'      => $this->quote->vat_percent,
                'tax_amount'    => $this->quote->vat_amount,
                'total'         => $this->quote->total,
            ]);

            $invoice->update(['number' => 'INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT)]);

            foreach ($this->quote->items as $item) {
                $invoice->items()->create([
                    'product_id' => $item->product_id,
                    'name'       => $item->name,
                    'sku'        => $item->sku,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate'   => $this->quote->vat_percent,
                    'total'      => $item->total,
                    'sort_order' => $item->sort_order,
                ]);
            }

            return $invoice;
        });

        session()->flash('success', 'Инвойс ' . $invoice->number . ' создан.');
        $this->quote->refresh()->load(['customer', 'manager', 'items.product', 'versions.creator', 'invoice']);
    }

    public function render()
    {
        return view('livewire.admin.quotes.show');
    }
}
