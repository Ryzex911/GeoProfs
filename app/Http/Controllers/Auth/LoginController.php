<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function showLoginForm(): \Illuminate\View\View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // 1) Valideer invoer
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();
        // Checkt of een user geblokkeerd is
        if ($user && $user->isLocked()) {
            return back()->withErrors([
                'email' => 'Je account is tijdelijk geblokkeerd. Neem contact op met ICT.'
            ])->onlyInput('email');
        }

        $failedAttempts = LoginAttempt::forUser($user)->count();

        // Als gebruiker 3 of meer mislukte pogingen heeft → blokkeren
        if ($failedAttempts >= 3) {
            $user->lockNow();

            return back()->withErrors([
                'email' => 'Je account is geblokkeerd. Neem contact op met ICT.',
            ]);
        }

        // 2) Zoek user en check wachtwoord
        $user = User::where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {

            $email = strtolower(trim($request->input('email')));
            $user = User::where('email', $email)->first();

            LoginAttempt::create([
                'user_id' => optional($user)->id,   // NULL als user niet bestaat
                'email_tried' => $email,
                'attempts' => 1,
                'attempted_at' => now(),
            ]);


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

    protected function authenticated($request, $user)
    {
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('dashboard');
    }


    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
