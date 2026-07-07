<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Invoice\Invoice;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function customers(): StreamedResponse
    {
        abort_unless(auth()->user()->can('customers.export'), 403);

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['ID', 'Название', 'ИНН', 'Статус', 'Сегмент', 'Телефон', 'Email', 'Регион', 'Город', 'Менеджер', 'Клиент с'], ';');

            Customer::with('users')
                ->orderBy('id')
                ->chunk(200, function ($customers) use ($handle) {
                    foreach ($customers as $customer) {
                        fputcsv($handle, [
                            $customer->id,
                            $customer->name,
                            $customer->inn ?? '',
                            $customer->status,
                            $customer->segment ?? '',
                            $customer->phone ?? '',
                            $customer->email ?? '',
                            $customer->region ?? '',
                            $customer->city ?? '',
                            $customer->users->first()?->name ?? '',
                            $customer->customer_since?->format('d.m.Y') ?? '',
                        ], ';');
                    }
                });

            fclose($handle);
        }, 'customers-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function invoices(): StreamedResponse
    {
        abort_unless(auth()->user()->can('invoices.export'), 403);

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['Номер', 'Клиент', 'Менеджер', 'Дата', 'Срок оплаты', 'Статус', 'Валюта', 'Сумма', 'Оплачено', 'Остаток'], ';');

            Invoice::with(['customer', 'manager'])
                ->orderBy('id')
                ->chunk(200, function ($invoices) use ($handle) {
                    foreach ($invoices as $invoice) {
                        fputcsv($handle, [
                            $invoice->number,
                            $invoice->customer?->name ?? '',
                            $invoice->manager?->name ?? '',
                            $invoice->created_at->format('d.m.Y'),
                            $invoice->due_date?->format('d.m.Y') ?? '',
                            $invoice->status,
                            $invoice->currency,
                            number_format($invoice->total, 2, '.', ''),
                            number_format($invoice->paid_amount, 2, '.', ''),
                            number_format($invoice->remaining, 2, '.', ''),
                        ], ';');
                    }
                });

            fclose($handle);
        }, 'invoices-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
