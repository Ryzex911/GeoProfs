import { test, expect } from "@playwright/test";
import fs from "node:fs/promises";
import path from "node:path";
import { waitForOtpFromLaravelLog } from "../helpers/otpFromLaravelLog";

async function fillOtpDInputs(page: any, otp: string) {
    const dInputs = page.locator('input[name^="d"][maxlength="1"]');
    const count = await dInputs.count();
    if (count < 6) throw new Error(`Expected 6 OTP inputs (d1..d6), found ${count}`);

    for (let i = 0; i < 6; i++) {
        await dInputs.nth(i).fill(otp[i]);
    }

    // Zet ook hidden "code" indien aanwezig
    await page.evaluate((value: string) => {
        const el =
            (document.querySelector("#code") as HTMLInputElement | null) ||
            (document.querySelector('input[name="code"]') as HTMLInputElement | null);
        if (el) el.value = value;
    }, otp);
}
async function loginWith2FA(page: any) {
    const logPath = path.join(process.cwd(), "storage", "logs", "laravel.log");

    let fromSize = 0;
    try {
        fromSize = (await fs.stat(logPath)).size;
    } catch {
        fromSize = 0;
    }

    await page.goto("/login");
    await page.locator('input[name="email"]').fill(process.env.E2E_EMAIL!);
    await page.locator('input[name="password"]').fill(process.env.E2E_PASSWORD!);
    await page.locator('button[type="submit"]').first().click();

    const d1 = page.locator('input[name="d1"]');

    await Promise.race([
        page.waitForURL(/dashboard/i, { timeout: 10_000 }).catch(() => null),
        d1.waitFor({ state: "visible", timeout: 10_000 }).catch(() => null),
    ]);

    if (await d1.isVisible().catch(() => false)) {
        const otp = await waitForOtpFromLaravelLog({ fromSize });
        await fillOtpDInputs(page, otp);
        await page.getByRole("button", { name: /verifiëren/i }).click();
    }

    await expect(page).toHaveURL(/dashboard/i);
}
test("E2E: login + email 2FA -> dashboard (and continue)", async ({ page }) => {
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

    // 2) Of direct dashboard, of 2FA scherm
    const d1 = page.locator('input[name="d1"]');

    // Wacht totdat één van beide “gebeurt”
    await Promise.race([
        page.waitForURL(/dashboard/i, { timeout: 10_000 }).catch(() => null),
        d1.waitFor({ state: "visible", timeout: 10_000 }).catch(() => null),
    ]);

    // 3) Als 2FA zichtbaar is: OTP ophalen en verifiëren
    if (await d1.isVisible().catch(() => false)) {
        const otp = await waitForOtpFromLaravelLog({ fromSize });
        await fillOtpDInputs(page, otp);

        // Op jouw 2FA scherm zijn er 2 submit knoppen → kies expliciet Verifiëren
        await page.getByRole("button", { name: /verifiëren/i }).click();
    }

    // 4) Nu MOET je op dashboard zitten
    await expect(page).toHaveURL(/dashboard/i);

    // 5) Vanaf hier ga je verder met verlof aanvragen / checks
    // Voor nu: laat dashboard open staan zodat jij het ziet
    await page.pause();

    // Voorbeeld (pas routes/selectors aan naar jouw app):
    // await page.goto("/leave-requests/create");
    // await page.locator('select[name="leave_type_id"]').selectOption("1");
    // await page.locator('input[name="start_date"]').fill("2026-02-10");
    // await page.locator('input[name="end_date"]').fill("2026-02-12");
    // await page.locator('textarea[name="reason"]').fill("E2E test aanvraag");
    // await page.getByRole("button", { name: /aanvragen|indienen|submit/i }).click();
    // await expect(page.getByText(/succes|aangevraagd|opgeslagen/i)).toBeVisible();

});

test("manager keurt een aanvraag goed", async ({ page }) => {
    await loginWith2FA(page);

    await page.goto("/manager/requests");

    const approveBtn = page
        .locator('button:has-text("Goedkeuren")')
        .first();

    await expect(approveBtn).toBeVisible();

    await approveBtn.click();

    await expect(
        page.getByText("Goedgekeurd")
    ).toBeVisible({ timeout: 10000 });
});
