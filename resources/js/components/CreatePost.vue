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
                          <button class="btn btn-outline-primary w-100" @click="openMediaSelector" v-if="!mediaFileName">
                            <i class="fa fa-plus me-2"></i>
                            Choose Media
                          </button>
                          <div class="w-100 d-flex align-items-center" v-else>
                            <div class="selected-file-info flex-grow-1">
                              <span class="fw-medium"><i class="fas fa-file-image me-2"></i>{{ mediaFileName }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-danger ms-2" @click="removeMedia">
                              <i class="fa fa-times"></i>
                            </button>
                          </div>
                          <input 
                            type="file" 
                            ref="mediaFileInput" 
                            @change="handleMediaSelect" 
                            accept="image/*,video/*" 
                            class="d-none"
                          >
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
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Container-fluid Ends -->

            <!-- Post Editor Modal -->
            <div class="modal fade post-editor-modal" id="postEditorModal" tabindex="-1" ref="postEditorModalEl">
              <div class="modal-dialog modal-xl">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="postEditorModalLabel">Edit Your Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0">
                    <div class="row g-0">
                      <!-- Media Preview (left side) - Only shown when media is present -->
                      <div class="col-md-6" v-if="hasMedia">
                        <div class="media-preview-pane h-100">
                          <img :src="selectedMedia" alt="Post media" class="img-fluid h-100 w-100 object-fit-cover">
                        </div>
                      </div>
                      
                      <!-- Editor Content (right side) - Takes full width when no media -->
                      <div :class="hasMedia ? 'col-md-6' : 'col-12'">
                        <div class="editor-container p-4">
                          <!-- Tab buttons -->
                          <div class="editor-tabs mb-4">
                            <button 
                              class="btn editor-tab-btn me-2" 
                              :class="activeEditorTab === 'edit-post' ? 'btn-primary' : 'btn-outline-secondary'"
                              @click="switchEditorTab('edit-post')"
                            >
                              <i class="fas fa-edit me-1"></i> Edit Post
                            </button>
                            <button 
                              class="btn editor-tab-btn" 
                              :class="activeEditorTab === 'select-hashtags' ? 'btn-primary' : 'btn-outline-secondary'"
                              @click="switchEditorTab('select-hashtags')"
                            >
                              <i class="fas fa-hashtag me-1"></i> Select Hashtags
                            </button>
                          </div>
                          
                          <!-- Edit Post Content -->
                          <div v-show="activeEditorTab === 'edit-post'" class="edit-post-content">
                            <div class="form-group">
                              <label class="form-label">Post Content</label>
                              
                              <!-- Rich Text Editor -->
                              <div class="rich-text-editor card">
                                <div class="editor-toolbar card-header">
                                  <button type="button" class="btn btn-sm editor-btn" @click="formatText('bold')">
                                    <i class="fas fa-bold"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm editor-btn" @click="formatText('italic')">
                                    <i class="fas fa-italic"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm editor-btn" @click="formatText('underline')">
                                    <i class="fas fa-underline"></i>
                                  </button>
                                  <button type="button" class="btn btn-sm editor-btn" @click="formatText('strikethrough')">
                                    <i class="fas fa-strikethrough"></i>
                                  </button>
                                  <div class="editor-divider"></div>
                                  <button type="button" class="btn btn-sm editor-btn" @click="formatText('emoji')">
                                    <i class="far fa-smile"></i>
                                  </button>
                                </div>
                                <div 
                                  class="editor-content card-body" 
                                  contenteditable="true"
                                  ref="postContentEditor"
                                  @input="updatePostContent"
                                ></div>
                              </div>
                              
                              <!-- Show selected hashtags on the edit post tab as well -->
                              <div class="selected-hashtags mt-3" v-if="editingPost.hashtags && editingPost.hashtags.length > 0">
                                <label class="form-label">Selected Hashtags</label>
                                <div>
                                  <span 
                                    v-for="(tag, tagIndex) in editingPost.hashtags" 
                                    :key="tagIndex" 
                                    class="badge hashtag-badge me-1 mb-1"
                                  >
                                    #{{ tag }}
                                    <i class="fas fa-times ms-1" @click.stop="removeHashtag(tag)"></i>
                                  </span>
                                </div>
                              </div>
                            </div>
                          </div>
                          
                          <!-- Select Hashtags Content -->
                          <div v-show="activeEditorTab === 'select-hashtags'" class="hashtags-content">
                            <div class="hashtags-container">
                              <div class="mb-3">
                                <label class="form-label">Your Selected Hashtags</label>
                                <div class="selected-hashtags-container mb-3" v-if="editingPost.hashtags && editingPost.hashtags.length > 0">
                                  <span 
                                    v-for="(tag, index) in editingPost.hashtags" 
                                    :key="'selected-'+index"
                                    class="badge hashtag-badge hashtag-selected me-2 mb-2"
                                    @click="removeHashtag(tag)"
                                  >
                                    #{{ tag }}
                                    <i class="fas fa-times ms-1"></i>
                                  </span>
                                </div>
                                <div v-else class="empty-hashtags">
                                  <p class="text-muted">No hashtags selected yet. Add your own or choose from trending hashtags below.</p>
                                </div>
                              </div>
                              
                              <!-- Add custom hashtag input -->
                              <div class="mb-4">
                                <label class="form-label">Add Your Own Hashtag</label>
                                <div class="input-group">
                                  <span class="input-group-text">#</span>
                                  <input 
                                    type="text" 
                                    class="form-control" 
                                    v-model="customHashtag" 
                                    placeholder="Enter your hashtag"
                                    @keyup.enter="addCustomHashtag"
                                  >
                                  <button class="btn btn-primary" @click="addCustomHashtag">Add</button>
                                </div>
                                <small class="text-muted mt-1">Press Enter or click Add to add your custom hashtag</small>
                              </div>
                              
                              <!-- Trending hashtags section -->
                              <div class="mb-3" v-if="trendingHashtags.length > 0">
                                <label class="form-label">Trending Hashtags</label>
                                <div class="hashtag-cloud trending-hashtags">
                                  <span 
                                    v-for="(tag, index) in trendingHashtags" 
                                    :key="'trending-'+index"
                                    class="badge hashtag-badge trending-hashtag me-2 mb-2"
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
                    </div>
                  </div>
                  <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" @click="openPublishModal">
                      <i class="fas fa-paper-plane me-1"></i> Publish
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Publish Modal -->
            <div class="modal fade" id="publishModal" tabindex="-1" ref="publishModalEl">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Publish Your Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div v-if="publishError" class="alert alert-danger mb-3">
                      {{ publishError }}
                    </div>
                    
                    <h6 class="mb-3">Select platforms to publish to:</h6>
                    
                    <!-- Connected Platforms -->
                    <div class="connected-platforms mb-4">
                      <div class="row g-3">
                        <!-- Facebook -->
                        <div class="col-md-4">
                          <div class="platform-card" :class="{ 'selected': selectedPlatforms.facebook, 'disabled': !socialMediaStore.facebook.connected }">
                            <div class="form-check">
                              <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="facebook-platform" 
                                v-model="selectedPlatforms.facebook"
                                :disabled="!socialMediaStore.facebook.connected"
                              >
                              <label class="form-check-label w-100" for="facebook-platform">
                                <div class="d-flex align-items-center">
                                  <i class="fab fa-facebook platform-icon"></i>
                                  <span class="ms-2">Facebook</span>
                                </div>
                                <div v-if="socialMediaStore.facebook.connected" class="small text-success mt-1">
                                  <i class="fas fa-check-circle me-1"></i> Connected
                                </div>
                                <div v-else class="small text-muted mt-1">
                                  <i class="fas fa-times-circle me-1"></i> Not connected
                                </div>
                              </label>
                            </div>
                            <button 
                              v-if="!socialMediaStore.facebook.connected" 
                              class="btn btn-sm btn-outline-primary mt-2"
                              @click="connectFacebook"
                            >
                              Connect
                            </button>
                            <div v-else-if="socialMediaStore.facebook.pages.length > 0" class="mt-2">
                              <select 
                                class="form-select form-select-sm" 
                                v-model="selectedPages.facebook"
                                :disabled="!selectedPlatforms.facebook"
                              >
                                <option value="" disabled>Select a page</option>
                                <option 
                                  v-for="page in socialMediaStore.facebook.pages" 
                                  :key="page.page_id" 
                                  :value="page.page_id"
                                >
                                  {{ page.name }}
                                </option>
                              </select>
                            </div>
                          </div>
                        </div>
                        
                        <!-- Instagram -->
                        <div class="col-md-4">
                          <div class="platform-card" :class="{ 'selected': selectedPlatforms.instagram, 'disabled': !socialMediaStore.instagram.connected }">
                            <div class="form-check">
                              <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="instagram-platform" 
                                v-model="selectedPlatforms.instagram"
                                :disabled="!socialMediaStore.instagram.connected || !hasMedia"
                              >
                              <label class="form-check-label w-100" for="instagram-platform">
                                <div class="d-flex align-items-center">
                                  <i class="fab fa-instagram platform-icon"></i>
                                  <span class="ms-2">Instagram</span>
                                </div>
                                <div v-if="socialMediaStore.instagram.connected" class="small text-success mt-1">
                                  <i class="fas fa-check-circle me-1"></i> Connected
                                </div>
                                <div v-else class="small text-muted mt-1">
                                  <i class="fas fa-times-circle me-1"></i> Not connected
                                </div>
                                <div v-if="socialMediaStore.instagram.connected && !hasMedia" class="small text-danger mt-1">
                                  <i class="fas fa-exclamation-circle me-1"></i> Image required
                                </div>
                              </label>
                            </div>
                            <button 
                              v-if="!socialMediaStore.instagram.connected" 
                              class="btn btn-sm btn-outline-danger mt-2"
                              @click="connectInstagram"
                            >
                              Connect
                            </button>
                            <div v-else-if="!hasMedia" class="mt-2">
                              <button 
                                class="btn btn-sm btn-outline-danger"
                                @click="openMediaSelector"
                              >
                                Add Image
                              </button>
                            </div>
                            <div v-else-if="socialMediaStore.instagram.accounts.length > 0" class="mt-2">
                              <select 
                                class="form-select form-select-sm" 
                                v-model="selectedPages.instagram"
                                :disabled="!selectedPlatforms.instagram"
                              >
                                <option value="" disabled>Select an account</option>
                                <option 
                                  v-for="account in socialMediaStore.instagram.accounts" 
                                  :key="account.id" 
                                  :value="account.id"
                                >
                                  {{ account.name }}
                                </option>
                              </select>
                            </div>
                          </div>
                        </div>
                        
                        <!-- LinkedIn -->
                        <div class="col-md-4">
                          <div class="platform-card" :class="{ 'selected': selectedPlatforms.linkedin, 'disabled': !socialMediaStore.linkedin.connected }">
                            <div class="form-check">
                              <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="linkedin-platform" 
                                v-model="selectedPlatforms.linkedin"
                                :disabled="!socialMediaStore.linkedin.connected"
                              >
                              <label class="form-check-label w-100" for="linkedin-platform">
                                <div class="d-flex align-items-center">
                                  <i class="fab fa-linkedin platform-icon"></i>
                                  <span class="ms-2">LinkedIn</span>
                                </div>
                                <div v-if="socialMediaStore.linkedin.connected" class="small text-success mt-1">
                                  <i class="fas fa-check-circle me-1"></i> Connected
                                </div>
                                <div v-else class="small text-muted mt-1">
                                  <i class="fas fa-times-circle me-1"></i> Not connected
                                </div>
                              </label>
                            </div>
                            <button 
                              v-if="!socialMediaStore.linkedin.connected" 
                              class="btn btn-sm btn-outline-info mt-2"
                              @click="connectLinkedin"
                            >
                              Connect
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <div v-if="!anyPlatformSelected" class="alert alert-warning">
                      Please select at least one platform to publish to.
                    </div>
                    
                    <div v-if="isPublishing" class="text-center my-4">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Publishing...</span>
                      </div>
                      <p class="mt-2">Publishing your post...</p>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button 
                      type="button" 
                      class="btn btn-success" 
                      @click="publishToSelectedPlatforms"
                      :disabled="!anyPlatformSelected || isPublishing"
                    >
                      <i class="fas fa-paper-plane me-1"></i> Publish Now
                    </button>
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
import { ref, onMounted, onBeforeUnmount, computed, nextTick } from 'vue';
import Loader from '../components/Loader.vue';
import DashboardSidebar from '../components/DashboardSidebar.vue';
import { useDynamicResources } from '../composables/useDynamicResources';
import { createPost } from '../services/CreatePostService';
import { useSocialMediaStore } from '../store/socialMediaStore.js';
import { getAuthUrl } from '../services/SocialMediaAuthService.js';
import { getAuthUrl as getInstagramAuthUrl } from '../services/instagramService';
import { publishPosts } from '../services/CreatePostService.js';

const isLoading = ref(true);
const isGenerating = ref(false);
const postDescription = ref('');
const selectedMedia = ref(null);
const generatedPosts = ref([]);
const selectedPostIndex = ref(null);
const editingPost = ref({
  content: '',
  hashtags: [],
  mediaCaption: ''
});
const postEditorModalEl = ref(null);
const postEditorModal = ref(null);
const mediaPreviewModalEl = ref(null);
const mediaPreviewModal = ref(null);
const postContentEditor = ref(null);
const trendingHashtags = ref([]);
const customHashtag = ref('');
const mediaFile = ref(null);
const mediaPreview = ref(null);
const mediaCaption = ref('');
const mediaFileName = ref('');
const mediaFileInput = ref(null);
const activeEditorTab = ref('edit-post');
const publishModalEl = ref(null);
const publishModal = ref(null);
const publishError = ref('');
const isPublishing = ref(false);
const socialMediaStore = useSocialMediaStore();

const selectedPlatforms = ref({
  facebook: false,
  instagram: false,
  linkedin: false
});

const selectedPages = ref({
  facebook: '',
  instagram: ''
});

const anyPlatformSelected = computed(() => {
  return selectedPlatforms.value.facebook || 
         selectedPlatforms.value.instagram || 
         selectedPlatforms.value.linkedin;
});

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

const availableHashtags = ref([
  'mentalhealth', 'awareness', 'wellbeing', 'selfcare', 'mindfulness',
  'motivation', 'health', 'wellness', 'mentalhealthmatters', 'meditation',
  'selflove', 'anxiety', 'mentalhealthawareness', 'breakthestigma', 'emotionalwellbeing',
  'psychology', 'healing', 'depression', 'stress', 'therapy',
  'positivity', 'recovery', 'mentalhealthsupport', 'life', 'love'
]);


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
  if (mediaFileInput.value) {
    mediaFileInput.value.click();
  }
};

