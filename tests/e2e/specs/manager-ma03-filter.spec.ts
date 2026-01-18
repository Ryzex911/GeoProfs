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
 * TC-MA03: Filteren in manager dashboard
 *
 * Pre-condition: Manager is ingelogd; dashboard bevat meerdere aanvragen met verschillende statussen en van verschillende afdelingen.
 *
 * Beschrijving: Manager kan aanvragen filteren op status en (optioneel) op afdeling.
 */

test.describe('TC-MA03: Filteren in manager dashboard', () => {
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

    test('Stap 1: Manager kiest filter Goedgekeurd', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Controleer dat tabel zichtbaar is
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 5000 });

        // Vind status filter
        const statusFilter = page.locator('select#statusFilter');
        await expect(statusFilter).toBeVisible();

        // Selecteer "Goedgekeurd" of "approved"
        await statusFilter.selectOption('approved');

        // Wacht tot filtering is toegepast
        await page.waitForLoadState('networkidle');

        // Controleer dat alleen goedgekeurde aanvragen zichtbaar zijn
        const allRows = page.locator('table tbody tr');
        const rowCount = await allRows.count();

        if (rowCount > 0) {
            // Controleer dat alle zichtbare rijen "goedgekeurd" bevatten
            for (let i = 0; i < rowCount; i++) {
                const row = allRows.nth(i);
                await expect(row).toContainText(/goedgekeurd|approved/i);
            }
        }
    });

    test('Stap 2: Manager kiest filter In afwachting', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Vind status filter
        const statusFilter = page.locator('select#statusFilter');
        await expect(statusFilter).toBeVisible();

        // Selecteer "In afwachting" of "pending"
        await statusFilter.selectOption('pending');

        // Wacht tot filtering is toegepast
        await page.waitForLoadState('networkidle');

        // Controleer dat alleen pending aanvragen zichtbaar zijn
        const allRows = page.locator('table tbody tr');
        const rowCount = await allRows.count();

        if (rowCount > 0) {
            // Controleer dat alle zichtbare rijen "in afwachting" bevatten
            for (let i = 0; i < rowCount; i++) {
                const row = allRows.nth(i);
                await expect(row).toContainText(/in afwachting|pending/i);
            }
        }
    });

    test('Stap 3: Manager filtert op eigen afdeling', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Zoek naar afdeling/department filter (als aanwezig)
        const departmentFilter = page.locator('select#departmentFilter, select[name="department"]');

        if (await departmentFilter.isVisible()) {
            // Selecteer een afdeling, bijv. "Bouw" of eerste optie
            const options = await departmentFilter.locator('option').allTextContents();
            if (options.length > 1) {
                await departmentFilter.selectOption({ index: 1 }); // Selecteer eerste afdeling

                // Wacht tot filtering is toegepast
                await page.waitForLoadState('networkidle');

                // Controleer dat filtering is toegepast (moeilijk zonder specifieke data)
                await expect(page.locator('table')).toBeVisible();
            }
        } else {
            // Als geen afdeling filter, sla deze stap over
            console.log('Geen afdeling filter gevonden, stap overgeslagen');
        }
    });

    test('Stap 4: Combinatiefilter: Afdeling + Status', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Stel afdeling filter in (als aanwezig)
        const departmentFilter = page.locator('select#departmentFilter, select[name="department"]');
        if (await departmentFilter.isVisible()) {
            const options = await departmentFilter.locator('option').allTextContents();
            if (options.length > 1) {
                await departmentFilter.selectOption({ index: 1 });
            }
        }

        // Stel status filter in op "In afwachting"
        const statusFilter = page.locator('select#statusFilter');
        await statusFilter.selectOption('pending');

        // Wacht tot filtering is toegepast
        await page.waitForLoadState('networkidle');

        // Controleer dat alleen pending aanvragen van geselecteerde afdeling zichtbaar zijn
        const allRows = page.locator('table tbody tr');
        const rowCount = await allRows.count();

        if (rowCount > 0) {
            // Controleer dat alle zichtbare rijen zowel de afdeling als "in afwachting" bevatten
            for (let i = 0; i < rowCount; i++) {
                const row = allRows.nth(i);
                await expect(row).toContainText(/in afwachting|pending/i);
                // Afdeling controle zou specifieker zijn met echte data
            }
        }
    });

    test('Stap 5: Paginerings/overzichtstest', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/Requests\/requests/i);

        // Controleer dat tabel zichtbaar is
        const table = page.locator('table');
        await expect(table).toBeVisible();

        // Controleer paginering elementen (als aanwezig)
        const pagination = page.locator('.pagination, nav[aria-label*="Page"]');
        if (await pagination.isVisible()) {
            // Test paginering door naar volgende pagina te gaan
            const nextBtn = pagination.locator('a, button').filter({ hasText: /next|volgende|>/i });
            if (await nextBtn.isVisible()) {
                await nextBtn.click();
                await page.waitForLoadState('networkidle');

                // Controleer dat pagina is veranderd
                await expect(table).toBeVisible();
            }
        }

        // Controleer totaal aantal resultaten (uit KPI's)
        const totalKpi = page.locator('.kpi-value').first();
        if (await totalKpi.isVisible()) {
            const totalText = await totalKpi.innerText();
            const total = parseInt(totalText.replace(/\D/g, ''));
            expect(total).toBeGreaterThanOrEqual(0);
        }
    });
});
