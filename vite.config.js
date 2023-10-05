import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/sass/form.scss',
                'resources/js/app.js',
                'resources/js/Users.js',
            ],
            refresh: true,
        }),
    ],
});
