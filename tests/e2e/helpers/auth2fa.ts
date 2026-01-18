import { expect, type Page } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';
import { waitForOtpFromLaravelLog } from './otpFromLaravelLog';

function makeWrongOtp(otp: string) {
    const last = Number(otp[otp.length - 1] ?? '0');
    const newLast = ((last + 1) % 10).toString();
    return otp.slice(0, -1) + newLast;
}

export async function maybePause(page: Page) {
    if (process.env.E2E_DEMO_PAUSE === '1') {
        await page.pause();
    }
}

export async function loginSubmit(page: Page, email: string, password: string) {
    await page.goto('/login');
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('button[type="submit"]').first().click();
}

export async function waitFor2faOrDashboard(page: Page) {
    const d1 = page.locator('input[name="d1"]');

    await Promise.race([
        page.waitForURL(/dashboard/i, { timeout: 10_000 }).catch(() => null),
        d1.waitFor({ state: 'visible', timeout: 10_000 }).catch(() => null),
    ]);

    return d1;
}

export async function fill2faDInputs(page: Page, otp: string) {
    const dInputs = page.locator('input[name^="d"][maxlength="1"]');
    const count = await dInputs.count();
    if (count < 6) throw new Error(`Expected 6 OTP inputs (d1..d6), found ${count}`);

    for (let i = 0; i < 6; i++) {
        await dInputs.nth(i).fill(otp[i]);
    }

    // zet ook hidden code (als aanwezig)
    await page.evaluate((value: string) => {
        const el =
            (document.querySelector('#code') as HTMLInputElement | null) || (document.querySelector('input[name="code"]') as HTMLInputElement | null);
        if (el) el.value = value;
    }, otp);
}

export async function submit2faVerify(page: Page) {
    // 2 submit buttons aanwezig -> kies expliciet "Verifiëren"
    await page.getByRole('button', { name: /verifiëren/i }).click();
}

export async function getLogCursorSize() {
    const logPath = path.join(process.cwd(), 'storage', 'logs', 'laravel.log');
    try {
        return (await fs.stat(logPath)).size;
    } catch {
        return 0;
    }
}

export async function loginToDashboardWith2fa(page: Page, email: string, password: string) {
    const fromSize = await getLogCursorSize();

    await loginSubmit(page, email, password);

    const d1 = await waitFor2faOrDashboard(page);

    // Als 2FA nodig is
    if (await d1.isVisible().catch(() => false)) {
        const otp = await waitForOtpFromLaravelLog({ fromSize });
        await fill2faDInputs(page, otp);
        await submit2faVerify(page);
    }

    await expect(page).toHaveURL(/dashboard/i);
}

export async function loginTo2faScreen(page: Page, email: string, password: string) {
    const fromSize = await getLogCursorSize();

    await loginSubmit(page, email, password);

    const d1 = page.locator('input[name="d1"]');
    await expect(d1).toBeVisible(); // hier verwachten we echt 2FA

    return fromSize;
}

export async function loginWithWrong2fa(page: Page, email: string, password: string) {
    const fromSize = await loginTo2faScreen(page, email, password);

    const otp = await waitForOtpFromLaravelLog({ fromSize });
    const wrong = makeWrongOtp(otp);

    await fill2faDInputs(page, wrong);
    await submit2faVerify(page);

    // verwacht: niet naar dashboard
    await expect(page).not.toHaveURL(/dashboard/i);
    await expect(page.locator('input[name="d1"]')).toBeVisible();

    // foutmelding ergens op de pagina
    await expect(page.locator('body')).toContainText(/ongeldig|incorrect|invalid|fout|verific/i);
}

export async function maybeWait(ms: number) {
    if (process.env.E2E_DEMO_SLOW === '1') {
        await new Promise((r) => setTimeout(r, ms));
    }
}
