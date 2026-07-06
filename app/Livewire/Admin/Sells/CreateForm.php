<?php

namespace App\Livewire\Admin\Sells;

use App\Models\Catalog\Product;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Services\SellService;
use Livewire\Component;

class CreateForm extends Component
{
    public ?int $invoice_id = null;
    public ?int $customer_id = null;
    public string $sold_at = '';
    public string $currency = 'UZS';
    public string $notes = '';
    public array $items = [];

    public function mount(?int $invoiceId = null): void
    {
        $this->invoice_id = $invoiceId;
        $this->sold_at = now()->format('Y-m-d');

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            $this->customer_id = $invoice?->customer_id;
            $this->currency = $invoice?->currency ?? 'UZS';
        }

        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'product_id'       => null,
            'quantity'         => 1,
            'unit_price'       => 0,
            'discount_percent' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function rules(): array
    {
        return [
            'customer_id'                => 'required|exists:customers,id',
            'sold_at'                    => 'required|date',
            'currency'                   => 'required|in:UZS,USD',
            'items'                      => 'required|array|min:1',
            'items.*.product_id'         => 'required|exists:products,id',
            'items.*.quantity'           => 'required|numeric|min:0.001',
            'items.*.unit_price'         => 'required|numeric|min:0',
            'items.*.discount_percent'   => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function save(SellService $service): void
    {
        $this->authorize('create', \App\Models\Sell\Sell::class);
        $this->validate();

        $subtotal = 0;
        $sellItems = [];

        foreach ($this->items as $i => $item) {
            $discount = $item['discount_percent'] ?? 0;
            $lineTotal = $item['unit_price'] * $item['quantity'] * (1 - $discount / 100);
            $subtotal += $lineTotal;

            $sellItems[] = [
                'product_id'       => $item['product_id'],
                'quantity'         => $item['quantity'],
                'unit_price'       => $item['unit_price'],
                'discount_percent' => $discount,
                'total'            => round($lineTotal, 2),
                'sort_order'       => $i,
            ];
        }

        $sell = \App\Models\Sell\Sell::create([
            'number'      => $service->generateNumber(),
            'invoice_id'  => $this->invoice_id ?: null,
            'customer_id' => $this->customer_id,
            'manager_id'  => auth()->id(),
            'status'      => 'draft',
            'sold_at'     => $this->sold_at,
            'currency'    => $this->currency,
            'subtotal'    => round($subtotal, 2),
            'total'       => round($subtotal, 2),
            'notes'       => $this->notes ?: null,
        ]);

        $sell->items()->createMany($sellItems);

        if ($sell->invoice_id) {
            $service->recalculateShipmentStatus($sell->invoice);
        }

        $this->dispatch('sell-created');
        session()->flash('success', "Отгрузка «{$sell->number}» создана.");
    }

    public function render()
    {
        return view('livewire.admin.sells.create-form', [
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'invoices'  => Invoice::whereIn('status', ['sent', 'partially_paid', 'paid'])
                ->orderByDesc('id')->get(['id', 'number', 'total', 'currency']),
            'products'  => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }
}
