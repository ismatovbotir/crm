<?php

namespace App\Livewire\Admin;

use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use App\Models\Invoice\Payment;
use App\Models\Lead\Lead;
use App\Models\Quote\Quote;
use App\Models\Support\Ticket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $startOfMonth   = now()->startOfMonth();
        $startLastMonth = now()->subMonth()->startOfMonth();
        $endLastMonth   = now()->subMonth()->endOfMonth();

        // Leads KPI
        $newLeadsCount     = Lead::where('created_at', '>=', $startOfMonth)->count();
        $newLeadsLastMonth = Lead::whereBetween('created_at', [$startLastMonth, $endLastMonth])->count();
        $leadsChange       = $newLeadsLastMonth > 0
            ? round((($newLeadsCount - $newLeadsLastMonth) / $newLeadsLastMonth) * 100)
            : null;

        // Customers
        $activeCustomers = Customer::active()->count();

        // Quotes
        $openQuotesCount = Quote::whereIn('status', ['sent', 'viewed'])->count();

        // Tickets
        $openTicketsCount = Ticket::whereNotIn('status', ['resolved', 'closed'])->count();
        $criticalTickets  = Ticket::where('priority', 'critical')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        // Revenue — sum actual payments by their payment date
        $monthRevenue     = Payment::where('paid_at', '>=', $startOfMonth->toDateString())->sum('amount');
        $lastMonthRevenue = Payment::whereBetween('paid_at', [
            $startLastMonth->toDateString(),
            $endLastMonth->toDateString(),
        ])->sum('amount');
        $revenueChange    = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100)
            : null;

        // Overdue invoices
        $overdueCount = Invoice::whereNotIn('status', ['paid', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->count();

        // Weekly revenue — single query, group in PHP (1 query instead of 8)
        $eightWeeksAgo = now()->subWeeks(7)->startOfWeek();
        $allWeekPayments = Payment::where('paid_at', '>=', $eightWeeksAgo->toDateString())
            ->get(['paid_at', 'amount']);

        $weeklyRevenue = collect(range(7, 0))->map(function ($weeksAgo) use ($allWeekPayments) {
            $start = now()->subWeeks($weeksAgo)->startOfWeek();
            $end   = now()->subWeeks($weeksAgo)->endOfWeek();

            return [
                'label' => $start->format('d.m'),
                'value' => (float) $allWeekPayments
                    ->filter(fn ($p) => $p->paid_at >= $start && $p->paid_at <= $end)
                    ->sum('amount'),
            ];
        });

        // Lead sources for doughnut chart
        $leadSources = Lead::select('source_id', DB::raw('count(*) as count'))
            ->with('source')
            ->groupBy('source_id')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'label' => $r->source?->name ?? 'Другое',
                'value' => $r->count,
            ]);

        // Recent leads with relations
        $recentLeads = Lead::with(['source', 'manager'])
            ->latest()
            ->limit(8)
            ->get();

        return view('livewire.admin.dashboard', compact(
            'newLeadsCount', 'leadsChange',
            'activeCustomers', 'openQuotesCount',
            'openTicketsCount', 'criticalTickets',
            'monthRevenue', 'revenueChange',
            'overdueCount',
            'weeklyRevenue', 'leadSources', 'recentLeads'
        ));
    }
}
