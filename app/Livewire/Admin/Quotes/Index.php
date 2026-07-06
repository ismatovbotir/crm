<?php

namespace App\Livewire\Admin\Quotes;

use App\Models\Quote\Quote;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 15;
    public bool $showCreateForm = false;
    protected $queryString = [
        'search'       => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Quote::class);
    }

    public function getQuotesProperty()
    {
        $query = Quote::with(['customer', 'manager'])
            ->when(
                $this->search,
                fn ($q) => $q->where(fn ($q) => $q
                    ->where('number', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                )
            )
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter));

        // Non-admin managers only see their own quotes
        if (! auth()->user()->hasAnyRole(['super-admin', 'sales-director'])) {
            $query->where('manager_id', auth()->id());
        }

        return $query->latest()->paginate($this->perPage);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function closeForm(): void
    {
        $this->showCreateForm = false;
    }

    #[\Livewire\Attributes\On('quote-saved')]
    public function onQuoteSaved(): void
    {
        $this->showCreateForm = false;
    }

    public function render()
    {
        return view('livewire.admin.quotes.index', [
            'quotes'   => $this->quotes,
            'statuses' => ['draft', 'sent', 'viewed', 'accepted', 'rejected', 'expired'],
        ]);
    }
}
