import { test } from '@playwright/test';
import { loginWithWrong2fa, maybePause } from '../helpers/auth2fa';

test('Scenario 1 - Case 4: verkeerde 2FA code -> foutmelding', async ({ page }) => {
    await loginWithWrong2fa(page, process.env.E2E_EMAIL!, process.env.E2E_PASSWORD!);
    await maybePause(page);
});
