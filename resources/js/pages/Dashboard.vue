<template>
    <Loader v-show="isLoading"></Loader>
    <div v-show="!isLoading">    
      <!-- tap on top starts-->
      <div class="tap-top"><i data-feather="chevrons-up"></i></div>
      <!-- tap on tap ends-->
      <!-- page-wrapper Start-->
      <div class="page-wrapper compact-wrapper" id="pageWrapper">
        <!-- Page Header Start-->
        <div class="page-header" style=" margin-left: 220px; width: calc(100% - 220px);">
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
        <!-- Page Header Ends-->

        <!-- Page Body Start-->
        <div class="page-body-wrapper">
          <!-- Page Sidebar Start-->
          <DashboardSidebar />
          <!-- Page Sidebar Ends-->
          
          <div class="page-body" style=" margin-left: 220px; width: calc(100% - 220px);"> 
            <div class="container-fluid">            
              <div class="page-title"> 
                <div class="row">
                  <div class="col-6">
                    <h4>
                      Dashboard</h4>
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
                      <h4>Link Accounts </h4>
                    </div>
                    <div class="card-body p-2 pt-0">
                      <h1>Hello Link Here.</h1>
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
          <footer class="footer" style=" margin-left: 220px; width: calc(100% - 220px);">
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
import { ref, onMounted } from 'vue';
import Loader from '../components/Loader.vue';
import DashboardSidebar from '../components/DashboardSidebar.vue';
import { useDynamicResources } from '../composables/useDynamicResources';

const isLoading = ref(true);

// Data properties
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
 await removeDynamicCss();
 await removeDynamicJs();
 await initializeCss();
 await initializeScripts();
});
</script>

<style scoped>
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