import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
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
        assetsDir: 'assets',
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
                },
                assetFileNames: (assetInfo) => {
                    let extType = assetInfo.name.split('.').at(1);
                    if (/png|jpe?g|svg|gif|tiff|bmp|ico/i.test(extType)) {
                        extType = 'img';
                    }
                    return `assets/${extType}/[name]-[hash][extname]`;
                },
            }
        }
    },
    server: {
        https: true,
        host: true,
        hmr: {
            host: 'discountable.co.uk',
            protocol: 'https'
        },
        port: 5173
    },
    optimizeDeps: {
        include: ['vue', 'glightbox']
    }
}); 