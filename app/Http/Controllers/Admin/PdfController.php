<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use App\Models\Sell\Sell;
use App\Services\PdfService;
use Symfony\Component\HttpFoundation\Response;

class PdfController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    public function quote(Quote $quote): Response
    {
        $this->authorize('view', $quote);

        return $this->pdfService->downloadQuote($quote);
    }

    public function invoice(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        return $this->pdfService->downloadInvoice($invoice);
    }

    public function sell(Sell $sell): Response
    {
        $this->authorize('view', $sell);

        $sell->load(['customer', 'manager', 'invoice']);

        return $this->pdfService->downloadSell($sell);
    }
}
