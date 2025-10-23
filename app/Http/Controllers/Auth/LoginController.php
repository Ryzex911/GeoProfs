<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // 1) Validate
        $data = $request->validate([
            'email'    => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string'],
        ], [
            'email.exists' => 'Dit e-mailadres is niet bekend.',
        ]);

        // 2) Find user + check password
        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'Onjuist e-mailadres of wachtwoord.',
            ])->onlyInput('email');
        }

        // 3) Generate 2FA code & store session (do NOT log in yet)
        $code = random_int(100000, 999999);

        $request->session()->put('2fa:user:id', $user->id);
        $request->session()->put('2fa:code', $code);
        $request->session()->put('2fa:expires_at', now()->addMinutes(10));
        $request->session()->put('2fa:remember', (bool) $request->boolean('remember'));

        // 4) Send email with logging
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($code));
            Log::info('2FA code sent', ['email' => $user->email, 'code' => $code]);
            return redirect()->route('2fa.show')->with('status', 'We hebben een 2FA-code gemaild.');
        } catch (\Throwable $e) {
            Log::error('2FA mail failed', ['email' => $user->email, 'err' => $e->getMessage()]);
            return redirect()->route('2fa.show')->withErrors([
                'code' => 'Kon de 2FA-e-mail niet verzenden. Probeer opnieuw of klik op "Code opnieuw sturen".'
            ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
