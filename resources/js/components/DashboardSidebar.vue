<template>
  <div class="sidebar-wrapper">
    <!-- Logo Section -->
    <div class="sidebar-logo">
      <router-link to="/dashboard" class="logo-box">
        <img class="logo-lg" src="../../../public/assets/images/logo/logo.png" alt="logo">
        <img class="logo-sm" src="../../../public/assets/images/logo/logo-icon.png" alt="small logo">
      </router-link>
      <div class="sidebar-toggle" @click="toggleSidebar">
        <i class="fa fa-bars"></i>
      </div>
    </div>

    <!-- Main Navigation -->
    <nav class="sidebar-nav">
      <div class="nav-wrapper">
        <ul class="nav-menu">
          <li class="nav-item">
            <router-link to="/dashboard" class="nav-link">
              <i class="fas fa-home"></i>
              <span>Dashboard</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/create-post" class="nav-link">
              <i class="fas fa-plus-square"></i>
              <span>Create Post</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/schedule-post" class="nav-link">
              <i class="fas fa-calendar-alt"></i>
              <span>Schedule Post</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/bulk-post" class="nav-link">
              <i class="fas fa-layer-group"></i>
              <span>Post in Bulk</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/description-generator" class="nav-link">
              <i class="fas fa-pen-fancy"></i>
              <span>Description Maker</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/hashtags-finder" class="nav-link">
              <i class="fas fa-hashtag"></i>
              <span>Hashtags Finder</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/sentiment-analysis" class="nav-link">
              <i class="fas fa-chart-line"></i>
              <span>Sentiment Analysis</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/business-plan-generator" class="nav-link">
              <i class="fas fa-briefcase"></i>
              <span>Business Plan Generator</span>
            </router-link>
          </li>

          <li class="nav-item">
            <router-link to="/ai-assistant" class="nav-link">
              <i class="fas fa-robot"></i>
              <span>AI Assistant</span>
            </router-link>
          </li>
        </ul>
      </div>
    </nav>
  </div>
</template>

<script setup>
const toggleSidebar = () => {
  document.body.classList.toggle('sidebar-collapsed');
};
</script>

<style scoped>
.sidebar-wrapper {
  width: 250px;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  background: #2f3544;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  z-index: 1000;
}

.sidebar-logo {
  height: 70px;
  padding: 15px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.logo-box {
  display: flex;
  align-items: center;
}

.logo-lg {
  height: 35px;
  display: block;
}

.logo-sm {
  height: 30px;
  display: none;
}

.sidebar-toggle {
  cursor: pointer;
  color: #ffffff;
  padding: 5px;
}

.sidebar-toggle i {
  font-size: 20px;
}

.sidebar-nav {
  padding: 20px 0;
  height: calc(100vh - 70px);
  overflow-y: auto;
}

.nav-wrapper {
  padding: 0 15px;
}

.nav-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-item {
  margin-bottom: 5px;
}

.nav-link {
  display: flex;
  align-items: center;
  padding: 12px 15px;
  color: #b7b9bd;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.nav-link:hover, .nav-link.router-link-active {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

.nav-link i {
  width: 20px;
  margin-right: 10px;
  font-size: 18px;
}

.nav-link span {
  font-size: 14px;
  font-weight: 500;
}

/* Custom Scrollbar */
.sidebar-nav::-webkit-scrollbar {
  width: 5px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.2);
  border-radius: 5px;
}

/* Collapsed State */
body.sidebar-collapsed .sidebar-wrapper {
  width: 70px;
}

body.sidebar-collapsed .logo-lg {
  display: none;
}

body.sidebar-collapsed .logo-sm {
  display: block;
}

body.sidebar-collapsed .nav-link span {
  display: none;
}

body.sidebar-collapsed .nav-link {
  justify-content: center;
  padding: 12px;
}

body.sidebar-collapsed .nav-link i {
  margin: 0;
  font-size: 20px;
}

/* Responsive */
@media (max-width: 991px) {
  .sidebar-wrapper {
    transform: translateX(-100%);
  }

  body.sidebar-collapsed .sidebar-wrapper {
    transform: translateX(0);
    width: 250px;
  }

  body.sidebar-collapsed .logo-lg {
    display: block;
  }

  body.sidebar-collapsed .logo-sm {
    display: none;
  }

  body.sidebar-collapsed .nav-link span {
    display: block;
  }

  body.sidebar-collapsed .nav-link {
    justify-content: flex-start;
    padding: 12px 15px;
  }

  body.sidebar-collapsed .nav-link i {
    margin-right: 10px;
    font-size: 18px;
  }
}
</style>