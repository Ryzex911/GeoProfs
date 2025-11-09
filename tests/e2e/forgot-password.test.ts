import {expect, test} from '@playwright/test';
import {dbConnection} from './db-connection';

test('Forgot password', async ({page}) => {

    const email = 'osama.asmi04@gmail.com'
    const newPassword = 'newPassword321!';

    await page.goto('http://localhost/login')

    await page.click('text=Wachtwoord vergeten?')
    await expect(page).toHaveURL('http://localhost/forgot-password');


        await page.fill('input[name="email"]', email)
        await page.click('button[type="submit"]');

    await expect(page.getByText('Er is een resetlink naar je e-mailadres gestuurd.')).toBeVisible();

        await page.waitForTimeout(2000);

    const connection = await dbConnection();

    const [rows]: any = await connection.execute(
        'SELECT token FROM password_reset_tokens WHERE email = ? ORDER BY created_at DESC LIMIT 1',
        [email]
    );

    expect(rows.length).toBeGreaterThan(0)

    const token = rows[0].token
    console.log('Gebruikte token:', token);

    await page.goto(`http://localhost/reset-password/${token}?email=${email}`)
    await page.fill('input[name="password"]', newPassword);
    await page.fill('input[name="password_confirmation"]', newPassword);
    await page.click('button[type="submit"]');



    await connection.end();
    }
)
