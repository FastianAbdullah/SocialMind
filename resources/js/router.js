import { createRouter, createWebHistory } from 'vue-router';
import Index from './pages/Index.vue';
import Login from './pages/Login.vue';
import Register from './pages/Register.vue';
import Pricing from './pages/Pricing.vue';
import Privacy from './pages/Privacy.vue';
import Terms from './pages/Terms.vue';
import Dashboard from './pages/Dashboard.vue';
import CreatePost from './components/CreatePost.vue';
// Import other components

const routes = [
  { path: '/', component: Index, meta: { requiresAuth: false } },
  { path: '/login', component: Login, meta: { requiresAuth: false }},
  { path: '/register', component: Register, meta: { requiresAuth: false } },
  { path: '/pricing', component: Pricing, meta: { requiresAuth: false } },
  { path: '/privacy', component: Privacy, meta: { requiresAuth: false } },
  { path: '/terms', component: Terms, meta: { requiresAuth: false }},
  { path:'/dashboard', component: Dashboard, meta: { requiresAuth: true }},
  { path:'/create-post', component: CreatePost, meta: { requiresAuth: true }},
  // Define other routes
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token');
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    // Redirect to login if trying to access protected route without auth
    next('/login');
  } else if (to.path === '/login' && isAuthenticated) {
    // Redirect to dashboard if already authenticated
    next('/dashboard');
  } else {
    next();
  }
});

export default router;
