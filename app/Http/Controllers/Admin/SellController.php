<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sell\Sell;

class SellController extends Controller
{
    public function index()
    {
        return view('admin.sells.index');
    }

    public function show(Sell $sell)
    {
        return view('admin.sells.show', compact('sell'));
    }
}
