<template>
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <Loader v-show="isLoading"></Loader>
  <div v-show="!isLoading">
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <!-- Page Header Start-->
      <div class="page-header" style="margin-left: 220px; width: calc(100% - 220px);">
        <div class="header-wrapper row m-0">
          <form class="form-inline search-full col" action="#" method="get">
            <div class="form-group w-100">
              <div class="Typeahead Typeahead--twitterUsers">
                <div class="u-posRelative">
                  <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search Riho .." name="q" title="">
                  <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading... </span></div><i class="close-search" data-feather="x"></i>
                </div>
                <div class="Typeahead-menu"> </div>
              </div>
            </div>
          </form>
          <div class="header-logo-wrapper col-auto p-0">
            <div class="logo-wrapper"> 
              <router-link to="/dashboard">
                <img class="img-fluid for-light" src="../../../public/assets/images/logo/logo_dark.png" alt="logo-light"><img class="img-fluid for-dark" src="../../../public/assets/images/logo/logo.png" alt="logo-dark">
              </router-link>
            </div>
            <div class="toggle-sidebar"> 
              <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i>
            </div>
          </div>
          <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
            <div> <a class="toggle-sidebar" href="#"> <i class="iconly-Category icli"> </i></a>
              <div class="d-flex align-items-center gap-2 ">
                <h4 class="f-w-600">Welcome {{ user.name }}</h4><img class="mt-0" src="../../../public/assets/images/hand.gif" alt="hand-gif">
              </div>
            </div>
          </div>
          <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
              <li class="d-md-block d-none">
                <div class="form search-form mb-0">
                  <div class="input-group"><span class="input-icon">
                      <svg>
                        <use href="../../../public/assets/svg/icon-sprite.svg#search-header"></use>
                      </svg>
                      <input class="w-100" type="search" placeholder="Search"></span></div>
                </div>
              </li>
              <li>
                <div class="mode"><i class="moon" data-feather="moon"> </i></div>
              </li>
              <li class="profile-nav">
                <button @click="handleLogout" class="btn btn-pill btn-outline-primary btn-sm">Log Out</button>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- Page Header Ends -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <DashboardSidebar />
        <!-- Page Sidebar Ends-->
        <div class="page-body" style="margin-left: 220px; width: calc(100% - 220px);">
          <div class="container-fluid">
            <div class="page-title">
              <div class="row">
                <div class="col-6">
                  <h4>Social Links</h4>
                </div>
                <div class="col-6">
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid starts -->
          <div class="container-fluid">
            <div class="row">
              <div class="col-xl-6 box-col-7">
                <div class="card">
                  <div class="card-header sales-chart card-no-border pb-0">
                    <h4>Link Your Social Media Accounts</h4>
                  </div>
                  <div v-if="statusMessage" :class="['alert', `alert-${statusType}`, 'mt-3', 'fade show']" role="alert">
                    <div class="d-flex align-items-center">
                      <i :class="statusIcon" class="me-2"></i>
                      {{ statusMessage }}
                    </div>
                    <button type="button" class="btn-close" @click="clearStatus"></button>
                  </div>
                  <div class="card-body p-4">
                    <p class="mb-4">Connect your social profiles to start managing them in one place</p>
                    <div class="row g-3">
                      <div class="col-md-6">
                        <button 
                          class="w-100 btn" 
                          :class="connected.facebook ? 'btn-light' : 'btn-primary'"
                          @click="connectFacebook"
                          :disabled="connected.facebook"
                        >
                          <i class="fab fa-facebook me-2"></i>
                          {{ connected.facebook ? 'Facebook Connected' : 'Connect Facebook' }}
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button 
                          class="w-100 btn"
                          :class="connected.instagram ? 'btn-light' : 'btn-danger'"
                        >
                          <i class="fab fa-instagram me-2"></i>
                          {{ connected.instagram ? 'Instagram Connected' : 'Connect Instagram' }}
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button 
                          class="w-100 btn"
                          :class="connected.linkedin ? 'btn-light' : 'btn-info'" 
                          @click="connectLinkedin()"
                          :disabled="connected.linkedin"
                        >
                          <i class="fab fa-linkedin me-2"></i>
                          {{ connected.linkedin ? 'LinkedIn Connected' : 'Connect LinkedIn' }}
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button 
                          class="w-100 btn"
                          :class="connected.twitter ? 'btn-light' : 'btn-dark'"
                        >
                          <i class="fab fa-twitter me-2"></i>
                          {{ connected.twitter ? 'Twitter Connected' : 'Connect Twitter' }}
                        </button>
                      </div>
                    </div>
                    <div v-if="Object.values(connected).some(Boolean)" class="alert alert-success mt-4">
                      {{ connectedCount }} accounts successfully connected. You can now manage your social media presence from one dashboard.
                    </div>
                    <div v-if="error" class="alert alert-danger mt-3">
                      <strong>Error:</strong> {{ error }}
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xl-3 col-md-6 special-Offer-banner box-col-none">
                <div class="card">
                  <div class="special-Offer">
                    <div class="offer-contain">
                      <h4>Today’s Special Offer</h4>
                      <p class="mt-2 text-center">You can flat get 20% off on your next pro version if your sale break your last record.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Container-fluid Ends -->
        </div>
        <!-- footer start-->
        <footer class="footer" style="margin-left: 220px; width: calc(100% - 220px);">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-12 footer-copyright text-center">
                <p class="mb-0">Copyright 2024 © SocialMind. All rights reserved.</p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeMount } from 'vue';
