<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ){}

    // Laat alle users met hun rollen zien
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getAllUsers();
        $allRoles = $this->userService->getAllRoles();

        return view('users', compact('users', 'allRoles'));
    }




    // Users rollen wijzigen
    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        $this->authorize('updateRoles', $user);

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id' // Checkt of elke rol bestaat
        ]);

        $this->userService->updateRoles($user, $validated['roles']);
        return redirect()->back()->with('success', 'Rollen bijgewerkt.');
    }
}
