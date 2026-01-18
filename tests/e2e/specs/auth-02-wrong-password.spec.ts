import { expect, test } from '@playwright/test';
import { loginSubmit, maybePause } from '../helpers/auth2fa';

test('Scenario 1 - Case 2: verkeerd wachtwoord -> foutmelding', async ({ page }) => {
    await loginSubmit(page, process.env.E2E_EMAIL!, 'WrongPassword123!');

    await expect(page).toHaveURL(/\/login/i);
    await expect(page.locator('body')).toContainText(/ongeldig|incorrect|invalid|fout|mislukt/i);

    await maybePause(page);
});
