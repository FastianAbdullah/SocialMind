import { createRouter, createWebHistory } from 'vue-router';
import Index from './pages/Index.vue';
import Login from './pages/Login.vue';
import Register from './pages/Register.vue';
import Pricing from './pages/Pricing.vue';
import Privacy from './pages/Privacy.vue';
import Terms from './pages/Terms.vue';
import Dashboard from './pages/Dashboard.vue';
import CreatePost from './components/CreatePost.vue';
import SocialLink from './components/SocialLink.vue';

const routes = [
  { path: '/', component: Index, meta: { requiresAuth: false } },
  { path: '/login', component: Login, meta: { requiresAuth: false }},
  { path: '/register', component: Register, meta: { requiresAuth: false } },
  { path: '/pricing', component: Pricing, meta: { requiresAuth: false } },
  { path: '/privacy', component: Privacy, meta: { requiresAuth: false } },
  { path: '/terms', component: Terms, meta: { requiresAuth: false }},
  { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true }},
  { path:'/create-post', component: CreatePost, meta: { requiresAuth: true }},,
  {
    path: '/social-links',
    component: SocialLink,
    meta: { requiresAuth: true },
    props: route => ({
      token: route.query.token,
      status: route.query.status,
      message: route.query.message
    })
  },
  // Updated Facebook callback route to work with Laravel
  {
    path: '/oauth/callback',
    beforeEnter: (to, from, next) => {
      const code = to.query.code;
      if (code) {
        // Redirect to Laravel backend with the code
        window.location.href = `/facebook/callback?code=${code}`;
      } else {
        next({
          path: '/social-links',
          query: { 
            status: 'error',
            message: 'Authentication failed: No code provided'
          }
        });
      }
    }
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guard
router.beforeEach((to, from, next) => {
  const isAuthenticated = !!localStorage.getItem('token');
  
  // Special handling for OAuth callback
  if (to.path === '/oauth/callback') {
    next(); // Allow callback to process
    return;
  }
  
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