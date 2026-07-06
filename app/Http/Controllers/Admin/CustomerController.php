<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        return view('admin.customers.index');
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }
}
