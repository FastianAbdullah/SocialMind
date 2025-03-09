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
                    <h4 class="page-heading">Create Post</h4>
                  </div>
                </div>
              </div>
            </div>
            <!-- Container-fluid starts -->
            <div class="container-fluid position-relative">
              <!-- Custom Loader -->
              <div v-if="isGenerating" class="fullpage-loader-overlay">
                <div class="loader-content">
                  <div class="loader-animation">
                    <div class="pulse-ring"></div>
                    <div class="pulse-dot"></div>
                  </div>
                  <p class="mt-3 loader-text">✨ Crafting the best post drafts for you! 🚀</p>
                  <p class="loader-subtext">This might take a few seconds...</p>
                </div>
              </div>
              
              <div class="row" :class="{ 'content-loading': isGenerating }">
                <!-- Left Column - Post Content -->
                <div class="col-xl-6">
                  <div class="card h-100">
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
                        Use Templates
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Right Column - Settings -->
                <div class="col-xl-6">
                  <div class="card h-100">
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
                      <h5 class="mb-0 generated-posts-heading">Generated Posts</h5>
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
                            @click="openPostEditor(index)"
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
                                  class="badge hashtag-badge me-1 mb-1"
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

            <!-- Post Editor Modal -->
            <div class="modal fade" id="postEditorModal" tabindex="-1" aria-labelledby="postEditorModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="postEditorModalLabel">Edit Your Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="postEditorTabs" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="edit-post-tab" data-bs-toggle="tab" data-bs-target="#edit-post" 
                          type="button" role="tab" aria-controls="edit-post" aria-selected="true">Edit Post</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="select-hashtags-tab" data-bs-toggle="tab" data-bs-target="#select-hashtags" 
                          type="button" role="tab" aria-controls="select-hashtags" aria-selected="false">Select Hashtags</button>
                      </li>
                    </ul>
                    
                    <!-- Tabs Content -->
                    <div class="tab-content pt-3" id="postEditorTabsContent">
                      <!-- Edit Post Tab -->
                      <div class="tab-pane fade show active" id="edit-post" role="tabpanel" aria-labelledby="edit-post-tab">
                        <div class="form-group">
                          <label class="form-label">Post Content</label>
                          <textarea 
                            class="form-control post-editor" 
                            rows="8" 
                            v-model="editingPost.content"
                            placeholder="Enter your post content..."
                          ></textarea>
                          
                          <div class="selected-hashtags mt-3" v-if="editingPost.hashtags && editingPost.hashtags.length > 0">
                            <label class="form-label">Selected Hashtags</label>
                            <div>
                              <span 
                                v-for="(tag, tagIndex) in editingPost.hashtags" 
                                :key="tagIndex" 
                                class="badge hashtag-badge me-1 mb-1"
                              >
                                #{{ tag }}
                                <i class="fas fa-times ms-1" @click.stop="removeHashtag(tagIndex)"></i>
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Select Hashtags Tab -->
                      <div class="tab-pane fade" id="select-hashtags" role="tabpanel" aria-labelledby="select-hashtags-tab">
                        <div class="hashtags-container">
                          <div class="mb-3">
                            <label class="form-label">Popular Hashtags</label>
                            <div class="hashtag-cloud">
                              <span 
                                v-for="(tag, index) in availableHashtags" 
                                :key="index"
                                class="badge hashtag-badge me-2 mb-2"
                                :class="{ 'hashtag-selected': isHashtagSelected(tag) }"
                                @click="toggleHashtag(tag)"
                              >
                                #{{ tag }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" @click="publishPost">Publish Post</button>
                  </div>
                </div>
              </div>
            </div>
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
const editingPost = ref({
  content: '',
  hashtags: []
});
const postEditorModal = ref(null);
const availableHashtags = ref([
  'marketing', 'business', 'entrepreneur', 'success', 'motivation',
  'inspiration', 'startup', 'digital', 'leadership', 'innovation',
  'tech', 'growth', 'socialmedia', 'strategy', 'branding'
]);

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