const handleMediaSelect = (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  mediaFile.value = file;
  mediaFileName.value = file.name;
  
  // Create preview URL
  if (mediaPreview.value) {
    URL.revokeObjectURL(mediaPreview.value);
  }
  mediaPreview.value = URL.createObjectURL(file);
  selectedMedia.value = mediaPreview.value;
  
  // Set default caption from filename
  const fileName = file.name.split('.')[0];
  mediaCaption.value = fileName.replace(/[_-]/g, ' ');
};

const removeMedia = () => {
  if (mediaPreview.value) {
    URL.revokeObjectURL(mediaPreview.value);
  }
  mediaPreview.value = null;
  mediaFile.value = null;
  mediaFileName.value = '';
  mediaCaption.value = '';
  selectedMedia.value = null;
};

const generatePosts = async () => {
  if (!postDescription.value) return;
  
  try {
    isGenerating.value = true;
    generatedPosts.value = [];
    selectedPostIndex.value = null;
    trendingHashtags.value = [];
    
    const result = await createPost(postDescription.value);
    generatedPosts.value = result.posts;
    trendingHashtags.value = result.trendingHashtags || [];
    
    // Add trending hashtags to available hashtags
    if (trendingHashtags.value.length > 0) {
      // Merge trending hashtags with available hashtags (avoiding duplicates)
      availableHashtags.value = [...new Set([...trendingHashtags.value])];
    }
    
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
  if (!postEditorModal.value && window.bootstrap) {
    // Try to initialize modal if it wasn't done during mount
    const modalElement = document.getElementById('postEditorModal');
    if (modalElement) {
      postEditorModal.value = new window.bootstrap.Modal(modalElement);
    }
  }
  
  selectPost(index);
  const post = generatedPosts.value[index];
  
  // Make a deep copy of the post to edit
  editingPost.value = {
    content: post.content,
    hashtags: [...post.hashtags],
    purpose: post.purpose,
    mediaCaption: post.mediaCaption
  };
  
  // Initialize the editor with the content once, then let it handle its own state
  nextTick(() => {
    if (postContentEditor.value) {
      postContentEditor.value.innerHTML = editingPost.value.content;
    }
  });
  
  // Show the modal with a jquery fallback if bootstrap object isn't available
  if (postEditorModal.value) {
    postEditorModal.value.show();
  } else if (window.$ && $('#postEditorModal').length) {
    $('#postEditorModal').modal('show');
  } else {
    console.error('Unable to initialize modal - Bootstrap not found');
  }
};

const removeHashtag = (tagOrIndex) => {
  let index;
  
  if (typeof tagOrIndex === 'number') {
    // It's an index
    index = tagOrIndex;
  } else {
    // It's a tag name
    index = editingPost.value.hashtags.indexOf(tagOrIndex);
  }
  
  if (index !== -1) {
    editingPost.value.hashtags.splice(index, 1);
    
    // Also remove the hashtag from the content if it exists
    if (postContentEditor.value && typeof tagOrIndex === 'string') {
      const regex = new RegExp(`#${tagOrIndex}\\b`, 'g');
      postContentEditor.value.innerHTML = postContentEditor.value.innerHTML.replace(regex, '');
      updatePostContent();
    }
  }
};

const toggleHashtag = (tag) => {
  const index = editingPost.value.hashtags.indexOf(tag);
  
  if (index !== -1) {
    // Remove the hashtag if it's already selected
    editingPost.value.hashtags.splice(index, 1);
  } else {
    // Add the hashtag if it's not already selected
    editingPost.value.hashtags.push(tag);
  }
};

const isHashtagSelected = (tag) => {
  return editingPost.value.hashtags.includes(tag);
};

// Method to extract hashtags from content
const extractHashtagsFromContent = (content) => {
  // Match hashtags in the text
  const hashtagRegex = /#(\w+)/g;
  const matches = content.match(hashtagRegex);
  
  if (!matches) return [];
  
  // Remove the # symbol and return unique tags
  return [...new Set(matches.map(tag => tag.substring(1)))];
};

// Modified updatePostContent to avoid resetting cursor
const updatePostContent = () => {
  if (!postContentEditor.value) return;
  
  // Get content from the contenteditable div
  const content = postContentEditor.value.innerHTML;
  
  // Update our model without triggering a re-render of the contenteditable
  editingPost.value.content = content;
  
  // Extract hashtags from content and add to selected hashtags
  const contentHashtags = extractHashtagsFromContent(content);
  if (contentHashtags.length > 0) {
    // Create a new set to avoid duplicates
    const uniqueHashtags = new Set([...editingPost.value.hashtags]);
    // Add new hashtags
    contentHashtags.forEach(tag => uniqueHashtags.add(tag));
    // Update the hashtags array
    editingPost.value.hashtags = Array.from(uniqueHashtags);
  }
};

// Format text in the rich text editor
const formatText = (command) => {
  if (command === 'emoji') {
    // Simple emoji picker (would be replaced with a proper emoji picker in production)
    const emojis = ['😊', '👍', '❤️', '✨', '🚀', '💡', '🌈', '👏', '💪', '🙏'];
    const selectedEmoji = emojis[Math.floor(Math.random() * emojis.length)];
    document.execCommand('insertText', false, selectedEmoji);
  } else {
    document.execCommand(command, false, null);
  }
  postContentEditor.value.focus();
  updatePostContent();
};

// Add method to handle custom hashtag addition
const addCustomHashtag = () => {
  if (!customHashtag.value) return;
  
  // Clean up the hashtag (remove spaces, #, etc.)
  const cleanTag = customHashtag.value.trim().replace(/^#/, '').replace(/\s+/g, '');
  
  if (cleanTag && !editingPost.value.hashtags.includes(cleanTag)) {
    editingPost.value.hashtags.push(cleanTag);
    
    // Also add to available hashtags for future use
    if (!availableHashtags.value.includes(cleanTag)) {
      availableHashtags.value.push(cleanTag);
    }
    
    // Optionally also update the content area if you want the hashtag to appear there
    if (postContentEditor.value) {
      postContentEditor.value.innerHTML += ` #${cleanTag}`;
      updatePostContent();
    }
  }
  
  // Clear the input
  customHashtag.value = '';
};

const switchEditorTab = (tabId) => {
  activeEditorTab.value = tabId;
};

const hasMedia = computed(() => {
  return !!selectedMedia.value;
});

const openPublishModal = () => {
  if (!publishModal.value && window.bootstrap) {
    const modalElement = document.getElementById('publishModal');
    if (modalElement) {
      publishModal.value = new window.bootstrap.Modal(modalElement);
    }
  }
  
  // Reset selections
  publishError.value = '';
  
  // Pre-select platforms based on connection status
  selectedPlatforms.value.facebook = socialMediaStore.facebook.connected;
  selectedPlatforms.value.instagram = socialMediaStore.instagram.connected && hasMedia.value;
  selectedPlatforms.value.linkedin = socialMediaStore.linkedin.connected;
  
  console.log("Selected Platforms are: ", selectedPlatforms.value);
  console.log("Social Media Store is: ", socialMediaStore);
  console.log("Has Media is: ", hasMedia.value);
  // Pre-select first page for each platform if available
  if (socialMediaStore.facebook.pages.length > 0) {
    selectedPages.value.facebook = socialMediaStore.facebook.pages[0].page_id;
  }
  
  if (socialMediaStore.instagram.accounts.length > 0) {
    console.log("Instagram Account id is: ", socialMediaStore.instagram.accounts[0].id);
    console.log("Selected Pages is: ", selectedPages.value);
    selectedPages.value.instagram = socialMediaStore.instagram.accounts[0].id;
    console.log("Selected Pages after instagram is: ", selectedPages.value);
  }
  
  // Show the modal
  if (publishModal.value) {
    publishModal.value.show();
  } else if (window.$ && $('#publishModal').length) {
    $('#publishModal').modal('show');
  }
};

const connectFacebook = async () => {
  try {
    socialMediaStore.$patch({
      facebook: {
        ...socialMediaStore.facebook,
        connecting: true
      }
    });
    
    const authUrl = await getAuthUrl('facebook');
    
    if (!authUrl) {
      throw new Error('No authentication URL received');
    }
    
    // Redirect to auth URL
    window.location.href = authUrl;
  } catch (err) {
    console.error('Facebook connection error:', err);
    publishError.value = `Failed to connect to Facebook: ${err.message}`;
    
    socialMediaStore.$patch({
      facebook: {
        ...socialMediaStore.facebook,
        connecting: false
      }
    });
  }
};

const connectInstagram = async () => {
  try {
    socialMediaStore.$patch({
      instagram: {
        ...socialMediaStore.instagram,
        connecting: true
      }
    });
    
    const authUrl = await getInstagramAuthUrl();
    
    if (!authUrl) {
      throw new Error('No authentication URL received');
    }
    
    // Redirect to auth URL
    window.location.href = authUrl;
  } catch (err) {
    console.error('Instagram connection error:', err);
    publishError.value = `Failed to connect to Instagram: ${err.message}`;
    
    socialMediaStore.$patch({
      instagram: {
        ...socialMediaStore.instagram,
        connecting: false
      }
    });
  }
};

const connectLinkedin = async () => {
  try {
    socialMediaStore.$patch({
      linkedin: {
        ...socialMediaStore.linkedin,
        connecting: true
      }
    });
    
    const authUrl = await getAuthUrl('linkedin');
    
    if (!authUrl) {
      throw new Error('No authentication URL received');
    }
    
    // Redirect to auth URL
    window.location.href = authUrl;
  } catch (err) {
    console.error('LinkedIn connection error:', err);
    publishError.value = `Failed to connect to LinkedIn: ${err.message}`;
    
    socialMediaStore.$patch({
      linkedin: {
        ...socialMediaStore.linkedin,
        connecting: false
      }
    });
  }
};

const publishToSelectedPlatforms = async () => {
  try {
    if (!anyPlatformSelected.value) {
      publishError.value = 'Please select at least one platform to publish to.';
      return;
    }
    
    isPublishing.value = true;
    publishError.value = '';
    
    // Get the content from the currently selected post
    const postContent = editingPost.value.content;
    
    // Prepare platforms data
    const platformsData = [];
    console.log("Selected Platforms are: ", selectedPlatforms.value);
    console.log("Selected Pages are: ", selectedPages.value);
    
    if (selectedPlatforms.value.facebook && selectedPages.value.facebook) {
      platformsData.push({
        platform_id: 1, // Facebook platform_id
        page_id: selectedPages.value.facebook,
        content: postContent,
        media: hasMedia.value ? mediaFile.value : null
      });
    }
    
    if (selectedPlatforms.value.instagram && selectedPages.value.instagram && hasMedia.value) {
      platformsData.push({
        platform_id: 2, // Instagram platform_id
        page_id: selectedPages.value.instagram,
        content: postContent,
        media: mediaFile.value
      });
    }
    
    if (selectedPlatforms.value.linkedin) {
      platformsData.push({
        platform_id: 3, // LinkedIn platform_id
        content: postContent,
        media: hasMedia.value ? mediaFile.value : null
      });
    }
    
    console.log('Publishing to platforms:', platformsData);
    
    // Call the service to publish posts
    const results = await publishPosts(platformsData);
    console.log('Published results:', results);
    
    // Close the modal
    if (publishModal.value) {
      publishModal.value.hide();
    }
    
    // Show success notification
    $.notify({
      title: 'Success',
      message: 'Your post has been published successfully!'
    }, {
      type: 'success',
      allow_dismiss: true
    });
    
  } catch (error) {
    console.error('Error publishing posts:', error);
    publishError.value = error.message || 'Failed to publish posts. Please try again.';
    
    // Show error notification
    $.notify({
      title: 'Error',
      message: 'Failed to publish posts. Please try again.'
    }, {
      type: 'danger',
      allow_dismiss: true
    });
  } finally {
    isPublishing.value = false;
  }
};

onMounted(async () => {
  // Remove existing resources before initializing new ones
  await removeDynamicCss();
  await removeDynamicJs();
  
  // Initialize new resources
  await initializeCss();
  await initializeScripts();

  // Initialize the Bootstrap modal after component is mounted
  if (postEditorModalEl.value) {
    postEditorModal.value = new window.bootstrap.Modal(postEditorModalEl.value);
  }
  
  // Use the global bootstrap object if available
  if (window.bootstrap) {
    if (postEditorModalEl.value) {
      postEditorModal.value = new window.bootstrap.Modal(postEditorModalEl.value);
    }
    
    if (mediaPreviewModalEl.value) {
      mediaPreviewModal.value = new window.bootstrap.Modal(mediaPreviewModalEl.value);
    }
  }

  // Initialize the publish modal
  if (publishModalEl.value) {
    publishModal.value = new window.bootstrap.Modal(publishModalEl.value);
  }

  socialMediaStore.initializeFromStorage();
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
  font-size: 10px;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.hashtag-badge i:hover {
  opacity: 1;
}

.modal-dialog.modal-xl {
  max-width: 1140px;
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

/* Editor Tabs Styling */
.nav-tabs {
  border-bottom: 1px solid #e0e0e0;
  background-color: #f5f5f5;
  border-radius: 8px 8px 0 0;
}

.editor-tab {
  font-weight: 600;
  color: #666;
  position: relative;
  transition: all 0.3s ease;
  padding: 12px 25px;
  margin: 0 10px;
}

.editor-tab.active {
  color: #006666;
  background-color: transparent;
  border-color: transparent;
}

.editor-tab.active:after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 3px;
  background: #006666;
  border-radius: 3px 3px 0 0;
}

/* Rich Text Editor Styling */
.rich-text-editor {
  border: none;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  overflow: hidden;
}

.editor-toolbar {
  background-color: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
  padding: 10px 15px;
  display: flex;
  align-items: center;
}

.editor-btn {
  background: transparent;
  border: none;
  color: #495057;
  font-size: 14px;
  padding: 6px 10px;
  margin-right: 5px;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.editor-btn:hover {
  background-color: rgba(0, 102, 102, 0.1);
  color: #006666;
}

.editor-divider {
  width: 1px;
  height: 24px;
  background-color: #dee2e6;
  margin: 0 10px;
}

.editor-content {
  min-height: 200px;
  max-height: 400px;
  overflow-y: auto;
  padding: 15px;
  line-height: 1.7;
  color: #212529;
  font-size: 15px;
  outline: none;
  border: none;
  background-color: #fff;
}

.editor-content:focus {
  box-shadow: none;
}

/* Hashtags Styling */
.selected-hashtags-container {
  padding: 12px;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px solid #e9ecef;
}

.hashtag-search .form-control {
  border-radius: 50px;
  padding: 10px 18px;
  border: 1px solid #e0e0e0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.hashtag-cloud {
  padding: 15px;
  background-color: #f8f9fa;
  border-radius: 10px;
  border: 1px solid #e9ecef;
  display: flex;
  flex-wrap: wrap;
  max-height: 250px;
  overflow-y: auto;
}

.hashtag-badge {
  background-color: #ffffff;
  color: #555;
  border: 1px solid #e0e0e0;
  font-weight: 500;
  padding: 8px 14px;
  border-radius: 50px;
  font-size: 14px;
  transition: all 0.2s ease;
  cursor: pointer;
}

.hashtag-badge:hover {
  background-color: #f0f8f8;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
}

.hashtag-badge.hashtag-selected {
  background-color: #006666;
  color: white;
  border-color: #006666;
}

.hashtag-badge i {
  cursor: pointer;
  font-size: 10px;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.hashtag-badge i:hover {
  opacity: 1;
}

.empty-hashtags {
  padding: 15px;
  text-align: center;
  background-color: #f8f9fa;
  border-radius: 8px;
  border: 1px dashed #dee2e6;
}

/* Modal styling */
.modal-content {
  border: none;
  border-radius: 15px;
  box-shadow: 0 10px 50px rgba(0, 0, 0, 0.1);
}

.modal-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
  background-color: #f8f9fa;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
  background-color: #f8f9fa;
}

.modal-title {
  font-weight: 700;
  color: #333;
}

.form-label {
  font-weight: 600;
  color: #555;
  margin-bottom: 10px;
}

/* Trending hashtags style */
.trending-hashtag {
  background-color: #f8f4ff;
  border-color: #d1c4e9;
  color: #673ab7;
}

.trending-hashtag:hover {
  background-color: #ede7f6;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(103, 58, 183, 0.2);
}

.trending-hashtag.hashtag-selected {
  background-color: #673ab7;
  color: white;
  border-color: #5e35b1;
}

.trending-hashtags {
  background-color: #f5f0ff;
  border: 1px solid #e1d5f5;
}

/* Custom hashtag input styling */
.input-group {
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.input-group-text {
  background-color: #f5f5f5;
  border-color: #e0e0e0;
  color: #666;
  font-weight: 600;
}

.input-group .form-control {
  border-color: #e0e0e0;
}

.input-group .btn {
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  border: none;
  color: white;
  font-weight: 500;
}

/* Media Preview Styles */
.media-preview-container {
  background-color: #f8f9fa;
  border-radius: 8px;
  overflow: hidden;
}

.media-content {
  padding: 0;
  background-color: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.media-preview-image {
  max-width: 100%;
  max-height: 400px;
  object-fit: contain;
}

.media-header {
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.selected-media-preview {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.media-caption {
  background-color: rgba(255,255,255,0.95);
  padding: 8px 12px;
  border-radius: 0 0 8px 8px;
}

/* Add animation for the modal */
.modal.fade .modal-dialog {
  transition: transform 0.3s ease-out;
}

.modal.fade .modal-content {
  transition: opacity 0.3s ease;
}

@media (max-width: 767.98px) {
  .media-content {
    min-height: 200px;
  }
  
  .media-preview-image {
    max-height: 200px;
  }
}

/* Selected file info */
.selected-file-info {
  padding: 8px 12px;
  background-color: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
}

/* Split view styles */
.media-preview-pane {
  background-color: #000;
  border-right: 1px solid #dee2e6;
  max-height: 600px;
  overflow: hidden;
}

.editor-container {
  max-height: 600px;
  overflow-y: auto;
}

.editor-tabs {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: white;
  padding-top: 8px;
}

.editor-tab-btn {
  transition: all 0.2s ease;
}

.editor-tab-btn:hover {
  transform: translateY(-2px);
}

/* Object fit for images */
.object-fit-cover {
  object-fit: cover;
  object-position: center;
}

/* Make the editor taller in split view */
.split-view .editor-content {
  min-height: 250px;
}

/* Modal size adjustment */
@media (min-width: 1200px) {
  .modal-xl {
    max-width: 1140px;
  }
}

@media (max-width: 767.98px) {
  .media-preview-pane {
    max-height: 300px;
  }
}

/* PostEditor Styles */
.editor-container {
  max-height: 400px; /* Reduced height from original */
  overflow-y: auto;
  padding: 15px;
}

/* Make modal smaller */
.post-editor-modal .modal-lg {
  max-width: 900px; /* Reduced width from default 992px */
}

/* Media display on left side */
.media-carousel-container {
  position: relative;
  height: 100%;
  background-color: #000;
}

.media-preview-pane {
  height: 400px; /* Reduced height to match editor */
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
}

/* Make images fit within container */
.editor-media-img {
  max-width: 100%;
  max-height: 400px;
  object-fit: contain; /* Changed from cover to contain to ensure entire image fits */
  display: block;
  margin: 0 auto;
}

/* Caption styling */
.media-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0,0,0,0.6);
  color: white;
  padding: 8px 12px;
  font-size: 0.9rem;
}

/* Navigation buttons */
.media-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background-color: rgba(255,255,255,0.5);
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  cursor: pointer;
  transition: all 0.2s ease;
}

.media-nav-btn:hover {
  background-color: rgba(255,255,255,0.8);
}

.media-prev-btn {
  left: 10px;
}

.media-next-btn {
  right: 10px;
}

/* Media indicators for multiple images */
.media-indicators {
  position: absolute;
  bottom: 12px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  z-index: 2;
}

.media-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background-color: rgba(255,255,255,0.5);
  margin: 0 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.media-indicator.active {
  background-color: white;
  transform: scale(1.3);
}

/* Editor tabs */
.editor-tabs {
  position: sticky;
  top: 0;
  z-index: 10;
  background-color: white;
  padding: 10px 15px;
  border-bottom: 1px solid #eee;
}

.editor-tab-btn {
  transition: all 0.2s ease;
  font-size: 0.9rem;
  padding: 6px 12px;
}

.editor-tab-btn:hover {
  transform: translateY(-2px);
}

/* Rich text editor */
.rich-text-editor {
  border: 1px solid #dee2e6;
  border-radius: 4px;
  overflow: hidden;
}

.editor-toolbar {
  display: flex;
  align-items: center;
  padding: 6px 10px; /* Reduced padding */
  background-color: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.editor-btn {
  margin-right: 5px;
  background: transparent;
  color: #495057;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  width: 24px; /* Smaller buttons */
  height: 24px; /* Smaller buttons */
  font-size: 0.8rem; /* Smaller icons */
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.editor-btn:hover {
  background-color: #e9ecef;
}

.editor-divider {
  width: 1px;
  height: 20px;
  background-color: #dee2e6;
  margin: 0 8px;
}

.editor-content {
  min-height: 120px; /* Reduced from 160px */
  max-height: 200px; /* Reduced from 250px */
  overflow-y: auto;
  padding: 10px; /* Reduced padding */
  font-size: 0.95rem; /* Slightly smaller text */
  background-color: #fff;
}

/* Make sure hashtag selector is compact */
.hashtags-content {
  padding: 0 15px;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
  .media-preview-pane {
    height: 220px; /* Even smaller on mobile */
  }
  
  .editor-media-img {
    max-height: 220px;
  }
  
  .editor-content {
    min-height: 100px;
    max-height: 150px;
  }
  
  .post-editor-modal .modal-lg {
    max-width: 95%;
    margin: 0 auto;
  }
}

.platform-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 15px;
  transition: all 0.2s ease;
  background-color: #f8f9fa;
  height: 100%;
}

.platform-card.selected {
  border-color: #4CAF50;
  background-color: rgba(76, 175, 80, 0.05);
  box-shadow: 0 0 0 1px rgba(76, 175, 80, 0.5);
}

.platform-card.disabled {
  opacity: 0.7;
  background-color: #f1f1f1;
}

.platform-icon {
  font-size: 1.5rem;
}

.platform-card .fab.fa-facebook {
  color: #1877F2;
}

.platform-card .fab.fa-instagram {
  color: #E1306C;
}

.platform-card .fab.fa-linkedin {
  color: #0077B5;
}

.form-check-label {
  cursor: pointer;
}

.form-check-input:disabled + .form-check-label {
  cursor: not-allowed;
  opacity: 0.7;
}
</style>