<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Support\Ticket;

class TicketController extends Controller
{
    public function index()
    {
        return view('portal.tickets.index');
    }

    public function show(Ticket $ticket)
    {
        return view('portal.tickets.show', compact('ticket'));
    }
}
