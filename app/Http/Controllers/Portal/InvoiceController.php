<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Invoice\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        return view('portal.invoices.index');
    }

    public function show(Invoice $invoice)
    {
        return view('portal.invoices.show', compact('invoice'));
    }
}
