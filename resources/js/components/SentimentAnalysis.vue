<template>
  <div>
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
      <!-- Page Header Start-->
      <div class="page-header" style="margin-left: 230px; width: calc(100% - 230px);">
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
            <div>
              <h4 class="f-w-600">Sentiment Analysis</h4>
            </div>
          </div>
          <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
            <ul class="nav-menus">
              <li>
                <div class="mode"><i class="moon" data-feather="moon"> </i></div>
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
        
        <div class="page-body" style="margin-left: 230px; width: calc(100% - 230px); min-height: 100vh; margin-top: 0;">
          <div class="container-fluid">
            <div class="page-title">
              <div class="row">
                <div class="col-12 text-center">
                  <h4>Post Sentiment Analysis</h4>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <!-- Platform Tabs -->
              <div class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h5>My Posts</h5>
                    <p>Select a post to analyze its sentiment</p>
                  </div>
                  <div class="card-body">
                    <ul class="nav nav-tabs nav-primary" id="platform-tabs" role="tablist">
                      <li class="nav-item" v-for="(posts, platform) in userPosts" :key="platform">
                        <button 
                          class="nav-link" 
                          :class="{ 'active': activePlatform === platform }"
                          :id="`${platform}-tab`"
                          data-bs-toggle="tab"
                          :data-bs-target="`#${platform}-posts`"
                          type="button"
                          role="tab"
                          @click="activePlatform = platform"
                        >
                          <i :class="getPlatformIcon(platform)" class="me-2"></i>
                          {{ platform.charAt(0).toUpperCase() + platform.slice(1) }}
                          <span class="badge rounded-pill bg-primary ms-2">{{ posts.length }}</span>
                        </button>
                      </li>
                    </ul>
                    
                    <div class="tab-content" id="platform-tabs-content">
                      <div 
                        v-for="(posts, platform) in userPosts" 
                        :key="`${platform}-content`"
                        class="tab-pane fade"
                        :class="{ 'show active': activePlatform === platform }"
                        :id="`${platform}-posts`"
                        role="tabpanel"
                      >
                        <div class="row mt-4">
                          <div v-if="posts.length === 0" class="col-12 text-center py-5">
                            <div class="empty-state">
                              <i :class="getPlatformIcon(platform)" class="empty-icon"></i>
                              <h6 class="mt-3">No posts on {{ platform }}</h6>
                              <p>You haven't posted anything on {{ platform }} yet.</p>
                            </div>
                          </div>
                          
                          <div v-for="(post, index) in posts" :key="`post-${post.id}`" class="col-md-6 col-xl-4 mb-4">
                            <div 
                              class="post-card" 
                              :class="{ 
                                'animate-in': true
                              }"
                              :style="{ animationDelay: `${index * 0.1}s` }"
                            >
                              <div class="post-status">
                                <span class="badge" :class="getStatusBadgeClass(post.status)">
                                  {{ post.status }}
                                </span>
                                <span class="post-date">{{ formatDate(post.created_at) }}</span>
                              </div>
                              
                              <h6 class="post-title">{{ truncateText(post.initial_description, 80) }}</h6>
                              
                              <div class="post-content">
                                <p>{{ truncateText(post.AI_generated_description, 150) }}</p>
                              </div>
                              
                              <div class="post-footer">
                                <button 
                                  class="btn btn-sm btn-primary analyze-btn"
                                  @click="analyzeSentiment(post)"
                                  :disabled="isAnalyzing"
                                >
                                  <span v-if="!isAnalyzing || selectedPostId !== post.id">
                                    <i class="fas fa-chart-bar me-1"></i> Analyze Sentiment
                                  </span>
                                  <span v-else class="d-flex align-items-center">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Analyzing...
                                  </span>
                                </button>
                                
                                <a 
                                  v-if="post.post_url" 
                                  :href="post.post_url" 
                                  target="_blank"
                                  class="btn btn-sm btn-outline-secondary view-btn"
                                >
                                  <i class="fas fa-external-link-alt me-1"></i> View
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Posts State -->
              <div v-if="Object.keys(userPosts).length === 0 && !isLoading" class="col-12 text-center py-5">
                <div class="empty-state">
                  <i class="fas fa-chart-line empty-icon"></i>
                  <h5 class="mt-3">No Posts Available</h5>
                  <p>You haven't published any posts yet. Create a post first to analyze sentiment.</p>
                  <router-link to="/create-post" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i> Create a Post
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- footer start-->
        <footer class="footer" style="margin-left: 230px; width: calc(100% - 230px);">
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

    <!-- Sentiment Modal -->
    <div class="modal fade" id="sentimentModal" tabindex="-1" aria-labelledby="sentimentModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sentiment-modal">
          <div class="modal-header border-0">
            <h5 class="modal-title" id="sentimentModalLabel">Post Sentiment Analysis</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <div v-if="sentimentData">
              <div class="sentiment-result mb-4">
                <div class="sentiment-icon" :class="sentimentClass">
                  <i class="fas" :class="getSentimentIcon(sentimentData.analysis.overall_sentiment)"></i>
                </div>
                <div class="sentiment-label">
                  {{ sentimentData.analysis.overall_sentiment.toUpperCase() }}
                </div>
              </div>
              <div class="sentiment-stats">
                <p>Based on analysis of {{ sentimentData.comment_count || 0 }} comments</p>
              </div>
              <div class="sentiment-message">
                {{ getSentimentMessage(sentimentData.analysis.overall_sentiment) }}
              </div>
            </div>
            <div v-else class="sentiment-loading">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-3">Analyzing sentiment...</p>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import DashboardSidebar from './DashboardSidebar.vue';