const openPostEditor = (index) => {
  selectPost(index);
  const post = generatedPosts.value[index];
  
  // Make a copy of the post to edit
  editingPost.value = {
    content: post.content,
    hashtags: [...post.hashtags],
    purpose: post.purpose
  };
  
  // Show the modal
  if (!postEditorModal.value) {
    postEditorModal.value = new bootstrap.Modal(document.getElementById('postEditorModal'));
  }
  postEditorModal.value.show();
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

const publishPost = () => {
  // Update the selected post with edited content
  if (selectedPostIndex.value !== null) {
    generatedPosts.value[selectedPostIndex.value] = { ...editingPost.value };
    
    // Show success notification
    $.notify({
      title: 'Success',
      message: 'Post published successfully!'
    }, {
      type: 'success',
      allow_dismiss: true
    });
    
    // Close the modal
    postEditorModal.value.hide();
  }
};

const removeHashtag = (index) => {
  editingPost.value.hashtags.splice(index, 1);
};

const isHashtagSelected = (tag) => {
  return editingPost.value.hashtags.includes(tag);
};

const toggleHashtag = (tag) => {
  if (isHashtagSelected(tag)) {
    // Remove the hashtag
    const index = editingPost.value.hashtags.indexOf(tag);
    editingPost.value.hashtags.splice(index, 1);
  } else {
    // Add the hashtag
    editingPost.value.hashtags.push(tag);
  }
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
  margin-bottom: 1.5rem;
}

.page-heading {
  font-size: 28px;
  font-weight: 700;
  margin: 0;
  color: #333333;
  position: relative;
  display: inline-block;
}

.page-heading:after {
  content: '';
  position: absolute;
  width: 50px;
  height: 3px;
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  border-radius: 3px;
}

.selected-media-preview {
  position: relative;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.selected-media-preview:hover {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.generate-btn {
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  border: none;
  font-weight: 600;
  padding: 12px 20px;
  transition: all 0.3s ease;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 102, 102, 0.2);
  letter-spacing: 0.5px;
}

.generate-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(0, 102, 102, 0.3);
}

.generate-btn:active:not(:disabled) {
  transform: translateY(-1px);
}

.generate-btn:disabled {
  background: #e0e0e0;
  cursor: not-allowed;
  box-shadow: none;
}

.card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  overflow: hidden;
}

.card:hover {
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.card-body {
  padding: 1.5rem;
}

.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
}

.card-header h5 {
  font-weight: 700;
  color: #333;
  margin-bottom: 0.25rem;
}

.card-footer {
  background-color: #f8f9fa;
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1rem 1.5rem;
}

.post-card {
  border: 2px solid transparent;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
  position: relative;
  overflow: hidden;
}

.post-card:before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 5px;
  height: 0;
  background: linear-gradient(to bottom, #006666, #00a3a3);
  transition: height 0.3s ease;
}

.post-card:hover {
  transform: translateY(-7px);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.post-card:hover:before {
  height: 100%;
}

.post-card.selected {
  border-color: #006666;
  background-color: rgba(0, 102, 102, 0.05);
}

.post-card.selected:before {
  height: 100%;
}

.post-content {
  min-height: 100px;
  max-height: 150px;
  overflow-y: auto;
  margin-bottom: 15px;
  font-size: 15px;
  line-height: 1.6;
  color: #121216;
  font-weight: bold;
  padding-right: 5px;
}

.post-content::-webkit-scrollbar {
  width: 4px;
}

.post-content::-webkit-scrollbar-thumb {
  background-color: rgba(0, 102, 102, 0.5);
  border-radius: 4px;
}

.post-hashtags {
  min-height: 30px;
  display: flex;
  flex-wrap: wrap;
}

/* Fix for hashtag visibility */
.hashtag-badge {
  background-color: #ffffff !important;
  color: #333333 !important;
  border: 1px solid #e0e0e0;
  font-weight: 500;
  padding: 5px 10px;
  border-radius: 50px;
  font-size: 12px;
  transition: all 0.2s ease;
}

.hashtag-badge:hover {
  background-color: #f0f0f0 !important;
  transform: translateY(-2px);
}

/* Improved Custom loader styles */
.fullpage-loader-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(20, 25, 34, 0.85);
  z-index: 9999;
  display: flex;
  justify-content: center;
  align-items: center;
  backdrop-filter: blur(3px);
}

