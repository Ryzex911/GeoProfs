<?php

namespace App\Http\Controllers;

use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $this->authorize('accessAdmin', User::class);

        return view('admin.dashboard');
    }
}
