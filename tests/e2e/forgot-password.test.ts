import {expect, test} from '@playwright/test';
import mysql from 'mysql2/promise';

test('Forgot password', async ({page}) => {
        await page.goto('http://localhost/login')

        await page.click('text=Wachtwoord vergeten?')
        await expect(page).toHaveURL('http://localhost/forgot-password');

        const email = 'osama.asmi04@gmail.com'
        await page.fill('input[name="email"]', email)
        await page.click('button[type="submit"]');

        await expect(page.getByText('Er is een resetlink naar je e-mailadres gestuurd.')).toBeVisible();

        await page.waitForTimeout(2000);

        const connection = await mysql.createConnection({
            host: '127.0.0.1',
            user: 'sail',
            password: 'password',
            database: 'laravel',
        })
    }
)