.loader-content {
  text-align: center;
  padding: 30px 40px;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.25);
  max-width: 80%;
  animation: fadeIn 0.5s ease;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.loader-animation {
  position: relative;
  width: 80px;
  height: 80px;
  margin: 0 auto;
}

.pulse-ring {
  position: absolute;
  width: 80px;
  height: 80px;
  background: rgba(0, 102, 102, 0.2);
  border-radius: 50%;
  animation: pulse 1.5s ease-out infinite;
}

.pulse-dot {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 30px;
  height: 30px;
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  border-radius: 50%;
  box-shadow: 0 0 20px rgba(0, 102, 102, 0.6);
}

.loader-text {
  font-size: 22px;
  font-weight: 600;
  color: #006666;
  margin: 20px 0 5px;
}

.loader-subtext {
  font-size: 16px;
  color: #666;
  margin-bottom: 0;
}

.content-loading {
  opacity: 0.7;
  pointer-events: none;
  filter: blur(1px);
  transition: all 0.3s ease;
}

/* Button styles */
.btn {
  border-radius: 8px;
  padding: 8px 16px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-outline-primary {
  border-color: #006666;
  color: #006666;
}

.btn-outline-primary:hover {
  background-color: #006666;
  border-color: #006666;
}

.btn-primary {
  background-color: #006666;
  border-color: #006666;
}

.btn-primary:hover {
  background-color: #005555;
  border-color: #005555;
  transform: translateY(-2px);
  box-shadow: 0 4px 10px rgba(0, 102, 102, 0.2);
}

.btn-sm {
  padding: 4px 10px;
  font-size: 13px;
}

.btn-success {
  background-color: #28a745;
  border-color: #28a745;
}

/* Form input styling */
.form-control {
  border-radius: 8px;
  padding: 12px 15px;
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
}

.form-control:focus {
  box-shadow: 0 0 0 3px rgba(0, 102, 102, 0.1);
  border-color: #006666;
}

textarea.form-control {
  resize: none;
  min-height: 120px;
}

/* Animations */
@keyframes pulse {
  0% {
    transform: scale(0.8);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Media Queries */
@media (max-width: 768px) {
  .page-title h4 {
    font-size: 24px;
  }
  
  .loader-content {
    padding: 20px;
    max-width: 90%;
  }
  
  .loader-text {
    font-size: 18px;
  }
}

/* Update the generated posts heading style */
.generated-posts-heading {
  color: #333333;
  font-weight: bold;
}

/* Remove the old loader */
.custom-loader-overlay {
  display: none;
}

/* Post Editor Modal Styles */
.post-editor {
  min-height: 200px;
  font-size: 16px;
  line-height: 1.6;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  resize: vertical;
}

.hashtag-cloud {
  display: flex;
  flex-wrap: wrap;
  padding: 15px;
  background-color: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.hashtag-badge {
  background-color: #f0f0f0;
  color: #333;
  border: 1px solid #e0e0e0;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 50px;
  font-size: 14px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.hashtag-badge:hover {
  background-color: #e0e0e0;
  transform: translateY(-2px);
}

.hashtag-badge.hashtag-selected {
  background-color: #006666;
  color: white;
  border-color: #006666;
}

.hashtag-badge i {
  cursor: pointer;
  opacity: 0.7;
  font-size: 10px;
}

.hashtag-badge i:hover {
  opacity: 1;
}

.modal-dialog.modal-lg {
  max-width: 800px;
}

.nav-tabs .nav-link {
  color: #666;
  font-weight: 500;
  padding: 10px 20px;
  border: none;
  border-bottom: 2px solid transparent;
}

.nav-tabs .nav-link.active {
  color: #006666;
  border-bottom: 2px solid #006666;
  background-color: transparent;
}

.tab-content {
  padding: 20px 0;
}

.selected-hashtags {
  padding: 10px 15px;
  background-color: #f9f9f9;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}
</style>