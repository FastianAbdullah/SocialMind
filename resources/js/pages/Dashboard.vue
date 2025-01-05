<template>
  <body class="dark-only"> 
    <!-- loader starts-->
    <!-- <div class="loader-wrapper">
      <div class="loader"> 
        <div class="loader4"></div>
      </div>
    </div> -->
    <!-- loader ends-->
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
          <!-- <script class="result-template" type="text/x-handlebars-template">
            <div class="ProfileCard u-cf">                        
            <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
            <div class="ProfileCard-details"> 
            <div class="ProfileCard-realName">{{name}}</div>
            </div> 
            </div>
          </script> -->
          <!-- <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script> -->
        </div>
      </div>
      <!-- Page Header Ends                              -->
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
                  <div class="mobile-back text-end"> <span>Back </span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
                </li>
                <li class="sidebar-main-title">
                  <div>
                    <h6 class="lan-1">General</h6>
                  </div>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="file-manager.html">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-file"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-file"></use>
                    </svg><span>File manager</span></a>
                </li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="kanban.html">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-board"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-board"></use>
                    </svg><span>kanban Board</span></a></li>
                <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="calendar-basic.html">
                    <svg class="stroke-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                    </svg>
                    <svg class="fill-icon">
                      <use href="../../../public/assets/svg/icon-sprite.svg#fill-calender"></use>
                    </svg><span>Calendar</span></a></li>
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
  </body>
</template>

<script>
export default {
  methods: {
    loadScript(src) {
      return new Promise((resolve, reject) => {
        const existingScript = document.querySelector(`script[src="${src}"]`);
        if (existingScript) {
          console.log(`${src} already loaded.`);
          return resolve();
        }

        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
      });
    },
    removeDynamicCss() {
      return new Promise((resolve,reject) => {
        try {

          const links = document.querySelectorAll('link[data-dynamic="true"]');
          links.forEach((link) => {
            document.head.removeChild(link);
          });
          console.log(`${links.length} CSS file(s) removed.`);
          resolve();

      } catch (error) {
          console.log('Error Occured While loading the Css file', error);
          reject();
        }
      });
    },
    loadCss(href) {
      return new Promise((resolve, reject) => {
        // Check if the CSS file is already loaded
        const existingLink = document.querySelector(`link[href="${href}"]`);
        if (existingLink) {
          console.log(`${href} already loaded.`);
          return resolve();
        }

        // Create and load a new <link> element
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = href;
        link.dataset.dynamic = 'true'; // Mark as dynamically added for removal later

        link.onload = () => {
          console.log(`${href} loaded successfully.`);
          resolve();
        };

        link.onerror = () => {
          console.error(`Failed to load CSS: ${href}`);
          reject(new Error(`Failed to load CSS: ${href}`));
        };

        document.head.appendChild(link);
      });
    },
    async initializeCss() {
      try {
        // load Bottstrap.
        await this.loadCss('http://[::1]:5173/resources/css/vendors/bootstrap.css');
        console.log('bootstrap Css loaded successfully.');

        await this.loadCss('http://[::1]:5173/resources/css/style.css');
        console.log('Styles Css loaded successfully.');

        await this.loadCss('http://[::1]:5173/resources/css/color-1.css');

        await this.loadCss('http://[::1]:5173/resources/css/responsive.css');

      } catch (error) {
        console.log('Error Occured While Intializing Css files' , error);
      }
    },
    async initializeScripts() {
      try {
        // Ensure jQuery is loaded
        await this.loadScript('http://[::1]:5173/resources/js/legacy/jquery.min.js');
        console.log('jQuery loaded successfully.');

        // Load the sidebar-menu.js file
        await this.loadScript('http://[::1]:5173/resources/js/legacy/sidebar-menu.js');
        console.log('Sidebar menu script loaded successfully.');

        console.log('Sidebar menu initialized.');
      } catch (error) {
        console.error('Error loading scripts:', error);
      }
    },
  },
  mounted() {
    //remove already present css files.
    this.removeDynamicCss();
    // load css files of the component.
    this.initializeCss();
    // load javascript files of the component.
    this.initializeScripts();
  },
};
</script>