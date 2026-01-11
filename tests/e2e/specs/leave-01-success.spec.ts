import { expect, test } from '@playwright/test';
import { loginToDashboardWith2fa, maybePause } from '../helpers/auth2fa';
import { fillLeaveForm, getDashboardPendingCount, openLeaveRequestForm, submitLeaveForm } from '../helpers/leaveUi';

test('Scenario 2 - Case 1: goed verlof aanvragen', async ({ page }) => {
    test.setTimeout(120_000);

    await loginToDashboardWith2fa(page, process.env.E2E_EMAIL!, process.env.E2E_PASSWORD!);

    const before = await getDashboardPendingCount(page);

    await openLeaveRequestForm(page);

    await fillLeaveForm(page, {
        typeLabel: process.env.E2E_LEAVE_TYPE_LABEL ?? 'Vakantie',
        startDay: 21,
        endDay: 29,
        remark: `naar spanje ${Date.now()}`,
    });

    // demo: laat even zien dat alles gevuld is
    if (process.env.E2E_DEMO_SLOW === '1') await page.waitForTimeout(1500);

    await submitLeaveForm(page);

    // Wacht op success toast (bewijs + filmen)
    await expect(page.getByText(/verlofverzoek is verzonden/i)).toBeVisible();
    if (process.env.E2E_DEMO_SLOW === '1') await page.waitForTimeout(2000);

    const after = await getDashboardPendingCount(page);
    expect(after).toBe(before + 1);

    await maybePause(page);
});
