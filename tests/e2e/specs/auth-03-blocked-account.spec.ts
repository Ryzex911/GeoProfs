import { expect, test } from '@playwright/test';
import { loginSubmit, maybePause } from '../helpers/auth2fa';

test('Scenario 1 - Case 3: geblokkeerd account -> melding', async ({ page }) => {
    await loginSubmit(page, process.env.E2E_BLOCKED_EMAIL!, process.env.E2E_BLOCKED_PASSWORD!);

    await expect(page).toHaveURL(/\/login/i);
    await expect(page.locator('body')).toContainText(/geblokkeerd/i);

    await maybePause(page);
});
