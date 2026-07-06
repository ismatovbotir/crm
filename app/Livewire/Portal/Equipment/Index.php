<?php

namespace App\Livewire\Portal\Equipment;

use App\Models\Catalog\ProductSerial;
use App\Models\Catalog\SerialOwner;
use App\Services\SerialService;
use Livewire\Component;

class Index extends Component
{
    // Add device form state
    public bool   $showAddForm    = false;
    public string $serialNumber   = '';
    public string $extBrand       = '';
    public string $extModel       = '';
    public bool   $showExtFields  = false;   // show brand/model when serial not found
    public ?array $foundSerial    = null;    // when RSG serial found and confirmed

    // History panel
    public bool  $showHistory   = false;
    public ?int  $historyId     = null;
    public array $historyData   = [];

    public function getCustomer()
    {
        return auth()->user()->customers()->first();
    }

    // ── Add device ────────────────────────────────────────────────────────────

    public function lookupSerial(): void
    {
        $this->showExtFields = false;
        $this->foundSerial   = null;
        $this->resetErrorBag();

        $sn       = trim($this->serialNumber);
        $customer = $this->getCustomer();

        if (!$sn) {
            $this->addError('serialNumber', 'Введите серийный номер.');
            return;
        }

        $serial = ProductSerial::where('serial_number', $sn)->first();

        if (!$serial) {
            // Not in system — show fields to register as external
            $this->showExtFields = true;
            return;
        }

        // Already belongs to this customer
        if ($customer && $serial->current_owner_id === $customer->id) {
            $this->addError('serialNumber', 'Это устройство уже добавлено в ваш список.');
            return;
        }

        // RSG serial owned by someone else
        if ($serial->current_owner_id && (!$customer || $serial->current_owner_id !== $customer->id)) {
            $this->addError('serialNumber', 'Этот серийный номер принадлежит другой компании.');
            return;
        }

        // RSG serial, available — let them claim it
        $this->foundSerial = [
            'id'           => $serial->id,
            'display_name' => $serial->display_name,
            'is_external'  => false,
        ];
    }

    public function addDevice(): void
    {
        $customer = $this->getCustomer();
        if (!$customer) return;

        $sn = trim($this->serialNumber);

        if ($this->foundSerial) {
            // Claim an RSG serial
            $serial = ProductSerial::findOrFail($this->foundSerial['id']);
            $serial->update(['current_owner_id' => $customer->id]);

            SerialOwner::create([
                'serial_id'   => $serial->id,
                'customer_id' => $customer->id,
                'acquired_at' => now(),
            ]);
        } elseif ($this->showExtFields) {
            // Register external device
            $this->validate([
                'serialNumber' => 'required|string|max:100',
                'extBrand'     => 'nullable|string|max:100',
                'extModel'     => 'nullable|string|max:100',
            ]);

            // Check it doesn't already exist
            if (ProductSerial::where('serial_number', $sn)->exists()) {
                $this->addError('serialNumber', 'Серийный номер уже зарегистрирован в системе.');
                return;
            }

            app(SerialService::class)->registerExternal(
                $sn,
                $this->extBrand ?: null,
                $this->extModel ?: null,
                $customer->id,
            );
        } else {
            $this->addError('serialNumber', 'Сначала выполните поиск.');
            return;
        }

        $this->reset('serialNumber', 'extBrand', 'extModel', 'showExtFields', 'foundSerial', 'showAddForm');
        session()->flash('success', 'Устройство добавлено.');
    }

    // ── History panel ─────────────────────────────────────────────────────────

    public function openHistory(int $id): void
    {
        $customer = $this->getCustomer();
        $serial   = ProductSerial::with([
            'statusHistory',
            'tickets' => fn($q) => $q->latest()->limit(10),
        ])
            ->where('current_owner_id', $customer?->id)
            ->findOrFail($id);

        $this->historyId   = $id;
        $this->historyData = [
            'serial_number'  => $serial->serial_number,
            'display_name'   => $serial->display_name,
            'is_external'    => $serial->is_external,
            'current_status' => $serial->current_status,
            'statuses'       => $serial->statusHistory->map(fn($s) => [
                'status'     => $s->status,
                'notes'      => $s->notes,
                'created_at' => $s->created_at?->format('d.m.Y H:i'),
            ])->toArray(),
            'tickets' => $serial->tickets->map(fn($t) => [
                'id'      => $t->id,
                'number'  => $t->number,
                'subject' => $t->subject,
                'status'  => $t->status,
            ])->toArray(),
        ];

        $this->showHistory = true;
    }

    public function closeHistory(): void
    {
        $this->showHistory = false;
        $this->historyId   = null;
        $this->historyData = [];
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $customer = $this->getCustomer();

        $devices = $customer
            ? ProductSerial::with(['statusHistory' => fn($q) => $q->latest()->limit(1)])
                ->where('current_owner_id', $customer->id)
                ->orderByDesc('updated_at')
                ->get()
            : collect();

        return view('livewire.portal.equipment.index', ['devices' => $devices])
            ->layout('layouts.portal')->section('content');
    }
}
