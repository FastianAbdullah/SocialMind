import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
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
                    ],
                    // Optional: Consider adding GSAP/Swiper to vendor chunk
                    // vendor: ['gsap', 'swiper', ...] 
                }
            }
        }
    },
    server: {
        hmr: {
            host: 'discountable.co.uk' // Ensure this matches your dev domain
        },
        port: 5173
    },
    optimizeDeps: {
        include: ['vue', 'glightbox']
    }
});