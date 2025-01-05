import { createRouter, createWebHistory } from 'vue-router';
import Index from './pages/Index.vue';
import Login from './pages/Login.vue';
import Register from './pages/Register.vue';
import Pricing from './pages/Pricing.vue';
import Privacy from './pages/Privacy.vue';
import Terms from './pages/Terms.vue';
import Dashboard from './pages/Dashboard.vue';
// Import other components

const routes = [
  { path: '/', component: Index },
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/pricing', component: Pricing },
  { path: '/privacy', component: Privacy },
  { path: '/terms', component: Terms },
  { path:'/dashboard', component: Dashboard}
  // Define other routes
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
