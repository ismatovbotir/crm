<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.catalog.categories.index');
    }
}
