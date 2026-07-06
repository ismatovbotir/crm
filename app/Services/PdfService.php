<?php

namespace App\Services;

use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use App\Models\Sell\Sell;
use Symfony\Component\HttpFoundation\Response;

class PdfService
{
    public function downloadQuote(Quote $quote): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.quote', [
            'quote' => $quote,
            'items' => $quote->items()->with('product')->get(),
        ])->setPaper('a4');

        $filename = 'КП-' . $quote->number . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadInvoice(Invoice $invoice): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'invoice'  => $invoice,
            'items'    => $invoice->items()->get(),
            'payments' => $invoice->payments()->orderBy('paid_at')->get(),
        ])->setPaper('a4');

        $filename = 'Инвойс-' . $invoice->number . '.pdf';

        return $pdf->download($filename);
    }

    public function downloadSell(Sell $sell): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.sell', [
            'sell'  => $sell,
            'items' => $sell->items()->with('product')->orderBy('sort_order')->get(),
        ])->setPaper('a4');

        $filename = 'Накладная-' . $sell->number . '.pdf';

        return $pdf->download($filename);
    }
}
