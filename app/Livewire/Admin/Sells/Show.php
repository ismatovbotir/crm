<?php

namespace App\Livewire\Admin\Sells;

use App\Models\Sell\Sell;
use App\Services\SellService;
use Livewire\Component;

class Show extends Component
{
    public Sell $sell;

    public function mount(Sell $sell): void
    {
        $this->authorize('view', $sell);
        $this->sell = $sell->load(['customer', 'manager', 'invoice', 'items.product']);
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->sell);

        $allowed = ['draft', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        if (! in_array($status, $allowed)) return;

        $this->sell->update(['status' => $status]);

        if ($this->sell->invoice_id) {
            $service = app(SellService::class);
            $service->recalculateShipmentStatus($this->sell->invoice);
        }

        $this->sell->refresh()->load(['customer', 'manager', 'invoice', 'items.product']);
        session()->flash('success', 'Статус обновлён.');
    }

    public function render()
    {
        return view('livewire.admin.sells.show');
    }
}
