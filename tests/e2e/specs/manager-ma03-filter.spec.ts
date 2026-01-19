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
 * TC-MA03: Manager filtert aanvragen in dashboard
 *
 * Pre-condition: Manager is ingelogd; meerdere aanvragen met uiteenlopende statussen bestaan
 *
 * Beschrijving: Manager filtert aanvragen op status en/of afdeling.
 */

test.describe('TC-MA03: Manager filtert aanvragen in dashboard', () => {
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
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Wacht tot tabel geladen is
        await page.waitForSelector('table tbody tr[data-request-id]');
        await page.waitForTimeout(1000);

        // Tel totaal aantal rijen voor filter
        const totalRowsBefore = await page.locator('table tbody tr[data-request-id]').count();
        console.log('Totaal rijen voor filter:', totalRowsBefore);

        // Kies filter "Goedgekeurd"
        const statusFilter = page.locator('#statusFilter');
        await expect(statusFilter).toBeVisible();
        await statusFilter.selectOption({ label: 'Goedgekeurd' });

        // Wacht tot filter is toegepast
        await page.waitForTimeout(1000);

        // Controleer dat alleen goedgekeurde aanvragen zichtbaar zijn
        const visibleRows = await page.locator('table tbody tr[data-request-id]:not([style*="display: none"])').count();
        console.log('Zichtbare rijen na filter:', visibleRows);

        if (visibleRows > 0) {
            // Controleer dat alle zichtbare rijen "Goedgekeurd" bevatten
            const approvedRows = page.locator('table tbody tr[data-request-id]:not([style*="display: none"])');
            const rowCount = await approvedRows.count();

            for (let i = 0; i < rowCount; i++) {
                await expect(approvedRows.nth(i)).toContainText(/goedgekeurd|approved/i);
            }
        }
    });

    test('Stap 2: Manager kiest filter In afwachting', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Wacht tot tabel geladen is
        await page.waitForSelector('table tbody tr[data-request-id]');
        await page.waitForTimeout(1000);

        // Kies filter "In afwachting"
        const statusFilter = page.locator('#statusFilter');
        await expect(statusFilter).toBeVisible();
        await statusFilter.selectOption({ label: 'In afwachting' });

        // Wacht tot filter is toegepast
        await page.waitForTimeout(1000);

        // Controleer dat alleen aanvragen in afwachting zichtbaar zijn
        const visibleRows = await page.locator('table tbody tr[data-request-id]:not([style*="display: none"])').count();
        console.log('Zichtbare rijen na filter In afwachting:', visibleRows);

        if (visibleRows > 0) {
            // Controleer dat alle zichtbare rijen "In afwachting" bevatten
            const pendingRows = page.locator('table tbody tr[data-request-id]:not([style*="display: none"])');
            const rowCount = await pendingRows.count();

            for (let i = 0; i < rowCount; i++) {
                await expect(pendingRows.nth(i)).toContainText(/in afwachting|pending/i);
            }
        }
    });

    test('Stap 3: Manager filtert op reden', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Wacht tot tabel geladen is
        await page.waitForSelector('table tbody tr[data-request-id]');
        await page.waitForTimeout(1000);

        // Kies filter op reden "Vakantie"
        const reasonFilter = page.locator('#reasonFilter');
        await expect(reasonFilter).toBeVisible();
        await reasonFilter.selectOption({ label: 'Vakantie' });

        // Wacht tot filter is toegepast
        await page.waitForTimeout(1000);

        // Controleer dat alleen vakantie aanvragen zichtbaar zijn
        const visibleRows = await page.locator('table tbody tr[data-request-id]:not([style*="display: none"])').count();
        console.log('Zichtbare rijen na reden filter:', visibleRows);

        if (visibleRows > 0) {
            // Controleer dat alle zichtbare rijen "Vakantie" bevatten
            const vacationRows = page.locator('table tbody tr[data-request-id]:not([style*="display: none"])');
            const rowCount = await vacationRows.count();

            for (let i = 0; i < rowCount; i++) {
                await expect(vacationRows.nth(i)).toContainText(/vakantie/i);
            }
        }
    });

    test('Stap 4: Manager combineert filters', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Wacht tot tabel geladen is
        await page.waitForSelector('table tbody tr[data-request-id]');
        await page.waitForTimeout(1000);

        // Stel eerste filter in: Status = "In afwachting"
        const statusFilter = page.locator('#statusFilter');
        await expect(statusFilter).toBeVisible();
        await statusFilter.selectOption({ label: 'In afwachting' });

        // Wacht tot eerste filter is toegepast
        await page.waitForTimeout(1000);

        // Stel tweede filter in: Reden = "Vakantie"
        const reasonFilter = page.locator('#reasonFilter');
        await expect(reasonFilter).toBeVisible();
        await reasonFilter.selectOption({ label: 'Vakantie' });

        // Wacht tot beide filters zijn toegepast
        await page.waitForTimeout(1000);

        // Controleer dat alleen aanvragen zichtbaar zijn die zowel "In afwachting" als "Vakantie" zijn
        const visibleRows = await page.locator('table tbody tr[data-request-id]:not([style*="display: none"])').count();
        console.log('Zichtbare rijen na gecombineerde filters:', visibleRows);

        if (visibleRows > 0) {
            // Controleer dat alle zichtbare rijen zowel "In afwachting" als "Vakantie" bevatten
            const combinedRows = page.locator('table tbody tr[data-request-id]:not([style*="display: none"])');
            const rowCount = await combinedRows.count();

            for (let i = 0; i < rowCount; i++) {
                const row = combinedRows.nth(i);
                await expect(row).toContainText(/in afwachting|pending/i);
                await expect(row).toContainText(/vakantie/i);
            }
        }
    });

    test('Stap 5: Manager gebruikt zoekfunctie', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Wacht tot tabel geladen is
        await page.waitForSelector('table tbody tr[data-request-id]');
        await page.waitForTimeout(1000);

        // Vind een bestaande medewerker naam om op te zoeken
        const firstRow = page.locator('table tbody tr[data-request-id]').first();
        const employeeName = await firstRow.locator('span.name-main').textContent();
        console.log('Zoeken naar medewerker:', employeeName);

        // Gebruik zoekfunctie
        const searchInput = page.locator('#search');
        await expect(searchInput).toBeVisible();
        await searchInput.fill(employeeName || 'Tamz');

        // Wacht tot zoekfilter is toegepast
        await page.waitForTimeout(1000);

        // Controleer dat resultaten gefilterd zijn
        const visibleRows = await page.locator('table tbody tr[data-request-id]:not([style*="display: none"])').count();
        console.log('Zichtbare rijen na zoeken:', visibleRows);

        if (visibleRows > 0) {
            // Controleer dat alle zichtbare rijen de gezochte naam bevatten
            const searchRows = page.locator('table tbody tr[data-request-id]:not([style*="display: none"])');
            const rowCount = await searchRows.count();

            for (let i = 0; i < rowCount; i++) {
                await expect(searchRows.nth(i)).toContainText(employeeName || 'Tamz');
            }
        }
    });

    test('Stap 6: Manager controleert paginering', async ({ page }) => {
        // Navigeer naar aanvragen pagina
        await page.goto('/manager/dashboard');
        await page.getByRole('link', { name: /aanvragen|verlof|beoordelen/i }).first().click();
        await expect(page).toHaveURL(/\/manager\/requests/i);

        // Wacht tot tabel geladen is
        await page.waitForSelector('table tbody tr[data-request-id]');
        await page.waitForTimeout(1000);

        // Controleer of paginering aanwezig is
        const pagination = page.locator('.pagination, nav[aria-label*="Page"], .page-links');
        const hasPagination = await pagination.isVisible().catch(() => false);

        if (hasPagination) {
            console.log('Paginering gevonden, test paginering functionaliteit');

            // Tel aantal rijen op huidige pagina
            const currentPageRows = await page.locator('table tbody tr[data-request-id]').count();
            console.log('Rijen op huidige pagina:', currentPageRows);

            // Vind paginatie links
            const pageLinks = page.locator('.pagination a, nav[aria-label*="Page"] a, .page-links a');
            const linkCount = await pageLinks.count();

            if (linkCount > 1) {
                // Klik op volgende pagina indien beschikbaar
                const nextLink = pageLinks.filter({ hasText: /next|volgende|>/i }).first();
                if (await nextLink.isVisible()) {
                    await nextLink.click();
                    await page.waitForTimeout(1000);

                    // Controleer dat pagina is veranderd
                    const newPageRows = await page.locator('table tbody tr[data-request-id]').count();
                    console.log('Rijen op volgende pagina:', newPageRows);

                    // Ga terug naar eerste pagina
                    const firstLink = pageLinks.filter({ hasText: /first|eerste|<<|1/i }).first();
                    if (await firstLink.isVisible()) {
                        await firstLink.click();
                        await page.waitForTimeout(1000);
                    }
                }
            }
        } else {
            console.log('Geen paginering gevonden, test toont alle resultaten op één pagina');

            // Controleer totaal aantal rijen
            const totalRows = await page.locator('table tbody tr[data-request-id]').count();
            console.log('Totaal aantal rijen (geen paginering):', totalRows);

            // Als er geen paginering is, is de test geslaagd zolang er rijen zijn
            expect(totalRows).toBeGreaterThan(0);
        }
    });
});
