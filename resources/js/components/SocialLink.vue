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
      <div class="page-header">
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
            <div class="logo-wrapper"> <a href="index.html"><img class="img-fluid for-light" src="../../../public/assets/images/logo/logo_dark.png" alt="logo-light"><img class="img-fluid for-dark" src="../../../public/assets/images/logo/logo.png" alt="logo-dark"></a></div>
            <div class="toggle-sidebar"> <i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
          </div>
          <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
            <div> <a class="toggle-sidebar" href="#"> <i class="iconly-Category icli"> </i></a>
              <div class="d-flex align-items-center gap-2 ">
                <h4 class="f-w-600">Welcome Alex</h4><img class="mt-0" src="../../../public/assets/images/hand.gif" alt="hand-gif">
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
              <li class="profile-nav onhover-dropdown">
                <div class="media profile-media"><img class="b-r-10" src="../../../public/assets/images/dashboard/profile.png" alt="">
                  <div class="media-body d-xxl-block d-none box-col-none">
                    <div class="d-flex align-items-center gap-2"> <span>Alex Mora </span><i class="middle fa fa-angle-down"> </i></div>
                    <p class="mb-0 font-roboto">Admin</p>
                  </div>
                </div>
                <ul class="profile-dropdown onhover-show-div">
                  <li><a href="user-profile.html"><i data-feather="user"></i><span>My Profile</span></a></li>
                  <li> <a href="edit-profile.html"> <i data-feather="settings"></i><span>Settings</span></a></li>
                  <li><a class="btn btn-pill btn-outline-primary btn-sm" href="login.html">Log Out</a></li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- Page Header Ends -->
      <!-- Page Body Start-->
      <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <div class="sidebar-wrapper" data-layout="stroke-svg">
          <div class="logo-wrapper"><a href="index.html"><img class="img-fluid" src="../../../public/assets/images/logo/logo.png" alt=""></a>
            <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
          </div>
          <div class="logo-icon-wrapper"><a href="index.html"><img class="img-fluid" src="../../../public/assets/images/logo/logo-icon.png" alt=""></a></div>
          <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
              <ul class="sidebar-links" id="simple-bar">
                <li class="back-btn"><a href="index.html"><img class="img-fluid" src="../../../public/assets/images/logo/logo-icon.png" alt=""></a>
                  <div class="mobile-back text-end"><span>Back </span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                </li>
                <li class="sidebar-main-title">
                  <div><h6 class="lan-1">General</h6></div>
                </li>
                <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="/dashboard">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-home"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-home"></use>
                    </svg>
                    <span>Dashboard</span>
                  </a>
                </li>
                <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="/social-links">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-user"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-user"></use>
                    </svg>
                    <span>Social Links</span>
                  </a>
                </li>
                <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="/file-manager">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-file"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-file"></use>
                    </svg>
                    <span>File Manager</span>
                  </a>
                </li>
                <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="/kanban">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-board"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-board"></use>
                    </svg>
                    <span>Kanban Board</span>
                  </a>
                </li>
                <li class="sidebar-list">
                  <i class="fa fa-thumb-tack"></i>
                  <a class="sidebar-link sidebar-title link-nav" href="/calendar">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-calender"></use>
                    </svg>
                    <span>Calendar</span>
                  </a>
                </li>
              </ul>
              <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
            </div>
          </nav>
        </div>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
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
                          class="w-100 btn position-relative"
                          :class="{
                            'btn-light': connected.instagram,
                            'btn-danger': !connected.instagram,
                            'disabled': socialMediaStore.instagram.connecting || connected.instagram
                          }"
                          @click="handleConnect('instagram')"
                          :disabled="connected.instagram"
                        >
                          <div class="d-flex align-items-center justify-content-center">
                            <i class="fab fa-instagram me-2"></i>
                            <span v-if="!socialMediaStore.instagram.connecting">
                              {{ connected.instagram ? 'Instagram Connected' : 'Connect Instagram' }}
                            </span>
                            <span v-else>
                              <i class="fas fa-spinner fa-spin me-2"></i>
                              Connecting...
                            </span>
                          </div>
                          
                          <div v-if="connected.instagram" 
                               class="position-absolute top-0 end-0 p-2">
                            <i class="fas fa-times-circle text-danger cursor-pointer" 
                               @click.stop="disconnectInstagramAccount"
                               title="Disconnect Instagram">
                            </i>
                          </div>
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button 
                          class="w-100 btn"
                          :class="connected.linkedin ? 'btn-light' : 'btn-info'" 
                          @click="handleConnect('linkedin')"
                        >
                          <i class="fab fa-linkedin me-2"></i>
                          {{ connected.linkedin ? 'LinkedIn Connected' : 'Connect LinkedIn' }}
                        </button>
                      </div>
                      <div class="col-md-6">
                        <button 
                          class="w-100 btn"
                          :class="connected.twitter ? 'btn-light' : 'btn-dark'"
                          @click="handleConnect('twitter')"
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
        <footer class="footer">
          <div class="container-fluid">
            <div class="row">
              <div class="col-md-12 footer-copyright text-center">
                <p class="mb-0">Copyright 2024 © SocialMind </p>
              </div>
            </div>
          </div>
        </footer>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Loader from './Loader.vue';