import Loader from '../components/Loader.vue';
import { useDynamicResources } from '../composables/useDynamicResources.js';
import { useSocialMediaStore } from '../store/socialMediaStore.js';
import DashboardSidebar from '../components/DashboardSidebar.vue';
import { getAuthUrl } from '../services/SocialMediaAuthService.js';
import axios from 'axios';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

const store = useStore();
const router = useRouter();
const socialMediaStore = useSocialMediaStore();
const isLoading = ref(true);
const error = ref(null);
const user = ref(null);
const statusMessage = ref(null);
const statusType = ref('info');
const cssFiles = [
  'resources/css/vendors/bootstrap.css',
  'resources/css/style.css',
  'resources/css/color-1.css',
  'resources/css/responsive.css'
];

const JsFiles = [
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
  'resources/js/legacy/chart/apex-chart/moment.min.js',
  'resources/js/legacy/notify/bootstrap-notify.min.js',
  'resources/js/legacy/script.js'
]

const connected = computed(() => ({
    facebook: socialMediaStore.facebook.connected,
    instagram: socialMediaStore.instagram.connected,
    linkedin: socialMediaStore.linkedin.connected,
    twitter: false
}));

// Computes the Number of Connected Accounts.
const connectedCount = computed(() => 
  Object.values(connected.value).filter(Boolean).length
);

const statusIcon = computed(() => {
  switch (statusType.value) {
    case 'success':
      return 'fas fa-check-circle';
    case 'error':
      return 'fas fa-exclamation-circle';
    default:
      return 'fas fa-info-circle';
  }
});

// Handle Logout Logic.
const handleLogout = async () => {
  try {
    await store.dispatch('logout');
    await socialMediaStore.clearSocialMediaStore();
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
  }
};

// Facebook Connection Handler.
const connectFacebook = async () => {
    error.value = null;
    isLoading.value = true;
    
    try {
        console.log('Initiating Facebook connection...');
        
        // Add loading state before API call
        socialMediaStore.$patch({
            facebook: {
                ...socialMediaStore.facebook,
                connecting: true
            }
        });
        
        const authUrl = await getAuthUrl('facebook');
        console.log('Received auth URL:', authUrl);
        
        if (!authUrl) {
            throw new Error('No authentication URL received');
        }

        // Log before redirect
        console.log('Redirecting to:', authUrl);
        
        // Use a small delay to ensure logs are visible
        // Testing
        setTimeout(() => {
            window.location.href = authUrl;
        }, 100);
        
    } catch (err) {
        console.error('Connection error:', err);
        error.value = `Connection failed: ${err.message}`;
        
        // Reset connecting state
        socialMediaStore.$patch({
            facebook: {
                ...socialMediaStore.facebook,
                connecting: false
            }
        });
        
        // Show error notification
        if (window.$) {
            window.$.notify({
                title: 'Error',
                message: `Failed to connect to Facebook: ${err.message}`
            }, {
                type: 'danger'
            });
        }
    } finally {
        isLoading.value = false;
    }
};

// Linkedin Connection Handler.
const connectLinkedin = async () => {
    error.value = null;
    isLoading.value = true;
    
    try {
        console.log('Initiating Linkedin connection...');
        
        // Add loading state before API call
        socialMediaStore.$patch({
            linkedin: {
                ...socialMediaStore.linkedin,
                connecting: true
            }
        });
        
        const authUrl = await getAuthUrl('linkedin');
        console.log('Received auth URL:', authUrl);
        
        if (!authUrl) {
            throw new Error('No authentication URL received');
        }

        // Log before redirect
        console.log('Redirecting to:', authUrl);
        
        // Use a small delay to ensure logs are visible
        // Testing
        setTimeout(() => {
            window.location.href = authUrl;
        }, 100);
        
    } catch (err) {
        console.error('Connection error:', err);
        error.value = `Connection failed: ${err.message}`;
        
        // Reset connecting state
        socialMediaStore.$patch({
            linkedin: {
                ...socialMediaStore.linkedin,
                connecting: false
            }
        });
        
        // Show error notification
        if (window.$) {
            window.$.notify({
                title: 'Error',
                message: `Failed to connect to Linkedin: ${err.message}`
            }, {
                type: 'danger'
            });
        }
    } finally {
        isLoading.value = false;
    }
};

