<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quote\Quote;

class QuoteController extends Controller
{
    public function index()
    {
        return view('admin.quotes.index');
    }

    public function show(Quote $quote)
    {
        return view('admin.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        abort_if(in_array($quote->status, ['accepted', 'rejected', 'expired']), 403);
        abort_if($quote->invoice()->exists(), 403);
        return view('admin.quotes.edit', compact('quote'));
    }
}
