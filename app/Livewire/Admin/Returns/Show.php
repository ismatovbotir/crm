<?php

namespace App\Livewire\Admin\Returns;

use App\Models\Sell\ProductReturn;
use App\Services\ReturnService;
use Livewire\Component;

class Show extends Component
{
    public ProductReturn $productReturn;

    public function mount(ProductReturn $productReturn): void
    {
        $this->productReturn = $productReturn->load([
            'customer', 'manager', 'sell', 'invoice', 'ticket', 'items.product', 'items.serial',
        ]);
    }

    public function approve(): void
    {
        if ($this->productReturn->status !== 'draft') return;
        $this->productReturn->update(['status' => 'approved']);
        $this->productReturn->refresh();
        session()->flash('success', 'Возврат подтверждён.');
    }

    public function markRefunded(): void
    {
        if ($this->productReturn->status !== 'approved') return;
        app(ReturnService::class)->processRefund($this->productReturn);
        $this->productReturn->refresh()->load([
            'customer', 'manager', 'sell', 'invoice', 'ticket', 'items.product', 'items.serial',
        ]);
        session()->flash('success', 'Возврат выполнен. Остатки обновлены.');
    }

    public function cancel(): void
    {
        if (!in_array($this->productReturn->status, ['draft', 'approved'])) return;
        $this->productReturn->update(['status' => 'cancelled']);
        $this->productReturn->refresh();
        session()->flash('success', 'Возврат отменён.');
    }

    public function render()
    {
        return view('livewire.admin.returns.show')
            ->layout('layouts.admin')->section('content');
    }
}
