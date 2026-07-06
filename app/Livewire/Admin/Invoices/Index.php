<?php

namespace App\Livewire\Admin\Invoices;

use App\Models\Invoice\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 15;
    public bool $showCreateForm = false;

    protected $queryString = ['search' => ['except' => ''], 'statusFilter' => ['except' => '']];

    public function getInvoicesProperty()
    {
        $q = Invoice::with(['customer', 'manager'])
            ->when($this->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('number', 'like', "%{$this->search}%")
                  ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter));

        if (! auth()->user()->hasAnyRole(['super-admin', 'sales-director', 'accountant'])) {
            $q->where('manager_id', auth()->id());
        }

        return $q->latest()->paginate($this->perPage);
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function closeForm(): void { $this->showCreateForm = false; }

    #[\Livewire\Attributes\On('invoice-saved')]
    public function onInvoiceSaved(): void { $this->showCreateForm = false; }

    public function render()
    {
        return view('livewire.admin.invoices.index', [
            'invoices' => $this->invoices,
            'statuses' => ['draft', 'sent', 'partially_paid', 'paid', 'overdue', 'cancelled'],
        ]);
    }
}
