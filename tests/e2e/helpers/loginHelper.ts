import { Page, expect } from "@playwright/test";
import fs from "node:fs/promises";
import path from "node:path";
import { waitForOtpFromLaravelLog } from "./otpFromLaravelLog"; // pas aan naar jouw helper

export async function loginWith2FA(page: Page) {
    const logPath = path.join(process.cwd(), "storage", "logs", "laravel.log");

    // Alleen nieuwe logregels lezen
    let fromSize = 0;
    try {
        fromSize = (await fs.stat(logPath)).size;
    } catch {
        fromSize = 0;
    }

    // 1) Login
    await page.goto("/login");
    await page.locator('input[name="email"]').fill(process.env.E2E_EMAIL!);
    await page.locator('input[name="password"]').fill(process.env.E2E_PASSWORD!);
    await page.locator('button[type="submit"]').first().click();

    // 2) Wacht op dashboard of 2FA
    const d1 = page.locator('input[name="d1"]');
    await Promise.race([
        page.waitForURL(/dashboard/i, { timeout: 10_000 }).catch(() => null),
        d1.waitFor({ state: "visible", timeout: 10_000 }).catch(() => null),
    ]);

    // 3) 2FA indien nodig
    if (await d1.isVisible().catch(() => false)) {
        const otp = await waitForOtpFromLaravelLog({ fromSize });

        const dInputs = page.locator('input[name^="d"][maxlength="1"]');
        for (let i = 0; i < 6; i++) {
            await dInputs.nth(i).fill(otp[i]);
        }

        await page.evaluate((value: string) => {
            const el =
                document.querySelector<HTMLInputElement>("#code") ||
                document.querySelector<HTMLInputElement>('input[name="code"]');
            if (el) el.value = value;
        }, otp);

        // ✅ BETROUWBARE submit + redirect
        await Promise.all([
            page.waitForURL(/dashboard/i, { timeout: 15_000 }),
            page.getByRole("button", { name: /^verifiëren$/i }).click(),
        ]);

    }




    // 4) Dashboard check

}
