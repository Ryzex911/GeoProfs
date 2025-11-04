// vite.config.js
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/main.jsx',        // <-- important
                'resources/css/login.css',
                'resources/css/reset-password.css',
            ],
            refresh: true,
        }),
    ],
})
