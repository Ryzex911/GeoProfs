<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
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
        $this->authorize('viewAny', User::class);

        $users = $this->users->getAllUsers();
        $allRoles = Role::all();

        return view('users', compact('users', 'allRoles'));
    }

    public function updateUserRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('updateRoles', $user);

        $validated = $request->validate([
            'roles' => 'required|array'
        ]);

        $this->users->updateRoles($user, $validated['roles']);
        return redirect()->back()->with('success', 'Rollen bijgewerkt.');
    }
}
