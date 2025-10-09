<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\TwoFactorAuth;

class TwoFactorController extends Controller
{
    // Toon 2FA pagina + stuur code via Gmail
    public function show()
    {
        $user = Auth::user();
        abort_unless($user, 401);

        // Verwijder oude ongebruikte codes
        TwoFactorAuth::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        // Genereer een 6-cijferige code
        $code = (string) random_int(100000, 999999);

        // Sla code op (gehashed) met vervaldatum
        TwoFactorAuth::create([
            'user_id'    => $user->id,
            'channel'    => 'email',
            'code'  => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        // --- PHPMailer met Gmail ---
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function ($str, $level) {
            \Log::info('PHPMailer SMTP: ' . trim($str));
        };

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_GMAIL_USER'); // jouw Gmail
            $mail->Password   = env('MAIL_GMAIL_PASS'); // app-wachtwoord
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), 'GeoProfs');
            $mail->addAddress($user->email, $user->name);
            $mail->isHTML(true);
            $mail->Subject = 'Je GeoProfs 2FA code';
            $mail->Body    = "
                <p>Hallo {$user->name},</p>
                <p>Je 2FA code is:
                   <strong style='font-size:22px;color:#0E3A5B;'>{$code}</strong></p>
                <p>Deze code verloopt over 10 minuten.</p>
                <p>Groet,<br>GeoProfs</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            \Log::error('PHPMailer fout: ' . $mail->ErrorInfo);
        }

        // Toon je 2FA pagina (met de 6 invoervelden)
        return view('auth.2fa');
    }

    // Controleer of de code juist is
    public function verify(Request $request)
    {
        $request->validate([
            'd1' => 'required|digits:1',
            'd2' => 'required|digits:1',
            'd3' => 'required|digits:1',
            'd4' => 'required|digits:1',
            'd5' => 'required|digits:1',
            'd6' => 'required|digits:1',
        ]);

        $user = Auth::user();
        abort_unless($user, 401);

        $entered = $request->d1.$request->d2.$request->d3.$request->d4.$request->d5.$request->d6;

        // Zoek actieve code
        $record = TwoFactorAuth::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $record) {
            return back()->withErrors(['code' => 'Geen geldige code gevonden of code is verlopen.']);
        }

        if (! Hash::check($entered, $record->code)) {
            return back()->withErrors(['code' => 'De ingevoerde code is onjuist.']);
        }

        $record->update(['verified_at' => now(), 'used_at' => now()]);
        session(['2fa_passed' => true]);

        return redirect()->route('dashboard');
    }
}