import { useDynamicResources } from '../composables/useDynamicResources';
import { useSocialMediaStore } from '../stores/socialMediaStore';
import { 
    getAuthUrl, 
    handleAuthCallback, 
    getPages as getFacebookPages, 
    disconnectFacebook 
} from '../services/facebookService';
import axios from 'axios';
import { 
    getAuthUrl as getInstagramAuthUrl,
    disconnectInstagram,
    checkConnection as checkInstagramConnection
} from '../services/instagramService';

const socialMediaStore = useSocialMediaStore();
const isLoading = ref(true);
const error = ref(null);

// Update the connected computed property
const connected = computed(() => ({
    facebook: socialMediaStore.facebook.connected,
    instagram: socialMediaStore.instagram.connected,
    linkedin: false,
    twitter: false
}));

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
        
        const authUrl = await getAuthUrl();
        console.log('Received auth URL:', authUrl);
        
        if (!authUrl) {
            throw new Error('No authentication URL received');
        }

        // Log before redirect
        console.log('Redirecting to:', authUrl);
        
        // Use a small delay to ensure logs are visible
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

const connectInstagram = async () => {
    error.value = null;
    
    try {
        console.log('Initiating Instagram connection...');
        
        socialMediaStore.$patch({
            instagram: {
                ...socialMediaStore.instagram,
                connecting: true
            }
        });

        const authUrl = await getInstagramAuthUrl();
        
        // Log before redirect
        console.log('Redirecting to:', authUrl);
        
        // Use notification for user feedback
        if (window.$) {
            window.$.notify({
                title: 'Instagram',
                message: 'Connecting to Instagram...'
            }, {
                type: 'info'
            });
        }

        // Use a small delay to ensure logs and notification are visible
        setTimeout(() => {
            window.location.href = authUrl;
        }, 100);

    } catch (err) {
        console.error('Instagram connection error:', err);
        error.value = err.message;
        
        socialMediaStore.$patch({
            instagram: {
                ...socialMediaStore.instagram,
                connecting: false
            }
        });

        // Show error notification
        if (window.$) {
            window.$.notify({
                title: 'Error',
                message: `Failed to connect to Instagram: ${err.message}`
            }, {
                type: 'danger'
            });
        }
    }
};

// Handle OAuth callback
const handleOAuthCallback = async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    if (token) {
        try {
            console.log('Handling OAuth callback with token:', token);
            const result = await handleAuthCallback(token);
            console.log('Callback result:', result);
            if (result.success) {
                // Get Facebook pages after successful connection
                const pages = await getFacebookPages(token);
                console.log('Fetched pages:', pages);
                
                socialMediaStore.setFacebookConnection({
                    access_token: token,
                    platform_id: result.platform_id,
                    pages: pages
                });
                
                // Clear URL parameters
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        } catch (err) {
            error.value = err.message;
            console.error('Failed to handle OAuth callback:', err);
        }
    }
};

// Add disconnect handler
const disconnectFacebookAccount = async () => {
    try {
        console.log('Disconnecting Facebook account...');
        await disconnectFacebook();
        socialMediaStore.clearFacebookConnection();
    } catch (err) {
        error.value = err.message;
        console.error('Failed to disconnect Facebook:', err);
    }
};

const disconnectInstagramAccount = async () => {
    try {
        await disconnectInstagram();
        socialMediaStore.clearInstagramConnection();
    } catch (err) {
        console.error('Instagram disconnect error:', err);
        error.value = err.message;
    }
};

const checkFacebookConnection = async () => {
    try {
        const response = await axios.get('/facebook/check-connection');
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

const connectedCount = computed(() => 
  Object.values(connected.value).filter(Boolean).length
);

// Add or update the handleConnect method
const handleConnect = async (platform) => {
    try {
        switch (platform) {
            case 'instagram':
                if (!connected.value.instagram) {
                    await connectInstagram();
                }
                break;
            case 'facebook':
                if (!connected.value.facebook) {
                    await connectFacebook();
                }
                break;
            // ... other platforms
        }
    } catch (err) {
        console.error(`${platform} connection error:`, err);
        error.value = err.message;
    }
};

const statusMessage = ref(null);
const statusType = ref('info');

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

const clearStatus = () => {
    statusMessage.value = null;
    statusType.value = 'info';
};

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

const {removeDynamicCss, initializeCss, removeDynamicJs,initializeScripts } = useDynamicResources(isLoading,cssFiles,JsFiles);

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
        
        // Handle OAuth callback if needed
        await handleOAuthCallback();

        // Handle URL parameters for status messages
        const urlParams = new URLSearchParams(window.location.search);
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

            // Clear URL parameters
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Check both Facebook and Instagram connections
        await Promise.all([
            checkFacebookConnection(),
            checkInstagramConnection()
        ]);
        
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
</style>