import { analyzeSentiment as analyzePostSentiment, getUserPosts as fetchUserPosts } from '../services/SentimentAnalysisService';
import { useDynamicResources } from '../composables/useDynamicResources';

// State
const userPosts = ref({});
const activePlatform = ref('');
const isAnalyzing = ref(false);
const sentimentData = ref(null);
const isLoading = ref(true);
const selectedPostId = ref(null);

// CSS and JS resources
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
  'resources/js/legacy/notify/bootstrap-notify.min.js',
  'resources/js/legacy/script.js'
];

// Initialize dynamic resources
const { removeDynamicCss, initializeCss, removeDynamicJs, initializeScripts } = useDynamicResources(isLoading, cssFiles, JsFiles);

// Computed
const sentimentClass = computed(() => {
  if (!sentimentData.value) return '';
  
  const sentiment = sentimentData.value.analysis.overall_sentiment;
  if (sentiment === 'positive') return 'sentiment-positive';
  if (sentiment === 'negative') return 'sentiment-negative';
  return 'sentiment-neutral';
});

// Methods
const fetchPosts = async () => {
  try {
    isLoading.value = true;
    const posts = await fetchUserPosts();
    userPosts.value = posts;
    
    // Set active platform to the first one that has posts
    const platforms = Object.keys(posts);
    if (platforms.length > 0) {
      let firstWithPosts = platforms.find(platform => posts[platform].length > 0);
      activePlatform.value = firstWithPosts || platforms[0];
    }
    
    // Show success notification
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Posts loaded successfully!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    console.error('Error fetching posts:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: error.message || 'Failed to load posts'
      }, {
        type: 'danger'
      });
    }
  } finally {
    isLoading.value = false;
  }
};

const getPlatformIcon = (platform) => {
  const iconMap = {
    'facebook': 'fab fa-facebook',
    'instagram': 'fab fa-instagram',
    'linkedin': 'fab fa-linkedin',
    'twitter': 'fab fa-twitter',
    'default': 'fas fa-share-alt'
  };
  
  return iconMap[platform.toLowerCase()] || iconMap.default;
};

const getStatusBadgeClass = (status) => {
  const statusMap = {
    'published': 'bg-success',
    'pending': 'bg-warning',
    'scheduled': 'bg-info',
    'failed': 'bg-danger',
    'draft': 'bg-secondary'
  };
  
  return statusMap[status.toLowerCase()] || 'bg-secondary';
};

const getSentimentIcon = (sentiment) => {
  if (sentiment === 'positive') return 'fa-smile';
  if (sentiment === 'negative') return 'fa-frown';
  return 'fa-meh';
};

const getSentimentMessage = (sentiment) => {
  if (sentiment === 'positive') return 'Your audience is responding positively to this post!';
  if (sentiment === 'negative') return 'Your audience seems to have concerns about this post.';
  return 'Your audience has a neutral reaction to this post.';
};

