import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js', 'resources/js/legacy/app.js'],
            refresh: false
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '~': '/resources',
            '@css': '/resources/css',
            '@fonts': '/resources/css/fonts',
            '@vendors': '/resources/css/vendors'
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', 'glightbox', 'bootstrap', 'jquery']
                }
            }
        }
    }
});