<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function index()
    {
        return view('portal.profile.index');
    }
}
