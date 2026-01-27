<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    // Alleen binnen deze controller gebruiken. Voor order en veiligheid
    protected RoleService $roles;

    public function __construct(RoleService $roles)
    {
        $this->roles = $roles;
    }

    public function index()
    {
        $roles = $this->roles->getAllRoles();

        return view('roles', ['roles' => $roles]);
    }

    public function switch(Request $request)
    {
        $user = Auth::user();
        $roleId = $request->input('role_id');

        if (!$user->roles()->where('roles.id', $roleId)->exists()) {
            return back()->withErrors(['role' => 'Je hebt geen toegang tot deze rol.']);
        }

        $this->roles->setActiveRoleId($roleId);

        return back()->with('success', 'Rol succesvol gewisseld!');
    }
}
