<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class CatalogController extends Controller
{
    public function index()
    {
        return view('portal.catalog.index');
    }
}
