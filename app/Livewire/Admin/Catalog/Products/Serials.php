<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Models\Catalog\Product;
use App\Models\Catalog\ProductSerial;
use App\Models\Support\Ticket;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Serials extends Component
{
    use WithPagination, WithFileUploads;

    public Product $product;
    public string $search = '';
    public string $statusFilter = '';

    // Add form
    public bool $showAddForm = false;
    public string $newSerial = '';
    public string $newNotes = '';

    // Import
    public $importFile = null;

    // History slide-over
    public bool  $showHistory   = false;
    public ?int  $historySerial = null;
    public array $historyData   = [];

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function addSerial(): void
    {
        $this->authorize('update', $this->product);
        $this->validate([
            'newSerial' => 'required|string|max:100',
            'newNotes'  => 'nullable|string|max:500',
        ]);

        $exists = ProductSerial::where('product_id', $this->product->id)
            ->where('serial_number', $this->newSerial)
            ->exists();

        if ($exists) {
            $this->addError('newSerial', 'Такой серийный номер уже зарегистрирован для этого товара.');
            return;
        }

        ProductSerial::create([
            'product_id'    => $this->product->id,
            'serial_number' => $this->newSerial,
            'current_status' => 'available',
            'notes'         => $this->newNotes ?: null,
        ]);

        $this->reset('newSerial', 'newNotes', 'showAddForm');
        $this->resetPage();
        session()->flash('serial_success', 'Серийный номер добавлен.');
    }

    public function deleteSerial(int $id): void
    {
        $this->authorize('update', $this->product);
        $serial = ProductSerial::where('product_id', $this->product->id)->findOrFail($id);

        if ($serial->current_status !== 'available') {
            session()->flash('serial_error', 'Нельзя удалить серийный номер в статусе: ' . $serial->current_status);
            return;
        }

        $serial->delete();
    }

    public function importCsv(): void
    {
        $this->authorize('update', $this->product);
        $this->validate(['importFile' => 'required|file|mimes:csv,txt|max:2048']);

        $path = $this->importFile->getRealPath();
        $handle = fopen($path, 'r');
        $added = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $sn = trim($row[0] ?? '');
            if (!$sn || $sn === 'serial_number') {
                continue; // skip empty/header
            }

            $exists = ProductSerial::where('product_id', $this->product->id)
                ->where('serial_number', $sn)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            ProductSerial::create([
                'product_id'    => $this->product->id,
                'serial_number' => $sn,
                'current_status' => 'available',
            ]);
            $added++;
        }

        fclose($handle);

        $this->reset('importFile');
        $this->resetPage();
        session()->flash('serial_success', "Импортировано: {$added} шт. Пропущено (дубли): {$skipped} шт.");
    }

    public function openHistory(int $id): void
    {
        $serial = ProductSerial::with([
            'product',
            'currentOwner',
            'statusHistory',
            'ownerHistory.customer',
            'tickets' => fn ($q) => $q->latest()->limit(10),
        ])->findOrFail($id);

        $this->historySerial = $id;
        $this->historyData   = [
            'serial_number'  => $serial->serial_number,
            'display_name'   => $serial->display_name,
            'is_external'    => $serial->is_external,
            'current_status' => $serial->current_status,
            'owner_name'     => $serial->currentOwner?->name,
            'statuses'       => $serial->statusHistory->map(fn ($s) => [
                'status'     => $s->status,
                'notes'      => $s->notes,
                'created_at' => $s->created_at?->format('d.m.Y H:i'),
            ])->toArray(),
            'tickets'        => $serial->tickets->map(fn ($t) => [
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
        $this->showHistory   = false;
        $this->historySerial = null;
        $this->historyData   = [];
    }

    public function render()
    {
        $serials = ProductSerial::with(['currentOwner', 'statusHistory'])
            ->where('product_id', $this->product->id)
            ->when($this->search, fn ($q) => $q->where('serial_number', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('current_status', $this->statusFilter))
            ->orderBy('serial_number')
            ->paginate(20);

        return view('livewire.admin.catalog.products.serials', ['serials' => $serials]);
    }
}
