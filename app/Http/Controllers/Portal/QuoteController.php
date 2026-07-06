<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Quote\Quote;

class QuoteController extends Controller
{
    public function index()
    {
        return view('portal.quotes.index');
    }

    public function show(Quote $quote)
    {
        return view('portal.quotes.show', compact('quote'));
    }
}
