import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/e2e',
    use: {
        headless: false,
        baseURL: 'http://localhost/login'
    }
});
