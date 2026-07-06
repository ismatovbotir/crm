<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;
use App\Models\Quote\Quote;
use App\Services\PdfService;
use Symfony\Component\HttpFoundation\Response;

class PdfController extends Controller
{
    public function __construct(private PdfService $pdfService) {}

    public function quote(Quote $quote): Response
    {
        $customer = auth()->user()->customers()->first();
        abort_unless($customer && $quote->customer_id === $customer->id, 403);

        return $this->pdfService->downloadQuote($quote);
    }

    public function invoice(Invoice $invoice): Response
    {
        $customer = auth()->user()->customers()->first();
        abort_unless($customer && $invoice->customer_id === $customer->id, 403);

        return $this->pdfService->downloadInvoice($invoice);
    }
}
