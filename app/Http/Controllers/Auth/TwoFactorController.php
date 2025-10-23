<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    // Laat de 2FA-code invoer zien
    public function show()
    {
        return view('auth.2fa');
    }

    // Verifieer de code
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric',
        ]);

        $userId = Session::get('2fa:user:id');
        $expectedCode = Session::get('2fa:user:code');

        if (!$userId || !$expectedCode) {
            return redirect()->route('login')->withErrors([
                'email' => 'Je sessie is verlopen. Log opnieuw in.',
            ]);
        }

        if ($request->input('code') != $expectedCode) {
            return back()->withErrors([
                'code' => 'Ongeldige verificatiecode.',
            ]);
        }

        // 2FA correct → verwijder sessie en log in
        Session::forget(['2fa:user:id', '2fa:user:code']);

        auth()->loginUsingId($userId);

        return redirect()->route('dashboard')->with('success', 'Je bent succesvol ingelogd.');
    }
}
