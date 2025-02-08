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
app.mount('#app');
