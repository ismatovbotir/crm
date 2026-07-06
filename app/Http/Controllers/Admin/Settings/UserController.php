<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.settings.users');
    }
}
