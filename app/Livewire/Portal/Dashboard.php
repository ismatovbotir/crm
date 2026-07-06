<?php

namespace App\Livewire\Portal;

use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use App\Models\Support\Ticket;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $customer = auth()->user()->customers()->first();
        $cid = $customer?->id;

        return view('livewire.portal.dashboard', [
            'customer'            => $customer,
            'openQuotesCount'     => $cid ? Quote::where('customer_id', $cid)->whereIn('status', ['sent', 'viewed'])->count() : 0,
            'unpaidInvoicesCount' => $cid ? Invoice::where('customer_id', $cid)->whereNotIn('status', ['paid', 'cancelled'])->count() : 0,
            'openTicketsCount'    => $cid ? Ticket::where('customer_id', $cid)->whereNotIn('status', ['resolved', 'closed'])->count() : 0,
            'recentQuotes'        => $cid ? Quote::where('customer_id', $cid)->latest()->limit(5)->get() : collect(),
            'recentTickets'       => $cid ? Ticket::where('customer_id', $cid)->latest()->limit(5)->get() : collect(),
        ]);
    }
}
