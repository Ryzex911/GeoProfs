import fs from 'node:fs/promises';
import path from 'node:path';

export async function waitForOtpFromLaravelLog(opts: { fromSize: number; timeoutMs?: number }) {
    const { fromSize, timeoutMs = 20_000 } = opts;
    const logPath = path.join(process.cwd(), 'storage', 'logs', 'laravel.log');
    const start = Date.now();

    while (Date.now() - start < timeoutMs) {
        try {
            const buf = await fs.readFile(logPath);
            const fresh = buf.subarray(fromSize).toString('utf8');

            // 1) Context match (prefer)
            const ctx = fresh.match(/(?:2fa|otp|code|verification|verificatie)[^\d]{0,50}(\d{6})/i)?.[1] ?? null;

            if (ctx) return ctx;

            // 2) Fallback: pak de laatste 6-cijferige code uit het nieuwe logdeel
            const all = [...fresh.matchAll(/\b(\d{6})\b/g)].map((m) => m[1]);
            if (all.length) return all[all.length - 1];
        } catch {
            // soms kort locked / nog niet aangemaakt
        }

        await new Promise((r) => setTimeout(r, 500));
    }

    throw new Error('OTP not found in laravel.log within timeout');
}
console.log('IMAP USER:', process.env.IMAP_USER);
