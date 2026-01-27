<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    // Laat alle users met hun rollen zien
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = $this->userService->getAllUsers();
        $allRoles = $this->userService->getAllRoles();

        return view('users', compact('users', 'allRoles'));
    }

    // Users rollen wijzigen + audit log
    public function updateUserRoles(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('updateRoles', $user);

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id'
        ]);

        $roleIds = $validated['roles'];
        $adminRoleId = $this->userService->getAdminRoleId();

        // BEFORE snapshot (ids + names)
        $user->loadMissing('roles');
        $beforeIds   = $user->roles->pluck('id')->values()->all();
        $beforeNames = $user->roles->pluck('name')->values()->all();

        // Prevent: admin kan zichzelf niet de-admin maken
        if (Auth::id() === $user->id) {
            $isCurrentAdmin = $user->hasRole('admin');
            $willLoseAdminRole = $adminRoleId && !in_array($adminRoleId, $roleIds);

            $willLoseAdminRole = $adminRoleId && !in_array($adminRoleId, $roleIds); // check als admin niet in de lijst staat van de nieuwe rollen

            // als de gebruiker admin is en de admin-rol wordt verwijderd >> blokkeer de actie
            if ($isCurrentAdmin && $willLoseAdminRole) {

                // Audit: geblokkeerde poging
                $audit->log(
                    action: 'user.roles.update_blocked',
                    auditable: $user,
                    oldValues: ['role_ids' => $beforeIds, 'roles' => $beforeNames],
                    newValues: ['attempted_role_ids' => $roleIds],
                    logType: 'audit',
                    description: "Blocked attempt to remove admin role from self (user #{$user->id})"
                );

                return redirect()->back()->with('error', 'Fout: Je kunt de Admin-rol niet van jezelf verwijderen.');
            }
        }

        // Update via service
        $this->userService->updateRoles($user, $roleIds);

        // AFTER snapshot (force refresh)
        $user->refresh()->load('roles');
        $afterIds   = $user->roles->pluck('id')->values()->all();
        $afterNames = $user->roles->pluck('name')->values()->all();

        // Diffs
        $added   = array_values(array_diff($afterNames, $beforeNames));
        $removed = array_values(array_diff($beforeNames, $afterNames));

        // Audit log
        $audit->log(
            action: 'user.roles.updated',
            auditable: $user,
            oldValues: [
                'role_ids' => $beforeIds,
                'roles'    => $beforeNames,
            ],
            newValues: [
                'role_ids' => $afterIds,
                'roles'    => $afterNames,
                'added'    => $added,
                'removed'  => $removed,
            ],
            logType: 'audit',
            description: "Roles updated for user #{$user->id}"
        );

        return redirect()->back()->with('success', 'Rollen bijgewerkt.');
    }
}
