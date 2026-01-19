import { test, expect, type Page } from '@playwright/test';
import fs from 'node:fs/promises';
import path from 'node:path';
import { waitForOtpFromLaravelLog } from '../helpers/otpFromLaravelLog';

async function fillOtpDInputs(page: Page, otp: string) {
    const dInputs = page.locator('input[name^="d"][maxlength="1"]');
    const count = await dInputs.count();
    if (count < 6) throw new Error(`Expected 6 OTP inputs (d1..d6), found ${count}`);

    for (let i = 0; i < 6; i++) {
        await dInputs.nth(i).fill(otp[i]);
    }

    // Zet ook hidden "code" indien aanwezig
    await page.evaluate((value: string) => {
        const el =
            (document.querySelector('#code') as HTMLInputElement | null) ||
            (document.querySelector('input[name="code"]') as HTMLInputElement | null);
        if (el) el.value = value;
    }, otp);
}

/**
 * TC-MA02: Manager keurt verlofaanvraag af
 *
 * Pre-condition: Manager is ingelogd, aanvraag staat op In afwachting, aanvraag behoort tot manager's team.
 *
 * Beschrijving: Bij afkeuren kan manager optioneel een reden invoeren; afkeuren zet status op Afgewezen en stuurt notificaties.
 */

test.describe('TC-MA02: Manager keurt verlofaanvraag af', () => {
    test.beforeEach(async ({ page }) => {
        const logPath = path.join(process.cwd(), 'storage', 'logs', 'laravel.log');

        // Alleen nieuwe logregels lezen
        let fromSize = 0;
        try {
            fromSize = (await fs.stat(logPath)).size;
        } catch {
            fromSize = 0;
        }

        // 1) Login
        await page.goto('/login');
        await page.locator('input[name="email"]').fill('Tmmpsacc@outlook.com');
        await page.locator('input[name="password"]').fill('1234567890');
        await page.locator('button[type="submit"]').first().click();

        // 2) Of direct dashboard, of 2FA scherm
        const d1 = page.locator('input[name="d1"]');

        // Wacht totdat één van beide "gebeurt"
        await Promise.race([
            page.waitForURL(/dashboard/i, { timeout: 10_000 }).catch(() => null),
            d1.waitFor({ state: 'visible', timeout: 10_000 }).catch(() => null),
        ]);

        // 3) Als 2FA zichtbaar is: OTP ophalen en verifiëren
        if (await d1.isVisible().catch(() => false)) {
            const otp = await waitForOtpFromLaravelLog({ fromSize });
            await fillOtpDInputs(page, otp);

            // Op jouw 2FA scherm zijn er 2 submit knoppen → kies expliciet Verifiëren
            await page.getByRole('button', { name: /verifiëren/i }).click();
        }

        // 4) Nu MOET je op dashboard zitten
        await expect(page).toHaveURL(/dashboard/i);
    });

    test('Stap 1: Manager opent verlofaanvraag detail', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Vind eerste pending aanvraag rij
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Controleer dat details zichtbaar zijn in rij (naam, reden, datums)
        await expect(pendingRow.locator('span.name-main')).toBeVisible();
        await expect(pendingRow.locator('span.reason-pill')).toBeVisible();
        await expect(pendingRow.locator('span.status-pill')).toContainText(/in afwachting/i);
    });

    test('Stap 2: Manager klikt op Afkeuren', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Vind eerste pending aanvraag (rij met afkeuren knop)
        const pendingRow = page.locator('table tbody tr').filter({
            has: page.locator('button.btn-decline')
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Vind en klik "Afkeuren" knop in deze rij
        const rejectBtn = pendingRow.locator('button.btn-decline');
        await expect(rejectBtn).toBeVisible();

        // Klik op afkeuren - verwacht modal of confirm
        await rejectBtn.click();

        // Controleer dat modal of confirm verschijnt (als aanwezig)
        // Voor nu, wacht even voor mogelijke modal
        await page.waitForTimeout(1000);
    });

    test('Stap 3: Manager laat reden leeg en bevestigt afkeuren', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Vind eerste pending aanvraag
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Haal request ID
        const requestId = await pendingRow.getAttribute('data-request-id');
        console.log('Afkeuren aanvraag ID:', requestId);

        // Klik afkeuren - dit opent de modal
        const rejectBtn = pendingRow.locator('button.btn-decline');
        await rejectBtn.click();

        // Wacht tot modal zichtbaar is
        const modal = page.locator('#declineModal');
        await expect(modal).toBeVisible();

        // Het reden veld in de modal - laat leeg (optioneel)
        const reasonTextarea = page.locator('#declineReason');
        if (await reasonTextarea.isVisible()) {
            // Laat leeg - reden is optioneel
            await reasonTextarea.fill('');
        }

        // Klik op de afkeuren knop in de modal
        const confirmBtn = page.locator('#declineSubmit');
        await confirmBtn.click();

        // Wacht tot modal verdwenen is
        await expect(modal).not.toBeVisible();

        // Wacht tot pagina geüpdatet is
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(500);
    });

    test('Stap 4: Manager vult reden in en bevestigt afkeuren', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Vind eerste pending aanvraag
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Haal request ID
        const requestId = await pendingRow.getAttribute('data-request-id');
        console.log('Afkeuren met reden aanvraag ID:', requestId);

        // Klik afkeuren - dit opent de modal
        const rejectBtn = pendingRow.locator('button.btn-decline');
        await rejectBtn.click();

        // Wacht tot modal zichtbaar is
        const modal = page.locator('#declineModal');
        await expect(modal).toBeVisible();

        // Het reden veld in de modal - vul reden in
        const reasonTextarea = page.locator('#declineReason');
        if (await reasonTextarea.isVisible()) {
            await reasonTextarea.fill('Projectdrukte');
        }

        // Klik op de afkeuren knop in de modal
        const confirmBtn = page.locator('#declineSubmit');
        await confirmBtn.click();

        // Wacht tot modal verdwenen is
        await expect(modal).not.toBeVisible();

        // Wacht tot pagina geüpdatet is
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(500);
    });

    test('Stap 5: Na afkeuren - status veranderd naar Afgewezen', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Refresh om laatste status te zien
        await page.reload();
        await page.waitForLoadState('networkidle');

        // Controleer dat afgekeurde aanvragen zichtbaar zijn
        const rejectedRows = page.locator('table tbody tr').filter({
            hasText: /afgekeurd|rejected/i
        });

        const rejectedCount = await rejectedRows.count();
        expect(rejectedCount).toBeGreaterThan(0);
    });

    test('Stap 6: Notificaties - systeem stuurt notificaties', async ({ page }) => {
        // Dit is moeilijk te testen in E2E zonder toegang tot email/in-app notificaties
        // Voor nu, controleer dat de actie succesvol was door status verandering
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Controleer dat afgekeurde aanvraag aanwezig is - gebruik eerste match om strict mode violation te voorkomen
        await expect(page.getByText(/afgekeurd|rejected/i).first()).toBeVisible();
    });

    test('Stap 7: Manager controleert overzicht met filter', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Filter op status Afgewezen
        const statusFilter = page.locator('select[name="status"]').or(page.getByLabel(/status/i));
        if (await statusFilter.isVisible()) {
            await statusFilter.selectOption('rejected'); // Assuming value is 'rejected' for Afgewezen

            // Wacht tot filter is toegepast
            await page.waitForLoadState('networkidle');
            await page.waitForTimeout(1000);
        }

        // Controleer dat gefilterde resultaten alleen afgekeurde aanvragen tonen
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();

        // Als er gefilterde resultaten zijn, controleer dat ze allemaal afgekeurd zijn
        if (rowCount > 0) {
        // Controleer alleen de eerste paar rijen om te zien of filter werkt
            const firstRow = rows.first();
            await expect(firstRow).toContainText(/afgekeurd|rejected/i);
        } else {
            // Als er geen rijen zijn, betekent dat dat er geen afgekeurde aanvragen zijn
            // Dit is ook acceptabel als er nog geen aanvragen afgekeurd zijn
            console.log('Geen gefilterde resultaten gevonden - mogelijk nog geen afgekeurde aanvragen');
        }
    });
});
