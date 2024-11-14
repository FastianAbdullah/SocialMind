import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
  const publicPath = mode === 'production'
    ? 'https://socialmind.discountroof.co.uk'
    : '/';

  return {
    plugins: [
      laravel({
        input: [
          'resources/js/app.js',
          'resources/js/legacy/plugins.js',
          'resources/js/legacy/app.js',
          'resources/js/legacy/animatedt-title.js',
        ],
        refresh: true,
      }),
      vue(),
    ],
    base: publicPath,
};
});