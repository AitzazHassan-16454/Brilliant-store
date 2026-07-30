import tailwindcss from '@tailwindcss/vite';
import inertia from '@inertiajs/vite'
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/css/app.css'],
            refresh: true,
            detectTls: false,
        }),
        vue(),
        tailwindcss(),
        inertia(),
    ],
})