import { test, expect } from '@playwright/test';

test('health check', async ({ page }) => {
    await page.goto('http://127.0.0.1:8000');
    await expect(page).toHaveTitle(/./); // check dat er een title is
});
