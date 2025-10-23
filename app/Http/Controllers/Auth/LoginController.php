<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        // 1) Valideer invoer
        $credentials = $request->validate([
            'email'    => ['required','email','exists:users,email'],
            'password' => ['required','string'],
        ], [
            'email.exists' => 'Dit e-mailadres is niet bekend.',
        ]);

        // 2) Zoek user en check wachtwoord
        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Onjuist e-mailadres of wachtwoord.',
            ])->onlyInput('email');
        }

        // 3) Wachtwoord klopt → zet pending 2FA in sessie (nog NIET inloggen)
        $request->session()->put('2fa:user:id', $user->id);
        $request->session()->put('2fa:remember', (bool) $request->boolean('remember'));

        // (Hier kan je je eigen 2FA-code versturen)

        return redirect()->route('2fa.show');
    }
}
