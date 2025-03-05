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
            <div class="container-fluid position-relative">
              <!-- Custom Loader -->
              <div v-if="isGenerating" class="custom-loader-overlay">
                <div class="loader-content">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-3 loader-text">✨ Crafting the best post drafts for you! 🚀 Please wait...</p>
                </div>
              </div>
              
              <div class="row" :class="{ 'content-loading': isGenerating }">
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
                      <!-- Media Options -->
                      <div class="mb-4">
                        <label class="form-label">Media for Post (Optional)</label>
                        <div class="d-flex">
                          <button class="btn btn-outline-primary w-100" @click="openMediaSelector">
                            <i class="fa fa-plus me-2"></i>
                            Choose Media
                          </button>
                        </div>
                        <div v-if="selectedMedia" class="mt-3">
                          <div class="selected-media-preview">
                            <img :src="selectedMedia" alt="Selected media" class="img-fluid rounded">
                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2" @click="removeMedia">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                        </div>
                      </div>

                      <!-- Generate Button -->
                      <button 
                        class="btn btn-primary w-100 generate-btn" 
                        @click="generatePosts"
                        :disabled="!postDescription || isGenerating"
                      >
                        <span v-if="!isGenerating">Generate Post</span>
                        <span v-else>Generating...</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Generated Posts Section -->
              <div v-if="generatedPosts.length > 0" class="row mt-4 generated-posts" :class="{ 'content-loading': isGenerating }">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header">
                      <h5 class="mb-0">Generated Posts</h5>
                      <p class="text-muted">Select the post you want to use</p>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div 
                          v-for="(post, index) in generatedPosts" 
                          :key="index" 
                          class="col-md-4 mb-4"
                        >
                          <div 
                            class="card post-card h-100" 
                            :class="{ 'selected': selectedPostIndex === index }"
                            @click="selectPost(index)"
                          >
                            <div class="card-body">
                              <div v-if="selectedMedia" class="post-media mb-3">
                                <img :src="selectedMedia" alt="Post media" class="img-fluid rounded">
                              </div>
                              <div class="post-content">
                                <p>{{ post.content }}</p>
                              </div>
                              <div class="post-hashtags">
                                <span 
                                  v-for="(tag, tagIndex) in post.hashtags" 
                                  :key="tagIndex" 
                                  class="badge bg-light text-dark me-1 mb-1"
                                >
                                  #{{ tag }}
                                </span>
                              </div>
                            </div>
                            <div class="card-footer bg-transparent">
                              <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Purpose: {{ post.purpose }}</span>
                                <button 
                                  class="btn btn-sm" 
                                  :class="selectedPostIndex === index ? 'btn-success' : 'btn-outline-primary'"
                                >
                                  {{ selectedPostIndex === index ? 'Selected' : 'Select' }}
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="card-footer">
                      <div class="d-flex justify-content-end">
                        <button 
                          class="btn btn-primary" 
                          :disabled="selectedPostIndex === null"
                          @click="useSelectedPost"
                        >
                          Use Selected Post
                        </button>
                      </div>
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
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Loader from '../components/Loader.vue';
import DashboardSidebar from '../components/DashboardSidebar.vue';
import { useDynamicResources } from '../composables/useDynamicResources';
import { createPost } from '../services/CreatePostService';

const isLoading = ref(true);
const isGenerating = ref(false);
const postDescription = ref('');
const selectedMedia = ref(null);
const generatedPosts = ref([]);
const selectedPostIndex = ref(null);

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

const surpriseMe = () => {
  const surpriseTopics = [
    "The benefits of sustainable living and eco-friendly practices",
    "How AI is transforming the way we work and live",
    "Tips for maintaining work-life balance in a remote work environment",
    "The importance of mental health awareness in today's fast-paced world",
    "How small businesses can leverage social media for growth"
  ];
  
  const randomIndex = Math.floor(Math.random() * surpriseTopics.length);
  postDescription.value = surpriseTopics[randomIndex];
};

const openMediaSelector = () => {
  // In a real implementation, this would open a media library or file picker
  // For now, we'll just simulate selecting an image
  const demoImages = [
    'https://via.placeholder.com/600x400/007bff/ffffff?text=Business+Meeting',
    'https://via.placeholder.com/600x400/28a745/ffffff?text=Nature+Scene',
    'https://via.placeholder.com/600x400/dc3545/ffffff?text=Technology'
  ];
  
  const randomIndex = Math.floor(Math.random() * demoImages.length);
  selectedMedia.value = demoImages[randomIndex];
};

const removeMedia = () => {
  selectedMedia.value = null;
};

const generatePosts = async () => {
  if (!postDescription.value) return;
  
  try {
    isGenerating.value = true;
    generatedPosts.value = [];
    selectedPostIndex.value = null;
    
    const result = await createPost(postDescription.value);
    generatedPosts.value = result;
    
    // Scroll to the generated posts section
    setTimeout(() => {
      const postsSection = document.querySelector('.generated-posts');
      if (postsSection) {
        postsSection.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
  } catch (error) {
    console.error('Error generating posts:', error);
    // Show error notification
    $.notify({
      title: 'Error',
      message: 'Failed to generate posts. Please try again.'
    }, {
      type: 'danger',
      allow_dismiss: true
    });
  } finally {
    isGenerating.value = false;
  }
};

const selectPost = (index) => {
  selectedPostIndex.value = index;
};

const useSelectedPost = () => {
  if (selectedPostIndex.value === null) return;
  
  const selectedPost = generatedPosts.value[selectedPostIndex.value];
  
  // Show success notification
  $.notify({
    title: 'Success',
    message: 'Post selected successfully! Ready to schedule or publish.'
  }, {
    type: 'success',
    allow_dismiss: true
  });
  
  // In a real implementation, you would save this post or navigate to scheduling
  console.log('Selected post:', selectedPost);
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
.page-title {
  padding: 30px 0;
}

.page-title h4 {
  font-size: 24px;
  font-weight: 600;
  margin: 0;
}

.selected-media-preview {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
}

.generate-btn {
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  border: none;
  font-weight: 600;
  padding: 12px;
  transition: all 0.3s ease;
}

.generate-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 102, 102, 0.2);
}

.generate-btn:disabled {
  background: #cccccc;
  cursor: not-allowed;
}

.post-card {
  border: 2px solid transparent;
  transition: all 0.2s ease;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.post-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.post-card.selected {
  border-color: #006666;
  background-color: rgba(0, 102, 102, 0.05);
}

.post-content {
  min-height: 100px;
  max-height: 150px;
  overflow-y: auto;
  margin-bottom: 10px;
}

.post-hashtags {
  min-height: 30px;
}

/* Fix for hashtag visibility */
.badge.bg-light {
  background-color: #f8f9fa !important;
  color: #212529 !important;
  border: 1px solid #dee2e6;
}

/* Custom loader styles */
.custom-loader-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.7);
  z-index: 1000;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
}

.loader-content {
  text-align: center;
  padding: 20px;
  background-color: rgba(255, 255, 255, 0.9);
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  max-width: 80%;
}

.loader-text {
  font-size: 18px;
  font-weight: 500;
  color: #006666;
  margin-bottom: 0;
}

.content-loading {
  opacity: 0.6;
  pointer-events: none;
  filter: blur(2px);
  transition: all 0.3s ease;
}

.spinner-border {
  width: 3rem;
  height: 3rem;
  color: #006666 !important;
}
</style>