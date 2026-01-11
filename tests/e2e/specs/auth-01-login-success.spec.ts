import { test } from '@playwright/test';
import { loginToDashboardWith2fa, maybePause } from '../helpers/auth2fa';

test('Scenario 1 - Case 1: succesvol login + 2FA -> dashboard', async ({ page }) => {
    await loginToDashboardWith2fa(page, process.env.E2E_EMAIL!, process.env.E2E_PASSWORD!);
    await maybePause(page); // zet E2E_DEMO_PAUSE=1 voor opnemen
});
