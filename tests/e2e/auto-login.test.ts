import {expect, test} from '@playwright/test';


test('Auto login', async ({page}) => {
    await page.goto('http://localhost/login')

    await page.fill('input[name="email"]', 'osama.asmi04@gmail.com');
    await page.fill('input[name="password"]', 'newPassword123!');

    await page.click('button[type="submit"]')

    await expect(page).toHaveURL('http://localhost/2fa')
})
