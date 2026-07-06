<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Support\EquipmentRequest;

class EquipmentRequestController extends Controller
{
    public function index()
    {
        return view('admin.equipment-requests.index');
    }

    public function show(EquipmentRequest $equipmentRequest)
    {
        return view('admin.equipment-requests.show', compact('equipmentRequest'));
    }
}
