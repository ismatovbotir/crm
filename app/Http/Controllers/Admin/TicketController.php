<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return view('admin.tickets.index');
    }

    public function show(Ticket $ticket)
    {
        return view('admin.tickets.show', compact('ticket'));
    }
}
