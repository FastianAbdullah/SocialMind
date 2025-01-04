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
            'resources/js/legacy/jquery.min.js',
            'resources/js/legacy/bootstrap/bootstrap.bundle.min.js',
            'resources/js/legacy/icons/feather-icon/feather.min.js',
            'resources/js/legacy/icons/feather-icon/feather-icon.js',
            'resources/js/legacy/scrollbar/simplebar.js',
            'resources/js/legacy/scrollbar/custom.js',
            'resources/js/legacy/config.js',
            'resources/js/legacy/sidebar-menu.js',
            'resources/js/legacy/sidebar-pin.js',
            'resources/js/legacy/slick/slick.min.js',
            'resources/js/legacy/slick/slick.js',
            'resources/js/legacy/header-slick.js',
            'resources/js/legacy/chart/apex-chart/apex-chart.js',
            'resources/js/legacy/chart/apex-chart/stock-prices.js',
            'resources/js/legacy/range-slider/rSlider.min.js',
            'resources/js/legacy/rangeslider/rangeslider.js',
            'resources/js/legacy/prism/prism.min.js',
            'resources/js/legacy/clipboard/clipboard.min.js',
            'resources/js/legacy/counter/jquery.waypoints.min.js',
            'resources/js/legacy/counter/jquery.counterup.min.js',
            'resources/js/legacy/counter/counter-custom.js',
            'resources/js/legacy/custom-card/custom-card.js',
            'resources/js/legacy/calendar/fullcalender.js',
            'resources/js/legacy/calendar/custom-calendar.js',
            'resources/js/legacy/dashboard/dashboard_2.js',
            'resources/js/legacy/animation/wow/wow.min.js',
            'resources/js/legacy/script.js',
            'resources/js/legacy/theme-customizer/customizer.js'
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



