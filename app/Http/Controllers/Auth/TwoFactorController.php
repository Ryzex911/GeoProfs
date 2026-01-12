<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class TwoFactorController extends Controller
{
    public function show()
    {
        return view('auth.2fa');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required','digits:6'],
        ]);

        $userId  = Session::get('2fa:user:id');
        $code    = Session::get('2fa:code');
        $expires = Session::get('2fa:expires_at');

        if (! $userId || ! $code || ! $expires) {
            return redirect()->route('login')->withErrors([
                'email' => 'Je sessie is verlopen. Log opnieuw in.',
            ]);
        }

        if (now()->greaterThan($expires)) {
            Session::forget(['2fa:user:id','2fa:code','2fa:expires_at','2fa:remember']);
            return redirect()->route('login')->withErrors([
                'email' => 'De 2FA-code is verlopen. Log opnieuw in.',
            ]);
        }

        if ($request->input('code') != $code) {
            return back()->withErrors(['code' => 'Ongeldige verificatiecode.']);
        }

        // Succes → log in en ruim sessie op
        $remember = (bool) Session::pull('2fa:remember', false);
        Auth::loginUsingId($userId, $remember);

        Session::forget(['2fa:user:id','2fa:code','2fa:expires_at','2fa:remember']);
        $request->session()->regenerate();

        $user = Auth::user();
        $roleService = app(\App\Services\RoleService::class);
        $firstRoleId = $user->roles()->pluck('roles.id')->first();
        if ($firstRoleId) {
            $roleService->setActiveRoleId((int)$firstRoleId);
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Je bent succesvol ingelogd.');
    }

    public function resend(Request $request)
    {
        $userId = $request->session()->get('2fa:user:id');
        if (! $userId) {
            return redirect()->route('login')->withErrors(['email' => 'Sessie verlopen. Log opnieuw in.']);
        }

        $code = random_int(100000, 999999);
        $request->session()->put('2fa:code', $code);
        $request->session()->put('2fa:expires_at', now()->addMinutes(10));

        $user = \App\Models\User::find($userId);

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($code));
            Log::info('2FA code resent', ['email' => $user->email, 'code' => $code]);
            return back()->with('status', 'Nieuwe 2FA-code is verzonden.');
        } catch (\Throwable $e) {
            Log::error('2FA mail resend failed', ['email' => $user->email, 'err' => $e->getMessage()]);
            return back()->withErrors(['code' => 'Kon de 2FA-e-mail niet verzenden. Probeer later opnieuw.']);
        }
    }
}
