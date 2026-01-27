<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Services\AuditLogger;
use App\Services\RoleService;
use App\Models\User;
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

    public function verify(Request $request, AuditLogger $audit)
    {
        $request->validate([
            'code' => ['required','digits:6'],
        ]);

        $userId  = Session::get('2fa:user:id');
        $code    = Session::get('2fa:code');
        $expires = Session::get('2fa:expires_at');

        // Sessie mist data
        if (! $userId || ! $code || ! $expires) {
            $audit->log(
                action: 'auth.2fa.session_missing',
                auditable: null,
                oldValues: null,
                newValues: [
                    'pending_user_id' => $userId,
                    'reason' => 'missing_session_data',
                ],
                logType: 'security',
                description: '2FA verify attempted but session data missing'
            );

            return redirect()->route('login')->withErrors([
                'email' => 'Je sessie is verlopen. Log opnieuw in.',
            ]);
        }

        // Code verlopen
        if (now()->greaterThan($expires)) {
            $audit->log(
                action: 'auth.2fa.expired',
                auditable: null,
                oldValues: null,
                newValues: [
                    'pending_user_id' => $userId,
                    'reason' => 'expired_code',
                ],
                logType: 'security',
                description: '2FA code expired'
            );

            Session::forget(['2fa:user:id','2fa:code','2fa:expires_at','2fa:remember']);

            return redirect()->route('login')->withErrors([
                'email' => 'De 2FA-code is verlopen. Log opnieuw in.',
            ]);
        }

        // Code fout
        if ($request->input('code') != $code) {
            $audit->log(
                action: 'auth.2fa.failed',
                auditable: null,
                oldValues: null,
                newValues: [
                    'pending_user_id' => $userId,
                    'reason' => 'invalid_code',
                ],
                logType: 'security',
                description: '2FA verification failed'
            );

            return back()->withErrors(['code' => 'Ongeldige verificatiecode.']);
        }

        // Succes → log in en ruim sessie op
        $remember = (bool) Session::pull('2fa:remember', false);

        // Log 2FA verified vóór de login (zodat ook als login faalt je 2FA success ziet)
        $audit->log(
            action: 'auth.2fa.verified',
            auditable: null,
            oldValues: null,
            newValues: [
                'pending_user_id' => $userId,
                'remember' => $remember,
            ],
            logType: 'security',
            description: '2FA verified'
        );

        Auth::loginUsingId($userId, $remember);

        Session::forget(['2fa:user:id','2fa:code','2fa:expires_at','2fa:remember']);
        $request->session()->regenerate();

        // Active role zetten (zoals je al deed)
        $user = Auth::user();
        $roleService = app(RoleService::class);

        $firstRoleId = $user->roles()->pluck('roles.id')->first();
        if ($firstRoleId) {
            $roleService->setActiveRoleId((int) $firstRoleId);

            // optioneel: log active role set (handig voor audit)
            $audit->log(
                action: 'role.active_set',
                auditable: $user,
                oldValues: null,
                newValues: [
                    'active_role_id' => (int) $firstRoleId,
                ],
                logType: 'audit',
                description: 'Active role set after login'
            );
        }

        return redirect()->intended(route('dashboard'))->with('success', 'Je bent succesvol ingelogd.');
    }

    public function resend(Request $request, AuditLogger $audit)
    {
        $userId = $request->session()->get('2fa:user:id');

        if (! $userId) {
            $audit->log(
                action: 'auth.2fa.session_missing',
                auditable: null,
                oldValues: null,
                newValues: [
                    'pending_user_id' => null,
                    'reason' => 'resend_without_session',
                ],
                logType: 'security',
                description: '2FA resend attempted but session missing'
            );

            return redirect()->route('login')->withErrors(['email' => 'Sessie verlopen. Log opnieuw in.']);
        }

        $code = random_int(100000, 999999);
        $request->session()->put('2fa:code', $code);
        $request->session()->put('2fa:expires_at', now()->addMinutes(10));

        $user = User::find($userId);

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($code));
            Log::info('2FA code resent', ['email' => $user->email]);

            $audit->log(
                action: 'auth.2fa.sent',
                auditable: $user,
                oldValues: null,
                newValues: [
                    'pending_user_id' => $userId,
                    'channel' => 'email',
                    'expires_in_minutes' => 10,
                ],
                logType: 'security',
                description: '2FA code sent/resend'
            );

            return back()->with('status', 'Nieuwe 2FA-code is verzonden.');
        } catch (\Throwable $e) {
            Log::error('2FA mail resend failed', ['email' => $user?->email, 'err' => $e->getMessage()]);

            $audit->log(
                action: 'auth.2fa.send_failed',
                auditable: $user,
                oldValues: null,
                newValues: [
                    'pending_user_id' => $userId,
                    'channel' => 'email',
                    'error' => $e->getMessage(),
                ],
                logType: 'security',
                description: '2FA email send failed'
            );

            return back()->withErrors(['code' => 'Kon de 2FA-e-mail niet verzenden. Probeer later opnieuw.']);
        }
    }
}
