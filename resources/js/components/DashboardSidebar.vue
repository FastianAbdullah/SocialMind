<template>
    <!--Dashboard Sidebar -->
    <div class="sidebar-wrapper" data-layout="stroke-svg">
        <div class="logo-wrapper"><router-link to="/dashboard"><img class="img-fluid" src="../../../public/assets/images/logo/logo.png" alt=""></router-link>
          <div class="back-btn"><i class="fa fa-angle-left"> </i></div>
          <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle"> </i></div>
        </div>
        <div class="logo-icon-wrapper"><router-link to="/dashboard"><img class="img-fluid" src="../../../public/assets/images/logo/logo-icon.png" alt=""></router-link></div>
        <nav class="sidebar-main">
          <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
          <div id="sidebar-menu">
            <ul class="sidebar-links" id="simple-bar">
              <li class="back-btn"><router-link to="/dashboard"><img class="img-fluid" src="../../../public/assets/images/logo/logo-icon.png" alt=""></router-link>
                <div class="mobile-back text-end"> <span>Back </span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
              </li>
              <li class="sidebar-main-title">
                <div>
                  <h6 class="lan-1">Features</h6>
                </div>
              </li>
              <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                <router-link class="sidebar-link sidebar-title link-nav" to="/dashboard">
                  <svg class="stroke-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#stroke-file"></use>
                  </svg>
                  <svg class="fill-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#fill-file"></use>
                  </svg><span>Dashboard</span>
                </router-link>
              </li>
              <li class="sidebar-list">
                    <i class="fa fa-thumb-tack"></i>
                    <router-link class="sidebar-link sidebar-title link-nav" to="/social-links">
                      <svg class="stroke-icon">
                        <use href="../../../public/assets/svg/icon-sprite.svg#stroke-user"></use>
                      </svg>
                      <svg class="fill-icon">
                        <use href="../../../public/assets/svg/icon-sprite.svg#fill-user"></use>
                      </svg>
                      <span>Social Links</span>
                    </router-link>
              </li>
              <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                <router-link class="sidebar-link sidebar-title link-nav" to="/create-post">
                  <svg class="stroke-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#stroke-board"></use>
                  </svg>
                  <svg class="fill-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#fill-board"></use>
                  </svg><span>Create Post</span>
                </router-link>
              </li>
              <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                <router-link class="sidebar-link sidebar-title link-nav" to="/schedule-post">
                  <svg class="stroke-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                  </svg>
                  <svg class="fill-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#fill-calender"></use>
                  </svg><span>Schedule Post</span>
                </router-link>
              </li>
              <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                <router-link class="sidebar-link sidebar-title link-nav" to="/bulk-post">
                  <svg class="stroke-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                  </svg>
                  <svg class="fill-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#fill-calender"></use>
                  </svg><span>Post in Bulk</span>
                </router-link>
              </li>
              <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                <router-link class="sidebar-link sidebar-title link-nav" to="/description-generator">
                  <svg class="stroke-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                  </svg>
                  <svg class="fill-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#fill-calender"></use>
                  </svg><span>Description Maker</span>
                </router-link>
              </li>
              <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
                <router-link class="sidebar-link sidebar-title link-nav" to="/hashtags-finder">
                  <svg class="stroke-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#stroke-calendar"></use>
                  </svg>
                  <svg class="fill-icon">
                    <use href="../../../public/assets/svg/icon-sprite.svg#fill-calender"></use>
                  </svg><span>Hashtags Finder</span>
                </router-link>
              </li>
            </ul>


            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
          </div>
        </nav>
    </div>
</template>

<style scoped>
.sidebar-wrapper {
  width: 230px; /* Adjust this value */
  min-width: 230px;
  max-width: 230px;
}
</style>