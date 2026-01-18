import { defineConfig, devices } from "@playwright/test";
import dotenv from "dotenv";
dotenv.config({ path: ".env.e2e" });

export default defineConfig({
    testDir: './tests/e2e/specs',
    timeout: 30_000,
    expect: { timeout: 10_000 },

    use: {
        baseURL: process.env.E2E_BASE_URL ?? 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
    },

    // simpel: alleen chromium
    projects: [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }],

    // start Laravel automatisch
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000',
        reuseExistingServer: true,
        timeout: 120_000,
        env: {
            ...process.env,
            MAIL_MAILER: 'log',
            QUEUE_CONNECTION: 'sync',
        },
    },
});
