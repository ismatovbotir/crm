<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Invoice\Invoice;
use App\Models\Sell\Sell;
use App\Services\SellService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Show extends Component
{
    public Invoice $invoice;

    // Payment form
    public string $paymentAmount    = '';
    public string $paymentMethod    = 'bank_transfer';
    public string $paymentDate      = '';
    public string $paymentReference = '';
    public bool   $showPaymentForm  = false;

    // Shipment modal
    public bool   $showShipmentModal = false;
    public array  $shipmentLines     = [];
    public string $shipmentDate      = '';
    public string $shipmentNotes     = '';

    // Serials modal
    public bool  $showSerialsModal   = false;
    public int   $serialsLineIndex   = -1;
    public array $availableSerials   = [];

    public function mount(Invoice $invoice): void
    {
        $this->authorize('view', $invoice);

        $this->invoice     = $invoice->load(['customer', 'manager', 'quote', 'items.product', 'payments', 'sells']);
        $this->paymentDate = now()->format('Y-m-d');
    }

    // ── Payment ───────────────────────────────────────────────────────────────

    public function addPayment(): void
    {
        $this->validate([
            'paymentAmount'    => 'required|numeric|min:0.01',
            'paymentMethod'    => 'required|in:bank_transfer,cash,card',
            'paymentDate'      => 'required|date',
            'paymentReference' => 'nullable|string|max:255',
        ]);

        $this->invoice->payments()->create([
            'amount'      => $this->paymentAmount,
            'currency'    => $this->invoice->currency,
            'paid_at'     => $this->paymentDate,
            'method'      => $this->paymentMethod,
            'reference'   => $this->paymentReference ?: null,
            'recorded_by' => auth()->id(),
        ]);

        $paidTotal = $this->invoice->payments()->sum('amount');
        $status    = $paidTotal >= $this->invoice->total ? 'paid'
                   : ($paidTotal > 0 ? 'partially_paid' : $this->invoice->status);

        $this->invoice->update(['paid_amount' => $paidTotal, 'status' => $status]);
        $this->invoice->refresh()->load(['customer', 'manager', 'quote', 'items.product', 'payments']);

        $this->showPaymentForm = false;
        $this->reset(['paymentAmount', 'paymentReference']);
        session()->flash('success', 'Платёж добавлен.');
    }

    public function changeStatus(string $status): void
    {
        // partially_paid and paid are managed automatically by payment recording
        $allowed = ['draft', 'sent', 'overdue', 'cancelled'];
        if (! in_array($status, $allowed)) return;

        if ($status === 'cancelled') {
            $hasPayments = $this->invoice->payments()->exists();
            $hasSells    = $this->invoice->sells()->exists();
            if ($hasPayments || $hasSells) {
                session()->flash('error', 'Нельзя отменить инвойс: по нему уже есть ' .
                    ($hasPayments ? 'платежи' : '') .
                    ($hasPayments && $hasSells ? ' и ' : '') .
                    ($hasSells ? 'отгрузки' : '') . '.');
                return;
            }
        }

        $this->invoice->update(['status' => $status]);
        $this->invoice->refresh();
    }

    // ── Shipment modal ────────────────────────────────────────────────────────

    public function openShipmentModal(): void
    {
        // Only items with a product_id can be shipped (sell_items.product_id is NOT NULL).
        $this->shipmentLines = $this->invoice->items
            ->filter(fn ($item) => $item->product_id !== null)
            ->values()
            ->map(fn ($item) => [
                'checked'             => true,
                'product_id'          => $item->product_id,
                'name'                => $item->name,
                'sku'                 => $item->sku ?? '',
                'invoice_quantity'    => (float) $item->quantity,
                'quantity'            => (float) $item->quantity,
                'unit_price'          => (float) $item->unit_price,
                'discount_percent'    => (float) ($item->discount_percent ?? 0),
                'is_serial'           => (bool)  ($item->product?->is_serial ?? false),
                'selected_serial_ids' => [],
            ])
            ->toArray();

        $this->shipmentDate  = now()->toDateString();
        $this->shipmentNotes = '';
        $this->showShipmentModal = true;
    }

    public function openSerialsModal(int $idx): void
    {
        $line = $this->shipmentLines[$idx] ?? null;
        if (!$line || !$line['is_serial']) return;

        $this->serialsLineIndex = $idx;
        $this->availableSerials = \App\Models\Catalog\ProductSerial::where('product_id', $line['product_id'])
            ->where('current_status', 'available')
            ->orderBy('serial_number')
            ->get(['id', 'serial_number'])
            ->toArray();

        $this->showSerialsModal = true;
    }

    public function toggleSerial(int $serialId): void
    {
        $idx = $this->serialsLineIndex;
        $selected = $this->shipmentLines[$idx]['selected_serial_ids'] ?? [];

        if (in_array($serialId, $selected)) {
            $this->shipmentLines[$idx]['selected_serial_ids'] = array_values(array_filter($selected, fn($id) => $id !== $serialId));
        } else {
            $this->shipmentLines[$idx]['selected_serial_ids'][] = $serialId;
        }
        // Keep quantity in sync
        $this->shipmentLines[$idx]['quantity'] = count($this->shipmentLines[$idx]['selected_serial_ids']);
    }

    public function closeSerialsModal(): void
    {
        $this->showSerialsModal = false;
        $this->availableSerials = [];
    }

    public function createShipment(): void
    {
        $this->resetErrorBag();

        // At least one line must be checked
        $selected = collect($this->shipmentLines)->filter(fn ($l) => ! empty($l['checked']));
        if ($selected->isEmpty()) {
            $this->addError('shipmentLines', 'Выберите хотя бы одну позицию.');
            return;
        }

        // Validate quantities / serials for selected lines
        foreach ($selected as $idx => $line) {
            if ($line['is_serial'] ?? false) {
                if (empty($line['selected_serial_ids'])) {
                    $this->addError("shipmentLines.{$idx}.quantity", 'Выберите серийные номера для товара: '.$line['name']);
                    return;
                }
                // quantity is auto-set by serial count, no need to validate > 0 separately
            } else {
                $qty = (float) ($line['quantity'] ?? 0);
                if ($qty <= 0) {
                    $this->addError("shipmentLines.{$idx}.quantity", 'Количество должно быть больше 0.');
                    return;
                }
            }
        }

        $this->validate([
            'shipmentDate'  => 'required|date',
            'shipmentNotes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($selected) {
            $service  = app(SellService::class);
            $subtotal = $selected->sum(function ($l) {
                $qty = ($l['is_serial'] ?? false) ? count($l['selected_serial_ids']) : (float) $l['quantity'];
                return round($qty * (float) $l['unit_price'] * (1 - (float) $l['discount_percent'] / 100), 2);
            });

            $sell = Sell::create([
                'number'        => $service->generateNumber(),
                'invoice_id'    => $this->invoice->id,
                'customer_id'   => $this->invoice->customer_id,
                'manager_id'    => auth()->id(),
                'status'        => 'draft',
                'sold_at'       => $this->shipmentDate,
                'currency'      => $this->invoice->currency,
                'exchange_rate' => $this->invoice->exchange_rate,
                'subtotal'      => $subtotal,
                'total'         => $subtotal,
                'notes'         => $this->shipmentNotes ?: null,
            ]);

            foreach ($selected->values() as $i => $line) {
                $isSerial  = $line['is_serial'] ?? false;
                $qty       = $isSerial ? count($line['selected_serial_ids']) : (float) $line['quantity'];
                $lineTotal = round(
                    $qty * (float) $line['unit_price'] * (1 - (float) $line['discount_percent'] / 100),
                    2
                );

                $sellItem = $sell->items()->create([
                    'product_id'       => $line['product_id'],
                    'quantity'         => $qty,
                    'unit_price'       => (float) $line['unit_price'],
                    'discount_percent' => (float) $line['discount_percent'],
                    'total'            => $lineTotal,
                    'sort_order'       => $i,
                ]);

                if ($isSerial && !empty($line['selected_serial_ids'])) {
                    $serialService = app(\App\Services\SerialService::class);
                    $serials = \App\Models\Catalog\ProductSerial::whereIn('id', $line['selected_serial_ids'])
                        ->where('product_id', $line['product_id'])
                        ->where('current_status', 'available')
                        ->get();
                    foreach ($serials as $serial) {
                        $serialService->markSold($serial, $this->invoice->customer_id, $sellItem->id);
                    }
                }
            }

            $service->recalculateShipmentStatus($this->invoice);
        });

        $this->showShipmentModal = false;
        $this->shipmentLines     = [];
        $this->shipmentNotes     = '';

        $this->invoice->refresh()->load(['customer', 'manager', 'quote', 'items.product', 'payments', 'sells']);
        session()->flash('success', 'Отгрузка создана.');
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.invoices.show');
    }
}
