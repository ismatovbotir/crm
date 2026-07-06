<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Invoice\Invoice;
use App\Models\Lead\Lead;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Index extends Component
{
    public string $period = 'month'; // month, quarter, year, custom
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->can('reports.sales'), 403);
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    public function updatedPeriod(): void
    {
        match ($this->period) {
            'month'   => [$this->dateFrom, $this->dateTo] = [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'quarter' => [$this->dateFrom, $this->dateTo] = [now()->startOfQuarter()->toDateString(), now()->toDateString()],
            'year'    => [$this->dateFrom, $this->dateTo] = [now()->startOfYear()->toDateString(), now()->toDateString()],
            default   => null,
        };
    }

    private function from(): Carbon
    {
        return Carbon::parse($this->dateFrom)->startOfDay();
    }

    private function to(): Carbon
    {
        return Carbon::parse($this->dateTo)->endOfDay();
    }

    #[Computed]
    public function kpi(): array
    {
        $from = $this->from();
        $to   = $this->to();

        $revenue = Invoice::whereBetween('created_at', [$from, $to])
            ->whereIn('status', ['paid', 'partially_paid'])
            ->sum('paid_amount');

        $invoiceCount = Invoice::whereBetween('created_at', [$from, $to])->count();
        $avgDeal = $invoiceCount > 0 ? $revenue / $invoiceCount : 0;

        $newLeads = Lead::whereBetween('created_at', [$from, $to])->count();
        $wonLeads = Lead::where('status', 'won')->whereBetween('converted_at', [$from, $to])->count();
        $conversion = $newLeads > 0 ? round($wonLeads / $newLeads * 100) : 0;

        $overdueCount = Invoice::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->count();

        return compact('revenue', 'invoiceCount', 'avgDeal', 'newLeads', 'wonLeads', 'conversion', 'overdueCount');
    }

    #[Computed]
    public function salesByMonth(): array
    {
        $months = collect(range(11, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            $revenue = Invoice::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->whereIn('status', ['paid', 'partially_paid'])
                ->sum('paid_amount');
            return ['label' => $month->format('M y'), 'value' => (float) $revenue];
        });
        return $months->values()->all();
    }

    #[Computed]
    public function leadFunnel(): array
    {
        $statuses = [
            'new'            => 'Новый',
            'qualified'      => 'Квалифицирован',
            'contacted'      => 'Контакт',
            'in_negotiation' => 'Переговоры',
            'won'            => 'Выиграл',
            'lost'           => 'Потерян',
        ];
        $counts = Lead::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $result = [];
        foreach ($statuses as $key => $label) {
            $result[] = ['status' => $key, 'label' => $label, 'count' => $counts[$key] ?? 0];
        }
        return $result;
    }

    #[Computed]
    public function topManagers(): array
    {
        $from = $this->from();
        $to   = $this->to();

        return Invoice::with('manager')
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('manager_id')
            ->selectRaw('manager_id, sum(paid_amount) as total_paid, count(*) as invoice_count')
            ->groupBy('manager_id')
            ->orderByDesc('total_paid')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'name'          => $row->manager?->name ?? '—',
                'total_paid'    => (float) $row->total_paid,
                'invoice_count' => $row->invoice_count,
            ])
            ->all();
    }

    #[Computed]
    public function overdueInvoices()
    {
        return Invoice::with(['customer', 'manager'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.reports.index');
    }
}
