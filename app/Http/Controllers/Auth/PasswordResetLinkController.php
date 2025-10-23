<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    /**
     * Toon het "wachtwoord vergeten" formulier.
     */
    public function create()
    {
        return view('auth.forgot-password')->with('status', session('status'));
    }

    /**
     * Verwerk het formulier en stuur de resetlink.
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'E-mailadres is verplicht.',
            'email.email'    => 'Dit is geen geldig e-mailadres.',
            'email.exists'   => 'Dit e-mailadres komt niet voor in ons systeem.',
        ]);

        // Stuur resetlink
        Password::sendResetLink($request->only('email'));

        // Altijd een nette succesmelding
        return back()->with('status', __('Er is een resetlink naar je e-mailadres gestuurd.'));
    }
}
