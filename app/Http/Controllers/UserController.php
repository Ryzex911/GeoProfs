<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $users;

    public function __construct(UserService $users)
    {
        $this->users = $users;
    }

    public function index()
    {
        $users = $this->users->getAllUsers();
        $allRoles = Role::all();

        return view('users', compact('users', 'allRoles'));
    }

    public function updateUserRoles(Request $request, $id)
    {
        $validated = $request->validate([
            'roles' => 'required|array'
        ]);

        $this->users->updateRoles($id, $validated['roles']);
        return redirect()->back()->with('success', 'Rollen bijgewerkt.');
    }
}
