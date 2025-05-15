import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/legacy/app.js',
                'resources/css/fonts/ff-1.css',
                'resources/css/fonts/ff-3.css',
                'resources/css/fonts/bootstrap-icons.css',
                'resources/css/vendors/bootstrap.css',
                'resources/css/plugins.min.css',
                'resources/css/style.css',
                'resources/css/color-1.css',
                'resources/css/responsive.css'
            ],
            refresh: false // Disable HMR/refresh for production
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
    build: {
        manifest: true,
        outDir: 'public/build',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: [
                        'vue',
                        'glightbox',
                        'bootstrap',
                        'jquery'
                    ]
                }
            }
        }
    }
});