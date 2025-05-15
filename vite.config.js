import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/vendors/bootstrap.css',
                'resources/css/style.css',
                'resources/css/color-1.css',
                'resources/css/responsive.css',
                'resources/js/app.js',
                'resources/js/legacy/app.js'
            ],
            refresh: true,
            publicDirectory: 'public',
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
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': '/resources/js',
            '~': '/resources',
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: [
                        'vue',
                        'glightbox',
                        'bootstrap',
                        'jquery'
                    ],
                    legacy: [
                        'resources/js/legacy/app.js'
                    ]
                }
            }
        }
    },
    server: {
        hmr: {
            host: 'discountable.co.uk'
        },
        port: 5173
    },
    optimizeDeps: {
        include: ['vue', 'glightbox']
    }
}); 
