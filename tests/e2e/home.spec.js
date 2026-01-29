import { test, expect } from '@playwright/test';

test('homepage laadt', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveTitle(/GeoProfs/);
});
