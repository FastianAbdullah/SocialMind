import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ command }) => {
    const isProduction = command === 'build';
    
    return {
        plugins: [
            laravel({
                input: [
                    'resources/js/app.js',      // Main JS entry (includes CSS imports)
                    'resources/js/legacy/app.js' // Legacy JS (if needed)
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
                        ]
                    }
                }
            }
        },
        server: {
            hmr: {
                host: isProduction ? 'discountable.co.uk' : 'localhost'
            },
            port: 5173,
            https: isProduction
        },
        define: {
            'import.meta.env.VITE_APP_URL': JSON.stringify(
                isProduction ? 'https://discountable.co.uk' : 'http://localhost:5173'
            )
        }
    }
});