import {expect, test} from "@playwright/test";
import {dbConnection} from './db-connection';

test('Login block', async ({page}) => {
    const connection = await dbConnection();

    const email = 'osama.asmi04@gmail.com';
    const wrongPass = 'Wrong1234';
    const goodPass = 'newPassword123!'

    // Long attempts opschonen om te testen
    await connection.execute('DELETE FROM login_attempts WHERE email_tried = ?', [email]);
    await connection.execute('UPDATE users SET lock_at = NULL WHERE email = ?', [email]);
    await connection.execute('UPDATE users SET attempts_cleared_at = NOW() WHERE email = ?', [email]);
    console.log('Unblock user automatically ');

    for (let i = 1; i <= 4; i++) {
        await page.goto('http://localhost/login');

        // vul inloggegevens in
        await page.fill('input[name="email"]', email);
        await page.fill('input[name="password"]', wrongPass);
        await page.click('button[type="submit"]');

        await page.waitForTimeout(1000);
        const isVisible = await page.locator('text=Onjuist e-mailadres of wachtwoord.').isVisible();

        console.log(`Attempt ${i}: Error visible =`, isVisible);

        // Controleer of er een nieuwe login_attempt in de database staat
        const [rows]: any = await connection.execute('SELECT COUNT(*) as count FROM login_attempts WHERE email_tried = ?', [email]);
        console.log(`Attempt ${i}: DB attempts: `, rows[0].count);
    }
    await page.goto('http://localhost/login')

    await page.fill('input[name="email"]', 'osama.asmi04@gmail.com');
    await page.fill('input[name="password"]', goodPass);
    await page.click('button[type="submit"]')

    await page.waitForTimeout(1000);

    await page.locator('text=Je account is geblokkeerd. Neem contact op met ICT.').isVisible();

    const [userRows]: any = await connection.execute(
        'SELECT lock_at FROM users WHERE email = ?',
        [email]
    );

    console.log('locked_at waarde:', userRows[0].lock_at);

    await connection.end();
})
