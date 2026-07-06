<?php

namespace App\Http\Controllers\Admin\Catalog;

use App\Http\Controllers\Controller;
use App\Models\Catalog\Product;

class ProductController extends Controller
{
    public function index()
    {
        return view('admin.catalog.products.index');
    }

    public function show(Product $product)
    {
        return view('admin.catalog.products.show', compact('product'));
    }
}
