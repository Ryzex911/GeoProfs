import { expect, test } from '@playwright/test';
import { loginToDashboardWith2fa, maybePause } from '../helpers/auth2fa';
import { fillLeaveForm, openLeaveRequestForm, submitLeaveForm } from '../helpers/leaveUi';

test('Scenario 2 - Case 3: Pending aanvraag annuleren en daarna verwijderen (demo slow)', async ({ page }) => {
    test.setTimeout(120_000);

    const demoWait = async (ms: number) => {
        if (process.env.E2E_DEMO_SLOW === '1') await page.waitForTimeout(ms);
    };

    // 1) login + 2FA
    await loginToDashboardWith2fa(page, process.env.E2E_EMAIL!, process.env.E2E_PASSWORD!);
    await demoWait(1200);

    // 2) maak een geldige aanvraag (Pending)
    await openLeaveRequestForm(page);
    await demoWait(800);

    const reason = `E2E cancel+delete ${Date.now()}`;

    await fillLeaveForm(page, {
        typeLabel: process.env.E2E_LEAVE_TYPE_LABEL ?? 'Vakantie',
        startDay: 21,
        endDay: 22,
        remark: reason,
    });

    // Laat ingevulde form even zien
    await demoWait(1500);

    await submitLeaveForm(page);

    // Wacht op succes toast als die komt (niet altijd nodig)
    await page
        .getByText(/verlofverzoek is verzonden/i)
        .waitFor({ timeout: 15_000 })
        .catch(() => null);
    await demoWait(1500);

    // 3) terug naar dashboard
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/dashboard/i);
    await demoWait(1000);

    // 4) mijn verzoeken (leave-requests)
    await page.getByRole('button', { name: /mijn verlof verzoek/i }).click();
    await expect(page).toHaveURL(/leave-requests/i);
    await demoWait(1000);

    // 5) vind de rij op reason
    const row = page.locator('table tbody tr', { hasText: reason }).first();
    await expect(row).toBeVisible();
    await demoWait(1000);

    // 6) Annuleren + confirm (popup zichtbaar houden)
    page.once('dialog', async (d) => {
        if (process.env.E2E_DEMO_SLOW === '1') {
            await new Promise((r) => setTimeout(r, 2500)); // popup 2.5s zichtbaar
        }
        await d.accept();
    });

    await row.getByRole('button', { name: /annuleren/i }).click();

    await expect(row).toContainText(/canceled|geannuleerd/i);
    await demoWait(1200);

    // 7) Verwijderen + confirm (popup zichtbaar houden)
    page.once('dialog', async (d) => {
        if (process.env.E2E_DEMO_SLOW === '1') {
            await new Promise((r) => setTimeout(r, 2500));
        }
        await d.accept();
    });

    await row.getByRole('button', { name: /verwijderen/i }).click();

    // 8) bewijs: rij weg
    await expect(page.locator('table tbody tr', { hasText: reason })).toHaveCount(0);
    await demoWait(1000);

    await maybePause(page);
});
