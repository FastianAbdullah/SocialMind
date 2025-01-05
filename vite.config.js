import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
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
            'resources/js/chart/apex-chart/moment.min.js',
            'resources/js/notify/bootstrap-notify.min.js',
            // Theme Js.
            'resources/js/script.js',

          ],
          refresh: true,
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
      },
  },
});



