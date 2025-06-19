<!-- Header.vue -->
<template>
    <header class="header header--4 header--fixed1">
      <div class="container">
        <div class="row">
          <div class="col-12">
            <nav class="menu d-lg-flex justify-content-lg-between align-items-lg-center py-3 py-lg-0">
              <div class="d-flex align-items-center justify-content-between">
                <a href="index.html" class="logo link d-inline-flex align-items-center flex-shrink-0">
                  <img src="img/logo-light.png" alt="logo" class="img-fluid object-fit-contain" width="150" height="50">
                </a>
                <button class="menu-toggle w-15 h-15 p-0 border-0 lh-1 bg-primary-50 clr-neutral-100 transition rounded flex-shrink-0 d-lg-none order-sm-3 fs-24">
                  <i class="bi bi-list"></i>
                </button>
              </div>
  
              <div class="menu-nav d-flex align-items-lg-center flex-column flex-lg-row flex-grow-1 gap-4 pb-4 pb-lg-0 rounded">
                <ul class="list list-lg-row ms-lg-auto align-items-lg-center">
                  <li class="menu-list">
                    <RouterLink to="/" class="link menu-link">Home</RouterLink>
                  </li>
                  <li class="menu-list">
                    <RouterLink to="/pricing" class="link menu-link">Pricing</RouterLink>
                  </li>
                  <li class="menu-list">
                    <RouterLink to="/privacy" class="link menu-link">Privacy</RouterLink>
                  </li>
                  <li class="menu-list">
                    <RouterLink to="/terms" class="link menu-link">Terms</RouterLink>
                  </li>
                  <template v-if="!isAuthenticated">
                    <li class="menu-list">
                      <RouterLink to="/login">
                        <div class="link d-inline-flex align-items-center gap-2 py-2 px-3 rounded-1 bg-grad-6 clr-white fw-bold fs-14">
                          <span class="d-inline-block ff-3">Get Started</span>
                          <span class="d-inline-block fs-12">
                            <i class="bi bi-arrow-up-right"></i>
                          </span>
                        </div>
                      </RouterLink>
                    </li>
                  </template>
                  <template v-else>
                    <li class="menu-list">
                      <RouterLink to="/dashboard" class="link d-inline-flex align-items-center gap-2 py-2 px-3 rounded-1 bg-primary-key clr-white fw-bold fs-14 me-2">
                        <span class="d-inline-block ff-3">Dashboard</span>
                        <span class="d-inline-block fs-12">
                          <i class="bi bi-speedometer2"></i>
                        </span>
                      </RouterLink>
                    </li>
                    <li class="menu-list">
                      <a href="#" @click.prevent="handleLogout" class="link d-inline-flex align-items-center gap-2 py-2 px-3 rounded-1 bg-grad-6 clr-white fw-bold fs-14">
                        <span class="d-inline-block ff-3">Log Out</span>
                        <span class="d-inline-block fs-12">
                          <i class="bi bi-box-arrow-right"></i>
                        </span>
                      </a>
                    </li>
                  </template>
                </ul>
              </div>
            </nav>
          </div>
        </div>
      </div>
    </header>
</template>
  
<script setup>
import { ref, onMounted, computed } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';
import { useSocialMediaStore } from '../store/socialMediaStore';

const store = useStore();
const router = useRouter();
const socialMediaStore = useSocialMediaStore();
const isAuthenticated = ref(false);

// Check authentication on component mount
onMounted(async () => {
  try {
    // Try to validate the session
    isAuthenticated.value = await socialMediaStore.validateSession();
    // If not authenticated but Vuex still thinks we are, fix that
    if (!isAuthenticated.value && store.state.isLoggedIn) {
      
      await handleLogout();
    }
  } catch (error) {
    console.error('Authentication check failed:', error);
    isAuthenticated.value = false;
    await handleLogout();
  }
});

// Handle logout
const handleLogout = async () => {
  try {
    // Clear social media store
    socialMediaStore.clearSocialMediaStore();
    isAuthenticated.value = false;
    
    // Logout via Vuex
    await store.dispatch('logout');
    
    // Redirect to login
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
    
    // Force logout even if API call fails
    localStorage.removeItem('token');
    localStorage.removeItem('socialMediaState');
    store.commit('setLoggedIn', false);
    router.push('/login');
  }
};
</script>
  