// Checks The Connection to Facebook on Mounting.
const checkFacebookConnection = async () => {
    try {
        const response = await axios.get('/facebook/check-connection');
        console.log("FACEBOOK RESPONSE: ",response.data);
        console.log("FACEBOOK STORE: ",socialMediaStore.facebook);
        if (response.data.connected) {
            socialMediaStore.$patch({
                facebook: {
                    connected: true,
                    connecting: false,
                    platformId: response.data.platform_id,
                    pages: response.data.pages
                }
            });
        }
    } catch (err) {
        console.error('Failed to check Facebook connection:', err);
        // If there's an error, assume not connected
        socialMediaStore.$patch({
            facebook: {
                connected: false,
                connecting: false,
                platformId: null,
                pages: []
            }
        });
    }
};

// Checks The Connection to Linkedin on Mounting.
const checkLinkedinConnection = async () => {
    try {
        const response = await axios.get('/linkedin/check-connection');
        if (response.data.connected) {
            socialMediaStore.setLinkedinConnection(response.data);
        }
    } catch (err) {
        console.error('Failed to check Linkedin connection:', err);
        // If there's an error, assume not connected
        socialMediaStore.clearLinkedinConnection();
    }
};

const clearStatus = () => {
    statusMessage.value = null;
    statusType.value = 'info';
};

const {removeDynamicCss, initializeCss, removeDynamicJs,initializeScripts } = useDynamicResources(isLoading,cssFiles,JsFiles);

onBeforeMount(async () => {
  // Get the user object
  const response = await axios.get('/user');
  user.value = response.data;
  console.log("USER: ",user.value);
});

onMounted(async () => {
    try {
        await removeDynamicCss();
        await removeDynamicJs();
        await initializeCss();
        await initializeScripts();
        
        // Initialize store from localStorage
        socialMediaStore.initializeFromStorage();
        
        // Check Facebook connection status
        await checkFacebookConnection();

        // Check Linkedin connection status
        await checkLinkedinConnection();

        // Handle URL parameters for status messages
        const urlParams = new URLSearchParams(window.location.search);
        console.log("URL PARAMS: ",urlParams);
        const status = urlParams.get('status');
        const message = urlParams.get('message');
        const platform = urlParams.get('platform');

        if (status && message) {
            statusType.value = status === 'success' ? 'success' : 'danger';
            statusMessage.value = decodeURIComponent(message);

            // Auto-hide message after 5 seconds
            setTimeout(() => {
                clearStatus();
            }, 5000);

            // If successful connection, update store
            if (status === 'success' && platform === 'facebook') {
                socialMediaStore.$patch({
                    facebook: {
                        connected: true,
                        connecting: false
                    }
                });
            }

            if (status === 'success' && platform === 'linkedin') {
                socialMediaStore.$patch({
                  linkedin: {
                    connected: true,
                    connecting: false
                  }
              });
            }

            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
    } catch (err) {
        console.error('Initialization error:', err);
        error.value = err.message;
    } finally {
        isLoading.value = false;
    }
});
</script>

<style scoped>
.btn {
  padding: 1rem;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn i {
  font-size: 1.2rem;
}

.btn-light {
  border: 1px solid #dee2e6;
}

.card {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.card-header {
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
  border-radius: 10px 10px 0 0;
}

.card-body {
  padding: 2rem;
}

.alert {
  border-radius: 10px;
}

.special-Offer {
  background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
  color: #fff;
  padding: 2rem;
  border-radius: 10px;
  text-align: center;
}

.special-Offer h4 {
  margin-bottom: 1rem;
}

.special-Offer p {
  margin-bottom: 0;
}

.alert {
    border-radius: 8px;
    margin-bottom: 1rem;
    position: relative;
}

.alert .btn-close {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}

.fade {
    transition: opacity 0.15s linear;
}

.fade.show {
    opacity: 1;
}

.page-header {
  margin-left: 220px;
  width: calc(100% - 220px);
 }
 
 .page-body {
  margin-left: 220px;
  width: calc(100% - 220px); 
 }
 
 .footer {
  margin-left: 220px;
  width: calc(100% - 220px);
 }
</style>