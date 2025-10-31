import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    expect: { timeout: 10_000 },

    use: {
        baseURL: 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
    },

    // hou het even simpel: alleen chromium
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],

    // start je Laravel app automatisch
    webServer: {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000',
        reuseExistingServer: true,
        timeout: 120_000,
    },
});
