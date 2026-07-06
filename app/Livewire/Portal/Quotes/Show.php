<?php

namespace App\Livewire\Portal\Quotes;

use App\Models\Quote\Quote;
use App\Notifications\QuoteAcceptedNotification;
use App\Notifications\QuoteViewedNotification;
use App\Services\TelegramService;
use Livewire\Component;

class Show extends Component
{
    public Quote $quote;

    public function mount(Quote $quote): void
    {
        // Check that this quote belongs to ANY of the user's companies (not just the first one).
        abort_unless(
            auth()->user()->customers()->where('customers.id', $quote->customer_id)->exists(),
            403
        );

        if ($quote->status === 'sent') {
            $quote->update(['status' => 'viewed', 'viewed_at' => now()]);
            $quote->refresh();

            if ($quote->manager) {
                $quote->manager->notify(new QuoteViewedNotification($quote));
            }
        }

        $this->quote = $quote;
    }

    public function accept(): void
    {
        $this->assertOwnership();
        abort_unless(in_array($this->quote->status, ['sent', 'viewed']), 403);

        $this->quote->update(['status' => 'accepted', 'accepted_at' => now()]);
        $this->quote->refresh();

        if ($this->quote->manager) {
            $this->quote->manager->notify(new QuoteAcceptedNotification($this->quote));
        }

        TelegramService::send(
            "✅ <b>КП принято</b>\n" .
            "КП: {$this->quote->number}\n" .
            "Клиент: {$this->quote->customer->name}\n" .
            "Сумма: " . number_format($this->quote->total, 0, '.', ' ') . " {$this->quote->currency}"
        );

        session()->flash('success', 'Коммерческое предложение принято. Менеджер свяжется с вами.');
    }

    public function reject(): void
    {
        $this->assertOwnership();
        abort_unless(in_array($this->quote->status, ['sent', 'viewed']), 403);

        $this->quote->update(['status' => 'rejected']);
        $this->quote->refresh();
        session()->flash('success', 'Предложение отклонено.');
    }

    private function assertOwnership(): void
    {
        abort_unless(
            auth()->user()->customers()->where('customers.id', $this->quote->customer_id)->exists(),
            403
        );
    }

    public function render()
    {
        return view('livewire.portal.quotes.show', [
            'items' => $this->quote->items()->with('product')->get(),
        ]);
    }
}
