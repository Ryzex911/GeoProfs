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
 * TC-MA01: Manager keurt verlofaanvraag goed
 *
 * Pre-condition: Ingelogd als Manager — URL: http://127.0.0.1:8000/Requests/requests
 *
 * Test case description: Manager opent een verlofaanvraag en keurt deze goed.
 */

test.describe('TC-MA01: Manager keurt verlofaanvraag goed', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('/login');

        await page.locator('input[name="email"]').fill('Tmmpsacc@outlook.com');
        await page.locator('input[name="password"]').fill('1234567890');
        const loginBtn = page.getByRole('button', { name: /inloggen|login/i });
        (await loginBtn.count()) ? await loginBtn.click() : await page.locator('button[type="submit"]').click();

        await page.waitForLoadState('networkidle');

        // 2FA flow
        if (/\/2fa($|[/?#])/.test(page.url())) {
            // mini-wacht zodat je app de mail verstuurt
            await page.waitForTimeout(2000);

            const code = await fetchLatest2faCode({ timeoutMs: 60000 });
            const codeInput = page.locator('input[name="code"], input[name="two_factor_code"], input#code, input[type="tel"]');
            await codeInput.first().fill(code);

            const verifyBtn = page.getByRole('button', { name: /verifi|bevestig|verify|submit|send|doorgaan/i });
            (await verifyBtn.count()) ? await verifyBtn.first().click() : await page.locator('button[type="submit"]').click();

            await page.waitForLoadState('networkidle');
        }

        await expect(page).toHaveURL(/\/dashboard/);
        await expect(page.getByText(/dashboard/i)).toBeVisible();
    });

    test('Stap 1: Manager navigeert naar aanvragenpagina', async ({ page }) => {
        // Navigeer naar aanvragenpagina
        await page.goto('/Requests/requests');

        // Controleer dat dashboard / lijst met aanvragen wordt geladen
        await expect(page).toHaveURL(/\/Requests\/requests/i);
        await expect(page.getByText('Verlofaanvragen beoordelen')).toBeVisible({ timeout: 5000 });

        // Controleer dat tabel zichtbaar is
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 5000 });
    });

    test('Stap 2: Manager opent verlofaanvraag', async ({ page }) => {
        // Navigeer naar aanvragenpagina
        await page.goto('/Requests/requests');
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Vind eerste aanvraag in tabel (bijv. met ID 2026-001 of eerste rij)
        const firstRow = page.locator('table tbody tr').first();
        await expect(firstRow).toBeVisible({ timeout: 5000 });

        // Controleer dat details zichtbaar zijn: naam, type, periode, reden, status
        await expect(firstRow.locator('span.name-main, .name-main')).toBeVisible();
        await expect(firstRow.locator('span.reason-pill, .reason-pill')).toBeVisible();
        await expect(firstRow.locator('span.status-pill, .status-pill')).toBeVisible();

        // Controleer dat periode/datum zichtbaar is
        await expect(firstRow.locator('td').filter({ hasText: /\d{1,2} \w{3,}/ })).toBeVisible();
    });

    test('Stap 3: Manager klikt op Goedkeuren', async ({ page }) => {
        // Navigeer naar aanvragenpagina
        await page.goto('/Requests/requests');
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Vind eerste pending aanvraag
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting|pending/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Vind en klik "Goedkeuren" knop
        const approveBtn = pendingRow.locator('button.btn-approve, form button[type="submit"]').filter({
            hasText: /goedkeuren/i
        });
        await expect(approveBtn).toBeVisible();

        // Klik op goedkeuren - verwacht confirm/modal indien aanwezig
        await approveBtn.click();

        // Wacht even voor mogelijke modal
        await page.waitForTimeout(1000);
    });

    test('Stap 4: Manager bevestigt goedkeuring', async ({ page }) => {
        // Navigeer naar aanvragenpagina
        await page.goto('/Requests/requests');
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Vind eerste pending aanvraag
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting|pending/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Klik goedkeuren
        const approveBtn = pendingRow.locator('button.btn-approve, form button[type="submit"]').filter({
            hasText: /goedkeuren/i
        });
        await approveBtn.click();

        // Wacht tot actie voltooid is
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(500);

        // Controleer dat status veranderd is naar Goedgekeurd
        // Refresh pagina om status update te zien
        await page.reload();
        await page.waitForLoadState('networkidle');

        // Controleer dat de aanvraag nu goedgekeurd is
        const approvedRows = page.locator('table tbody tr').filter({
            hasText: /goedgekeurd|approved/i
        });

        const approvedCount = await approvedRows.count();
        expect(approvedCount).toBeGreaterThan(0);
    });

    test('Stap 5: Notificaties worden verzonden', async ({ page }) => {
        // Dit is moeilijk te testen in E2E zonder toegang tot email systeem
        // Voor nu, controleer dat de actie succesvol was door status verandering
        await page.goto('/Requests/requests');
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Controleer dat er goedgekeurde aanvragen zijn
        await expect(page.getByText(/goedgekeurd|approved/i)).toBeVisible();

        // In praktijk zouden notificaties naar medewerker en admin gestuurd worden
        // Dit vereist integratie met email service voor volledige test
    });

    test('Stap 6: Manager controleert overzicht', async ({ page }) => {
        // Navigeer naar aanvragenpagina
        await page.goto('/Requests/requests');
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Filter op status Goedgekeurd
        const statusFilter = page.locator('select#statusFilter');
        if (await statusFilter.isVisible()) {
            // Selecteer "Goedgekeurd" of "approved"
            await statusFilter.selectOption('approved');

            await page.waitForLoadState('networkidle');
        } else {
            // Herlaad pagina
            await page.reload();
            await page.waitForLoadState('networkidle');
        }

        // Controleer dat aanvraag nu zichtbaar is als Goedgekeurd
        const approvedRows = page.locator('table tbody tr').filter({
            hasText: /goedgekeurd|approved/i
        });

        const approvedCount = await approvedRows.count();
        expect(approvedCount).toBeGreaterThan(0);

        // Toon dat test succesvol is
        await expect(page.getByText(/goedgekeurd|approved/i)).toBeVisible();
    });
});
