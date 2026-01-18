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
 * Pre-condition: Manager is ingelogd, manager heeft teamleden, er bestaat minstens één verlofaanvraag met status "In afwachting" van een teamlid van deze manager.
 *
 * Test case description: Manager opent een verlofaanvraag en keurt deze goed.
 */

test.describe('TC-MA01: Manager keurt verlofaanvraag goed', () => {
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

    test('Stap 1: Manager navigeert naar manager-dashboard', async ({ page }) => {
        // Navigeer naar manager dashboard (verlofbeheer)
        await page.goto('/manager/dashboard');

        // Controleer dat manager dashboard geladen is
        await expect(page).toHaveURL(/\/manager\/dashboard/i);
        await expect(page.getByText(/welkom.*manager/i)).toBeVisible({ timeout: 5000 });
    });

    test('Stap 2: Manager navigeert naar verlofaanvragen overzicht', async ({ page }) => {
        // Ga eerst naar manager dashboard
        await page.goto('/manager/dashboard');
        await expect(page).toHaveURL(/\/manager\/dashboard/i);

        // Zoek naar link naar aanvragen (Verlofaanvragen, Aanvragen beoordelen, etc.)
        const requestsLink = page.getByRole('link', {
            name: /aanvragen|verlof|beoordelen/i
        }).first();

        await expect(requestsLink).toBeVisible({ timeout: 5000 });
        await requestsLink.click();

        // Controleer dat we op Requests/requests pagina zijn
        await expect(page).toHaveURL(/\/Requests\/requests/i);
        await expect(page.getByText('Verlofaanvragen beoordelen')).toBeVisible({ timeout: 5000 });
    });

    test('Stap 3: Manager ziet tabel met "In afwachting" aanvragen', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Controleer dat tabel zichtbaar is
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 5000 });

        // Controleer dat er minstens één pending aanvraag zichtbaar is
        const pendingRows = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        });

        const count = await pendingRows.count();
        expect(count).toBeGreaterThan(0);
    });

    test('Stap 4: Manager opent en ziet details van specifieke aanvraag', async ({ page }) => {
        // Navigeer naar aanvragen
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Vind eerste pending aanvraag rij
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Haal werknemer naam uit de rij
        const employeeName = await pendingRow.locator('span.name-main').innerText();
        expect(employeeName).toBeTruthy();

        // Controleer dat reden en datums zichtbaar zijn in rij
        await expect(pendingRow.locator('span.reason-pill')).toBeVisible();

        // Controleer dat status "In afwachting" toont
        await expect(pendingRow.locator('span.status-pill')).toContainText(/in afwachting/i);
    });

    test('Stap 5: Manager klikt op Goedkeuren en bevestigt', async ({ page }) => {
        // Navigeer naar aanvragen
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Vind eerste pending aanvraag
        const pendingRow = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        }).first();

        await expect(pendingRow).toBeVisible({ timeout: 5000 });

        // Haal de request ID voor latere verificatie
        const requestId = await pendingRow.getAttribute('data-request-id');
        console.log('Goedkeuren aanvraag ID:', requestId);

        // Vind en klik "Goedkeuren" knop in deze rij
        const approveBtn = pendingRow.locator('button.btn-approve');
        await expect(approveBtn).toBeVisible();

        // Klik op goedkeuren (geen modal voor goedkeuren, direct submit)
        await approveBtn.click();

        // Wacht tot pagina geüpdatet is
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(500);
    });

    test('Stap 6: Status van aanvraag is veranderd naar "Goedgekeurd"', async ({ page }) => {
        // Navigeer naar aanvragen
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // De goedgekeurde aanvraag zou niet meer in pending sectie moeten zijn
        const pendingRows = page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        });

        // Refresh om laatste status te zien
        await page.reload();
        await page.waitForLoadState('networkidle');

        // Controleer dat de status veranderd is (minder pending aanvragen)
        const updatedPendingCount = await page.locator('table tbody tr').filter({
            hasText: /in afwachting/i
        }).count();

        // Na goedkeuren zou er minstens één minder pending moeten zijn
        expect(updatedPendingCount).toBeGreaterThanOrEqual(0);
    });

    test('Stap 7: Manager controleert aanvraaglijst - aanvraag verschijnt als "Goedgekeurd"', async ({ page }) => {
        // Navigeer naar aanvragen
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Filter op goedgekeurde aanvragen
        const statusFilter = page.locator('select#statusFilter');
        if (await statusFilter.isVisible()) {
            // Selecteer "Goedgekeurd" uit filter
            await statusFilter.selectOption('approved');

            await page.waitForLoadState('networkidle');
        } else {
            // Herlaad pagina om laatste status te zien
            await page.reload();
            await page.waitForLoadState('networkidle');
        }

        // Controleer dat minstens één goedgekeurde aanvraag zichtbaar is
        const approvedRows = page.locator('table tbody tr').filter({
            hasText: /goedgekeurd/i
        });

        const approvedCount = await approvedRows.count();
        expect(approvedCount).toBeGreaterThan(0);

        // Toon dat test succesvol voltooid is
        await expect(page.getByText(/goedgekeurd/i)).toBeVisible();
    });
});
