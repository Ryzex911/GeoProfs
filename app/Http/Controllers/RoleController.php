<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;

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
}
