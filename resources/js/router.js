import { createRouter, createWebHistory } from 'vue-router';
import Index from './pages/Index.vue';
import Login from './pages/Login.vue';
import Register from './pages/Register.vue';
import Pricing from './pages/Pricing.vue';
import Privacy from './pages/Privacy.vue';
import Terms from './pages/Terms.vue';
import Dashboard from './pages/Dashboard.vue';
import CreatePost from './components/CreatePost.vue';
import HashtagFinder from './components/HashtagFinder.vue';
import DescriptionGenerator from './components/DescriptionGenerator.vue';
import SentimentAnalysis from './components/SentimentAnalysis.vue';
import BusinessPlanGenerator from './components/BusinessPlanGenerator.vue';
import AIAssistant from './components/AIAssistant.vue';


const routes = [
  { path: '/', component: Index, meta: { requiresAuth: false } },
  { path: '/login', component: Login, meta: { requiresAuth: false }},
  { path: '/register', component: Register, meta: { requiresAuth: false } },
  { path: '/pricing', component: Pricing, meta: { requiresAuth: false } },
  { path: '/privacy', component: Privacy, meta: { requiresAuth: false } },
  { path: '/terms', component: Terms, meta: { requiresAuth: false }},
  { path: '/dashboard', component: Dashboard, meta: { requiresAuth: true }},
  { path:'/create-post', component: CreatePost, meta: { requiresAuth: true }},
  { path: '/hashtags-finder', component: HashtagFinder, meta: { requiresAuth: true }},
  { path: '/description-generator', component: DescriptionGenerator, meta: { requiresAuth: true }},
  { path: '/sentiment-analysis', component: SentimentAnalysis, meta: { requiresAuth: true }},
  { path: '/business-plan-generator', component: BusinessPlanGenerator, meta: { requiresAuth: true }},
  { path: '/ai-assistant', component: AIAssistant, meta: { requiresAuth: true }},
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  linkActiveClass: 'active',
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
    document.title = to.meta.title || 'SocialMind';
    next();
  }
});

export default router;