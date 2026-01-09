<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $roleIds = $validated['roles'];
        $adminRoleId = $this->userService->getAdminRoleId(); // Role id wordt opgehaald

        // check of de ingelogde gebruiker overeen komt met de gebruiker die bewerkt wordt
        if (Auth::id() === $user->id) {

            $isCurrentAdmin = $user->hasRole('admin'); // check of een gebruiker admin is

            $willLoseAdminRole = $adminRoleId && !in_array($adminRoleId, $roleIds); // check als admin niet in de lijst staat van de nieuwe rollen

            // als de gebruiker admin is en de admin-rol wordt verwijderd >> blokkeer de actie
            if ($isCurrentAdmin && $willLoseAdminRole) {
                return redirect()->back()->with('error', 'Fout: Je kunt de Admin-rol niet van jezelf verwijderen.');
            }
        }

        $this->userService->updateRoles($user, $roleIds);
        return redirect()->back()->with('success', 'Rollen bijgewerkt.');
    }
}
