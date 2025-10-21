<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        // 1) Basis validatie
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // 2) Probeer in te loggen
        if (Auth::attempt($credentials)) {
            // 3) Verfris sessie (beveiliging) en zorg dat user echt ingelogd blijft
            $request->session()->regenerate();

            // 4) -> 2FA stap: stuur naar /2fa zodat mail met code wordt verstuurd
            return redirect()->route('2fa.show');
        }

        // 5) Mislukt? Terug met foutmelding
        return back()->withErrors([
            'email' => 'De ingevoerde gegevens zijn onjuist.',
        ])->onlyInput('email');
    }


    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
