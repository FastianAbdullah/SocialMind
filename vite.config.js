import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ command }) => {
    const isProduction = command === 'build';
    
    return {
        plugins: [
            laravel({
                input: [
                    'resources/js/app.js',      // Main JS entry
                    'resources/js/legacy/app.js' // Legacy JS
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
            emptyOutDir: true,
            outDir: 'public/build',
            rollupOptions: {
                output: {
                    // Automatic vendor chunking
                    manualChunks(id) {
                        if (id.includes('node_modules')) {
                            return 'vendor';
                        }
                    }
                }
            }
        },
        server: {
            hmr: {
                host: 'localhost' // HMR only needed in development
            },
            port: 5173,
            https: false // Development server doesn't need HTTPS
        },
        define: {
            // Use standard HTTPS port in production (no port needed as 443 is default for HTTPS)
            'import.meta.env.VITE_APP_URL': JSON.stringify(
                isProduction ? 'https://discountable.co.uk' : 'http://localhost:5173'
            )
        }
    }
});