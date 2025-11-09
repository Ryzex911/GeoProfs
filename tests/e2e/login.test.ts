import {expect, test} from '@playwright/test';


// eerste test: Chect of de inlog pagina laadt.
test('Login loading', async ({page}) => {
    await page.goto('http://localhost/login')
    await expect(page).toHaveTitle(/login/i);
})