const truncateText = (text, maxLength) => {
  if (!text) return '';
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const analyzeSentiment = async (post) => {
  if (isAnalyzing.value) return;
  
  selectedPostId.value = post.id;
  sentimentData.value = null;
  isAnalyzing.value = true;
  
  // Show modal
  const sentimentModal = new window.bootstrap.Modal(document.getElementById('sentimentModal'));
  sentimentModal.show();
  
  try {
    // Check if post has a valid response_post_id
    if (!post.response_post_id) {
      if (window.$) {
        window.$.notify({
          title: 'Error',
          message: 'This post does not have a valid post ID for analysis'
        }, {
          type: 'danger'
        });
      }
      isAnalyzing.value = false;
      return;
    }
    
    console.log('Analyzing post:', post.response_post_id, post.platform);
    const result = await analyzePostSentiment(post.response_post_id, post.platform);
    
    // Create a default structure if some properties are missing
    const defaultSentimentData = {
      status: 'success',
      analysis: {
        overall_sentiment: 'neutral',
        sentiment_distribution: {
          positive: 0,
          neutral: 0,
          negative: 0
        },
        average_score: 0,
        comment_sentiments: [],
        common_positive_words: [],
        common_negative_words: []
      },
      comment_count: 0,
      charts: {
        sentiment_distribution: null,
        score_distribution: null
      }
    };
    
    // Merge the result with the default structure
    sentimentData.value = {
      ...defaultSentimentData,
      ...result,
      analysis: {
        ...defaultSentimentData.analysis,
        ...(result.analysis || {})
      }
    };
    
    // Show success notification
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Sentiment analysis completed!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    console.error('Analysis error:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: error.message || 'Failed to analyze sentiment'
      }, {
        type: 'danger'
      });
    }
    sentimentData.value = null;
  } finally {
    // Ensure loading state is reset
    isAnalyzing.value = false;
  }
};

// Lifecycle hooks
onMounted(async () => {
  await removeDynamicCss();
  await removeDynamicJs();
  await initializeCss();
  await initializeScripts();
  await fetchPosts();
});
</script>

<style scoped>
/* Fix sidebar layout */
.page-header, .page-body, .footer {
  margin-left: 230px !important;
  width: calc(100% - 230px) !important;
}

.sidebar-wrapper {
  position: fixed;
  height: 100%;
  z-index: 999;
}

/* Animations */
.animate-in {
  animation: slideIn 0.5s ease forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Post Card Styling */
.post-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.6), rgba(240, 240, 240, 0.8));
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  border-left: 4px solid #00a3a3;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  color: #333;
}

.post-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  background: linear-gradient(135deg, rgba(240, 240, 240, 0.8), rgba(230, 230, 230, 0.9));
}

.post-status {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.post-date {
  font-size: 0.8rem;
  color: #666;
}

.post-title {
  font-weight: 600;
  margin-bottom: 1rem;
  line-height: 1.4;
  color: #222;
}

.post-content {
  flex-grow: 1;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  color: #444;
  overflow: hidden;
}

.post-footer {
  display: flex;
  gap: 10px;
}

.analyze-btn {
  background: linear-gradient(135deg, #00a3a3, #00c4c4);
  border: none;
  transition: all 0.3s ease;
  min-width: 140px;
}

.analyze-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #00c4c4, #00e5e5);
  transform: translateY(-2px);
}

.view-btn {
  border-color: #666;
  color: #555;
  transition: all 0.3s ease;
}

.view-btn:hover {
  background-color: rgba(0, 0, 0, 0.1);
  color: #333;
}

/* Empty State */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem 0;
}

.empty-icon {
  font-size: 4rem;
  color: #aaa;
  margin-bottom: 1rem;
}

/* Card Headers */
.card-header {
  background: linear-gradient(135deg, rgba(240, 240, 240, 0.8), rgba(255, 255, 255, 0.8));
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.2rem 1.5rem;
}

.card-header h5 {
  margin-bottom: 0;
  color: #222;
  font-weight: 600;
}

/* Sentiment Modal */
.sentiment-modal {
  border-radius: 16px;
  overflow: hidden;
  border: none;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.modal-header, .modal-footer {
  background-color: #f8f9fa;
}

.sentiment-result {
  padding: 2rem 0;
}

.sentiment-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  margin-bottom: 1.5rem;
  font-size: 4rem;
}

.sentiment-positive {
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.2), rgba(139, 195, 74, 0.2));
  color: #4CAF50;
}

.sentiment-negative {
  background: linear-gradient(135deg, rgba(244, 67, 54, 0.2), rgba(255, 87, 34, 0.2));
  color: #F44336;
}

.sentiment-neutral {
  background: linear-gradient(135deg, rgba(255, 193, 7, 0.2), rgba(255, 235, 59, 0.2));
  color: #FFC107;
}

.sentiment-label {
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: #333;
}

.sentiment-stats {
  font-size: 1rem;
  color: #666;
  margin-bottom: 1.5rem;
}

.sentiment-message {
  font-size: 1.1rem;
  color: #555;
  margin-bottom: 1rem;
  font-style: italic;
}

.sentiment-loading {
  padding: 3rem 0;
}
</style> 