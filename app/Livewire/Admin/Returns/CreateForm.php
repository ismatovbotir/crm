<?php

namespace App\Livewire\Admin\Returns;

use App\Models\Catalog\ProductSerial;
use App\Models\Customer\Customer;
use App\Models\Sell\ProductReturn;
use App\Models\Sell\Sell;
use App\Services\ReturnService;
use Livewire\Component;

class CreateForm extends Component
{
    // Pre-fill from query string
    public ?int $sell_id   = null;
    public ?int $ticket_id = null;

    // Form fields
    public ?int   $customer_id   = null;
    public ?int   $selected_sell = null;
    public string $reason        = 'warranty';
    public string $currency      = 'UZS';
    public string $refund_amount = '';
    public string $notes         = '';

    // Return lines: [product_id, name, sku, max_qty, quantity, is_serial, serial_number, unit_price]
    public array $lines = [];

    // Available sells for selected customer
    public array $availableSells = [];

    public function mount(): void
    {
        // Pre-fill sell_id and ticket_id from query string
        $this->sell_id   = request()->query('sell_id');
        $this->ticket_id = request()->query('ticket_id');

        if ($this->sell_id) {
            $sell = Sell::with(['items.product', 'invoice'])->find($this->sell_id);
            if ($sell) {
                $this->customer_id   = $sell->customer_id;
                $this->selected_sell = $sell->id;
                $this->currency      = $sell->currency;
                $this->loadLinesFromSell($sell);
            }
        }
    }

    public function updatedCustomerId(): void
    {
        $this->availableSells = Sell::where('customer_id', $this->customer_id)
            ->latest()
            ->get(['id', 'number', 'sold_at'])
            ->toArray();
        $this->lines         = [];
        $this->selected_sell = null;
    }

    public function updatedSelectedSell(): void
    {
        if (!$this->selected_sell) {
            $this->lines = [];
            return;
        }
        $sell = Sell::with(['items.product'])->find($this->selected_sell);
        if ($sell) {
            $this->loadLinesFromSell($sell);
        }
    }

    private function loadLinesFromSell(Sell $sell): void
    {
        $this->lines = $sell->items->map(fn ($item) => [
            'checked'       => true,
            'product_id'    => $item->product_id,
            'name'          => $item->product?->name ?? 'Товар #' . $item->product_id,
            'sku'           => $item->product?->sku ?? '',
            'max_qty'       => (float) $item->quantity,
            'quantity'      => (float) $item->quantity,
            'is_serial'     => (bool) ($item->product?->is_serial ?? false),
            'serial_number' => '',
            'unit_price'    => (float) $item->unit_price,
        ])->toArray();

        // Auto-calc refund amount
        $this->recalcRefund();
    }

    public function updatedLines(): void
    {
        $this->recalcRefund();
    }

    private function recalcRefund(): void
    {
        $total = collect($this->lines)
            ->filter(fn ($l) => !empty($l['checked']))
            ->sum(fn ($l) => round((float) ($l['quantity'] ?? 0) * (float) ($l['unit_price'] ?? 0), 2));
        $this->refund_amount = (string) $total;
    }

    protected function rules(): array
    {
        return [
            'customer_id'   => 'required|exists:customers,id',
            'selected_sell' => 'required|exists:sells,id',
            'reason'        => 'required|in:warranty,defect,changed_mind,other',
            'currency'      => 'required|in:UZS,USD',
            'refund_amount' => 'required|numeric|min:0',
            'notes'         => 'nullable|string|max:2000',
            'lines'         => 'required|array|min:1',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $checkedLines = collect($this->lines)->filter(fn ($l) => !empty($l['checked']));
        if ($checkedLines->isEmpty()) {
            $this->addError('lines', 'Выберите хотя бы одну позицию для возврата.');
            return;
        }

        // Validate serial numbers for serial products
        foreach ($checkedLines as $idx => $line) {
            if ($line['is_serial'] && empty(trim($line['serial_number'] ?? ''))) {
                $this->addError("lines.{$idx}.serial_number", 'Введите серийный номер для: ' . $line['name']);
                return;
            }
            if ($line['is_serial'] && !empty($line['serial_number'])) {
                $serial = ProductSerial::where('serial_number', trim($line['serial_number']))
                    ->where('product_id', $line['product_id'])
                    ->where('current_status', 'sold')
                    ->where('current_owner_id', $this->customer_id)
                    ->first();
                if (!$serial) {
                    $this->addError(
                        "lines.{$idx}.serial_number",
                        'Серийный номер не найден или не принадлежит клиенту: ' . trim($line['serial_number'])
                    );
                    return;
                }
            }
        }

        $sell = Sell::find($this->selected_sell);

        $productReturn = ProductReturn::create([
            'number'        => app(ReturnService::class)->generateNumber(),
            'sell_id'       => $this->selected_sell,
            'invoice_id'    => $sell?->invoice_id,
            'customer_id'   => $this->customer_id,
            'manager_id'    => auth()->id(),
            'ticket_id'     => $this->ticket_id ?: null,
            'reason'        => $this->reason,
            'status'        => 'draft',
            'refund_amount' => $this->refund_amount,
            'currency'      => $this->currency,
            'notes'         => $this->notes ?: null,
        ]);

        foreach ($checkedLines as $line) {
            $serialId = null;
            if ($line['is_serial'] && !empty($line['serial_number'])) {
                $serial = ProductSerial::where('serial_number', trim($line['serial_number']))
                    ->where('product_id', $line['product_id'])
                    ->first();
                $serialId = $serial?->id;
            }

            $qty = $line['is_serial'] ? 1 : (float) $line['quantity'];
            $productReturn->items()->create([
                'product_id' => $line['product_id'],
                'name'       => $line['name'],
                'sku'        => $line['sku'] ?: null,
                'quantity'   => $qty,
                'serial_id'  => $serialId,
                'unit_price' => (float) $line['unit_price'],
                'total'      => round($qty * (float) $line['unit_price'], 2),
            ]);
        }

        session()->flash('success', 'Возврат ' . $productReturn->number . ' создан.');
        $this->redirect(route('admin.returns.show', $productReturn));
    }

    public function render()
    {
        return view('livewire.admin.returns.create-form', [
            'customers' => Customer::active()->orderBy('name')->limit(200)->get(),
        ])->layout('layouts.admin')->section('content');
    }
}
