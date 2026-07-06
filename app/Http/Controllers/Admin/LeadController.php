<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead\Lead;

class LeadController extends Controller
{
    public function index()
    {
        return view('admin.leads.index');
    }

    public function show(Lead $lead)
    {
        return view('admin.leads.show', compact('lead'));
    }
}
