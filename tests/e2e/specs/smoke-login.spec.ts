import { expect, test } from '@playwright/test';

test('smoke: login page is reachable and has login fields', async ({ page }) => {
    const res = await page.goto('/login');

    // Accept 200 (direct) or 302/301 (redirect) depending on middleware/app state
    const status = res?.status();
    expect([200, 301, 302]).toContain(status);

    // Gebruik stabiele selectors die vrijwel altijd kloppen in Laravel auth forms
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();

    // Submit button (kan "Login" / "Inloggen" zijn; daarom niet op tekst testen)
    await expect(page.locator('button[type="submit"]')).toBeVisible();
});
