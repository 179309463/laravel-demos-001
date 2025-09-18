import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            output: {
                manualChunks: undefined,
            },
        },
        chunkSizeWarningLimit: 1600,
    },
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    resolve: {
        alias: {
            '~bootstrap': 'bootstrap',
        },
    },
});
