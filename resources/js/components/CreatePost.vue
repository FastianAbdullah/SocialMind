<template>
  <Loader v-show="isLoading"></Loader>
  <div v-show="!isLoading">
      <!-- tap on top starts-->
      <div class="tap-top"><i data-feather="chevrons-up"></i></div>
      <!-- tap on tap ends-->
      <!-- page-wrapper Start-->
      <div class="page-wrapper compact-wrapper" id="pageWrapper">

        <!-- Page Header Start-->
        <!-- Page Header Ends-->

        <!-- Page Body Start-->
        <div class="page-body-wrapper">
          <!-- Page Sidebar Start-->
          <DashboardSidebar />
          <!-- Page Sidebar Ends-->
          
          <div class="page-body" style="margin-left: 220px; width: calc(100% - 220px); min-height: 100vh; margin-top: 0;"> 
            <div class="container-fluid">            
              <div class="page-title"> 
                <div class="row">
                  <div class="col-12 text-center">
                    <h4>Create Post</h4>
                  </div>
                </div>
              </div>
            </div>
            <!-- Container-fluid starts -->
            <div class="container-fluid">
              <div class="row">
                <!-- Left Column - Post Content -->
                <div class="col-xl-8">
                  <div class="card">
                    <div class="card-body">
                      <div class="mb-4">
                        <label class="form-label">What is your post about?</label>
                        <textarea 
                          class="form-control" 
                          rows="4"
                          placeholder="Enter your post description here..."
                          v-model="postDescription"
                        ></textarea>
                      </div>
                      <button class="btn btn-light" @click="surpriseMe">
                        <i class="fa fa-magic me-2"></i>
                        Surprise me
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Right Column - Settings -->
                <div class="col-xl-4">
                  <div class="card">
                    <div class="card-body">
                      <!-- Number of Variants -->
                      <div class="mb-4">
                        <label class="form-label">Number of variants</label>
                        <div class="range_4">
                          <div class="slider-container">
                            <input 
                              type="range" 
                              class="range-slider_input" 
                              min="1" 
                              max="7" 
                              v-model="variants"
                              @input="updateSliderPosition"
                            >
                            <div class="range-slider_line">
                              <div class="range-slider_line-fill" :style="sliderFillStyle"></div>
                            </div>
                            <div class="range-slider_thumb" :style="sliderThumbStyle">
                              {{ variants }}
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Media Options -->
                      <div class="mb-4">
                        <label class="form-label">Media for Post</label>
                        <div class="d-flex">
                          <button class="btn btn-outline-primary w-100">
                            <i class="fa fa-plus me-2"></i>
                            Choose Media
                          </button>
                        </div>
                      </div>

                      <!-- Generate Button -->
                      <button class="btn btn-primary w-100">
                        Generate Post
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Container-fluid Ends -->
          </div>
          <!-- footer start-->

        </div>
      </div>
  </div>
</template>



<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
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

const { initializeCss, initializeScripts, removeDynamicCss, removeDynamicJs } = useDynamicResources(isLoading, cssFiles, JsFiles);

const postDescription = ref('');
const variants = ref(1);

const sliderFillStyle = computed(() => {
  const percentage = ((variants.value - 1) / 6) * 100;
  return {
    width: `${percentage}%`
  };
});

const sliderThumbStyle = computed(() => {
  const percentage = ((variants.value - 1) / 6) * 100;
  return {
    left: `${percentage}%`
  };
});

const updateSliderPosition = (event) => {
  variants.value = parseInt(event.target.value);
};

const surpriseMe = () => {
  console.log('Surprise me clicked');
};

onMounted(async () => {
  // Remove existing resources before initializing new ones
  await removeDynamicCss();
  await removeDynamicJs();
  
  // Initialize new resources
  await initializeCss();
  await initializeScripts();
});

// Cleanup when component is destroyed
onBeforeUnmount(async () => {
  await removeDynamicCss();
  await removeDynamicJs();
});

</script>

<style scoped>
.range_4 {
  margin: 15px 0;
}

.slider-container {
  position: relative;
  height: 45px;
  margin: 0 20px;
}

.range-slider_input {
  width: 100%;
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 3;
  -webkit-appearance: none;
  appearance: none;
  height: 4px;
  opacity: 0;
  margin: 0;
}

.range-slider_input::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  cursor: pointer;
  border-radius: 50%;
  background: #006666;
}

.range-slider_line {
  height: 7px;
  width: 100%;
  background-color: rgba(0, 102, 102, 0.2);
  border-radius: 10px;
  top: 50%;
  transform: translateY(-50%);
  left: 0;
  position: absolute;
  z-index: 1;
}

.range-slider_line-fill {
  position: absolute;
  height: 7px;
  background-color: #006666;
  border-radius: 10px 0 0 10px;
  transition: width 0.2s ease;
}

.range-slider_thumb {
  width: 46px;
  height: 26px;
  border-radius: 6px;
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  background-color: #006666;
  display: flex;
  justify-content: center;
  align-items: center;
  font-weight: 700;
  font-size: 14px;
  color: #fff;
  z-index: 2;
  transition: left 0.2s ease;
}

.page-title {
  padding: 30px 0;
}

.page-title h4 {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
}
</style>