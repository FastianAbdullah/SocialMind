import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import store from './store/index.js';

axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const app = createApp(App);
const pinia = createPinia();

// Add the persisted state plugin to Pinia
pinia.use(piniaPluginPersistedstate);

app.use(pinia);
app.use(router);
app.use(store);

// Add this near the beginning of your app initialization
document.addEventListener('DOMContentLoaded', function() {
  // Force light theme
  document.body.classList.remove('dark-only');
  document.body.classList.add('light-only');
  localStorage.setItem('theme-color', 'light');
  
  // Remove dark mode toggle if it exists
  const modeElement = document.querySelector('.mode');
  if (modeElement) {
    modeElement.parentNode.removeChild(modeElement);
  }
});

app.mount('#app');
