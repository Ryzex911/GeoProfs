import { test, expect } from '@playwright/test';
import { fetchLatest2faCode } from './helpers/fetchLatest2faCode.js';

test('kan inloggen + 2FA naar dashboard', async ({ page }) => {
    await page.goto('/login');

    await page.locator('input[name="email"]').fill('medewerker.e2e@example.test');
    await page.locator('input[name="password"]').fill('Secret123!');
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
