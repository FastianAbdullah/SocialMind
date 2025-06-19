import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ command }) => {
    const isProduction = command === 'build';
    
    return {
        plugins: [
            laravel({
                input: [
                    'resources/js/app.js',
                    'resources/js/legacy/plugins.js',
                    'resources/js/legacy/app.js',
                    'resources/js/legacy/animatedt-title.js',
                    //Dashboard JS files.
                    // latest jquery
                    'resources/js/legacy/jquery.min.js',
                    // Bootstrap js
                    'resources/js/bootstrap.js',
                    'resources/js/legacy/notify/bootstrap-notify.min.js',
                    'resources/js/legacy/bootstrap/bootstrap.bundle.min.js',
                    // Feather Icon
                    'resources/js/legacy/icons/feather-icon/feather.min.js',
                    'resources/js/legacy/icons/feather-icon/feather-icon.js',
                    // ScrollBar Js
                    'resources/js/legacy/scrollbar/simplebar.js',
                    'resources/js/legacy/scrollbar/custom.js',
                    // Sidebar Jquery
                    'resources/js/legacy/config.js',
                    // Plugins JS start
                    'resources/js/legacy/sidebar-menu.js',
                    'resources/js/legacy/sidebar-pin.js',
                    'resources/js/legacy/slick/slick.min.js',
                    'resources/js/legacy/slick/slick.js',
                    'resources/js/legacy/header-slick.js',
                    'resources/js/legacy/chart/apex-chart/apex-chart.js',
                    'resources/js/legacy/chart/apex-chart/stock-prices.js',
                    'resources/js/legacy/chart/apex-chart/moment.min.js',
                    'resources/js/legacy/notify/bootstrap-notify.min.js',
                    // Theme Js.
                    'resources/js/legacy/script.js',
        
                  ],
        
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
                '@vendors': '/resources/css/vendors',
                'vue': 'vue/dist/vue.esm-bundler.js'
            },
        },
        build: {
            manifest: true,
            outDir: 'public/build',
            emptyOutDir: true,
            chunkSizeWarningLimit: 1600,
            rollupOptions: {
                output: {
                    manualChunks: {
                        vendor: ['vue', 'glightbox', 'bootstrap', 'jquery'],
                        gsap: ['gsap', 'gsap/ScrollTrigger', 'gsap/SplitText'],
                        utils: ['axios', 'pinia', 'vue-router']
                    }
                }
            }
        },
        server: isProduction ? {} : {
            hmr: {
                host: 'localhost'
            },
            host: '0.0.0.0',
            port: 5173
        },
        optimizeDeps: {
            include: ['vue', 'jquery', 'bootstrap', 'gsap']
        }
    }
});
