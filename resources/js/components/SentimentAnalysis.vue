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
                  <h4>Sentiment Analysis</h4>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <!-- Platform Tabs -->
              <div class="col-12 mb-4">
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
                                'selected': selectedPost && selectedPost.id === post.id,
                                'animate-in': true
                              }"
                              :style="{ animationDelay: `${index * 0.1}s` }"
                              @click="selectPost(post)"
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
                                  @click.stop="analyzeSentiment(post)"
                                >
                                  <i class="fas fa-chart-bar me-1"></i> Analyze Sentiment
                                </button>
                                
                                <a 
                                  v-if="post.post_url" 
                                  :href="post.post_url" 
                                  target="_blank"
                                  class="btn btn-sm btn-outline-light view-btn"
                                  @click.stop
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

              <!-- Analysis Results -->
              <div v-if="sentimentData" class="col-12 sentiment-results animate-fade-in">
                <div class="row">
                  <!-- Selected Post Info -->
                  <div class="col-12 mb-4">
                    <div class="card selected-post-card">
                      <div class="card-body">
                        <div class="d-flex align-items-center">
                          <div class="platform-icon">
                            <i :class="getPlatformIcon(selectedPost.platform)"></i>
                          </div>
                          <div class="post-info">
                            <h5>{{ truncateText(selectedPost.initial_description, 100) }}</h5>
                            <div class="post-meta">
                              <span class="badge" :class="getStatusBadgeClass(selectedPost.status)">
                                {{ selectedPost.status }}
                              </span>
                              <span class="post-date">
                                <i class="far fa-calendar-alt me-1"></i> 
                                {{ formatDate(selectedPost.created_at) }}
                              </span>
                              <span class="platform-name">
                                <i :class="getPlatformIcon(selectedPost.platform)" class="me-1"></i>
                                {{ selectedPost.platform }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Overview -->
                  <div class="col-lg-4 col-md-6 mb-4 animate-in">
                    <div class="card h-100 sentiment-overview-card">
                      <div class="card-header">
                        <h5>Sentiment Overview</h5>
                      </div>
                      <div class="card-body">
                        <div class="d-flex align-items-center justify-content-center mb-4">
                          <div class="sentiment-badge" :class="sentimentClass">
                            {{ sentimentData.analysis.overall_sentiment }}
                          </div>
                        </div>
                        
                        <div class="row text-center">
                          <div class="col-4 sentiment-stat animate-in" style="animation-delay: 0.1s">
                            <h3 class="text-success">{{ sentimentData.analysis.sentiment_distribution.positive || 0 }}</h3>
                            <p>Positive</p>
                          </div>
                          <div class="col-4 sentiment-stat animate-in" style="animation-delay: 0.2s">
                            <h3 class="text-warning">{{ sentimentData.analysis.sentiment_distribution.neutral || 0 }}</h3>
                            <p>Neutral</p>
                          </div>
                          <div class="col-4 sentiment-stat animate-in" style="animation-delay: 0.3s">
                            <h3 class="text-danger">{{ sentimentData.analysis.sentiment_distribution.negative || 0 }}</h3>
                            <p>Negative</p>
                          </div>
                        </div>
                        
                        <div class="text-center mt-3 animate-in" style="animation-delay: 0.4s">
                          <h6>Average Score: 
                            <span :class="{
                              'text-success': sentimentData.analysis.average_score > 0.1,
                              'text-danger': sentimentData.analysis.average_score < -0.1,
                              'text-warning': sentimentData.analysis.average_score >= -0.1 && sentimentData.analysis.average_score <= 0.1
                            }">
                              {{ sentimentData.analysis.average_score }}
                            </span>
                          </h6>
                          <p class="mb-0">Based on {{ sentimentData.comment_count }} comments</p>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Charts -->
                  <div class="col-lg-8 col-md-6 mb-4 animate-in" style="animation-delay: 0.2s">
                    <div class="card h-100 charts-card">
                      <div class="card-header">
                        <h5>Sentiment Charts</h5>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6 mb-3 chart-container-wrapper animate-in" style="animation-delay: 0.3s">
                            <div class="chart-container">
                              <img v-if="sentimentData.charts.sentiment_distribution" 
                                   :src="`data:image/png;base64,${sentimentData.charts.sentiment_distribution}`" 
                                   class="img-fluid" 
                                   alt="Sentiment Distribution" />
                            </div>
                          </div>
                          <div class="col-md-6 mb-3 chart-container-wrapper animate-in" style="animation-delay: 0.4s">
                            <div class="chart-container">
                              <img v-if="sentimentData.charts.score_distribution" 
                                   :src="`data:image/png;base64,${sentimentData.charts.score_distribution}`" 
                                   class="img-fluid" 
                                   alt="Score Distribution" />
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Common Words -->
                  <div class="col-lg-6 mb-4 animate-in" style="animation-delay: 0.3s">
                    <div class="card h-100 words-card">
                      <div class="card-header">
                        <h5>Common Words</h5>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <h6 class="text-success mb-3">Positive Words</h6>
                            <ul class="list-unstyled">
                              <li v-for="(word, index) in sentimentData.analysis.common_positive_words" 
                                  :key="`positive-${index}`"
                                  class="mb-2 word-item"
                                  :style="{ animationDelay: `${0.1 * index}s` }"
                              >
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ word }}
                              </li>
                            </ul>
                          </div>
                          <div class="col-md-6">
                            <h6 class="text-danger mb-3">Negative Words</h6>
                            <ul class="list-unstyled">
                              <li v-for="(word, index) in sentimentData.analysis.common_negative_words" 
                                  :key="`negative-${index}`"
                                  class="mb-2 word-item"
                                  :style="{ animationDelay: `${0.1 * index}s` }"
                              >
                                <i class="fas fa-times-circle text-danger me-2"></i>
                                {{ word }}
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Comments Sample -->
                  <div class="col-lg-6 mb-4 animate-in" style="animation-delay: 0.4s">
                    <div class="card h-100 comments-card">
                      <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Comment Samples</h5>
                        <div class="badge bg-primary">{{ sentimentData.comment_count }} Comments</div>
                      </div>
                      <div class="card-body comment-samples">
                        <div v-for="(comment, index) in sentimentData.analysis.comment_sentiments.slice(0, 5)" 
                             :key="`comment-${index}`"
                             class="comment-item"
                             :class="{
                               'comment-positive': comment.sentiment === 'positive',
                               'comment-neutral': comment.sentiment === 'neutral',
                               'comment-negative': comment.sentiment === 'negative',
                             }"
                             :style="{ animationDelay: `${0.1 * index}s` }"
                        >
                          <div class="comment-text">{{ comment.text }}</div>
                          <div class="comment-meta">
                            <span class="badge" :class="getSentimentBadgeClass(comment.sentiment)">
                              {{ comment.sentiment }}
                            </span>
                            <span class="score">Score: {{ comment.score }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Loading State -->
              <div v-if="isAnalyzing" class="col-12 text-center py-5 loading-container">
                <div class="loading-animation">
                  <div class="loading-spinner"></div>
                  <h5 class="mt-3">Analyzing Sentiment</h5>
                  <p>Please wait while we analyze the comments on your post...</p>
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
const selectedPost = ref(null);
const isAnalyzing = ref(false);
const sentimentData = ref(null);
const isLoading = ref(true);

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

const getSentimentBadgeClass = (sentiment) => {
  if (sentiment === 'positive') return 'bg-success';
  if (sentiment === 'negative') return 'bg-danger';
  return 'bg-warning';
};

const selectPost = (post) => {
  selectedPost.value = post;
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
  
  selectedPost.value = post;
  sentimentData.value = null;
  isAnalyzing.value = true;
  
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

    // Scroll to loading area
    setTimeout(() => {
      const loadingEl = document.querySelector('.loading-container');
      if (loadingEl) {
        loadingEl.scrollIntoView({ behavior: 'smooth' });
      }
    }, 100);
    
    console.log('Analyzing post:', post.response_post_id, post.platform);
    const result = await analyzePostSentiment(post.response_post_id, post.platform);
    
    // Ensure result has expected structure
    console.log('Sentiment analysis result:', result);
    
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
      },
      charts: {
        ...defaultSentimentData.charts,
        ...(result.charts || {})
      }
    };
    
    // Scroll to results after they load
    setTimeout(() => {
      const resultsEl = document.querySelector('.sentiment-results');
      if (resultsEl) {
        resultsEl.scrollIntoView({ behavior: 'smooth' });
      }
    }, 300);
    
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
    // Ensure loading state is reset even if there's an error
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

.animate-fade-in {
  animation: fadeIn 0.8s ease forwards;
  opacity: 0;
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

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

/* Post Card Styling */
.post-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.6), rgba(240, 240, 240, 0.8));
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
  cursor: pointer;
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

.post-card.selected {
  border-color: #00a3a3;
  box-shadow: 0 0 0 2px rgba(0, 163, 163, 0.3);
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
}

.analyze-btn:hover {
  background: linear-gradient(135deg, #00c4c4, #00e5e5);
  transform: translateY(-2px);
}

.view-btn {
  border-color: #666;
  color: #ccc;
  transition: all 0.3s ease;
}

.view-btn:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
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
  color: #3c3c4a;
  margin-bottom: 1rem;
}

/* Loading Animation */
.loading-animation {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem 0;
  color: #333;
}

.loading-spinner {
  width: 60px;
  height: 60px;
  border: 5px solid rgba(0, 163, 163, 0.2);
  border-top-color: #00a3a3;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1.5rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Selected Post Card */
.selected-post-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.7), rgba(240, 240, 240, 0.9));
  border-left: 4px solid #00a3a3;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  color: #333;
}

.platform-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, rgba(240, 240, 240, 0.9), rgba(230, 230, 230, 1));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1.5rem;
  flex-shrink: 0;
}

.platform-icon i {
  font-size: 1.8rem;
  color: #00a3a3;
}

.post-info {
  flex-grow: 1;
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-top: 0.5rem;
}

.platform-name {
  color: #666;
  font-size: 0.9rem;
}

/* Sentiment Badge & Cards */
.sentiment-badge {
  display: inline-block;
  padding: 1rem 2rem;
  border-radius: 50px;
  font-size: 1.2rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.sentiment-positive {
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.9), rgba(139, 195, 74, 0.9));
  color: white;
}

.sentiment-negative {
  background: linear-gradient(135deg, rgba(244, 67, 54, 0.9), rgba(255, 87, 34, 0.9));
  color: white;
}

.sentiment-neutral {
  background: linear-gradient(135deg, rgba(255, 193, 7, 0.9), rgba(255, 235, 59, 0.9));
  color: #333;
}

.sentiment-overview-card, .charts-card, .words-card, .comments-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.7), rgba(240, 240, 240, 0.9));
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  color: #333;
}

.sentiment-overview-card:hover, .charts-card:hover, .words-card:hover, .comments-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.sentiment-stat {
  transition: all 0.3s ease;
}

.sentiment-stat:hover {
  transform: translateY(-5px);
}

.chart-container-wrapper {
  transition: all 0.3s ease;
}

.chart-container-wrapper:hover {
  transform: translateY(-5px);
}

.chart-container {
  background: rgba(250, 250, 250, 0.7);
  border-radius: 8px;
  padding: 1rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* Word Items */
.word-item {
  animation: slideInLeft 0.5s ease forwards;
  opacity: 0;
  transform: translateX(-20px);
  padding: 0.5rem 0.8rem;
  border-radius: 6px;
  transition: all 0.3s ease;
  background: rgba(240, 240, 240, 0.7);
  margin-bottom: 0.6rem !important;
  color: #333;
}

.word-item:hover {
  background: rgba(230, 230, 230, 0.8);
  transform: translateX(5px);
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Comments Styling */
.comment-samples {
  max-height: 400px;
  overflow-y: auto;
}

.comment-item {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: rgba(250, 250, 250, 0.7);
  border-left: 4px solid #9E9E9E;
  animation: slideInRight 0.5s ease forwards;
  opacity: 0;
  transform: translateX(20px);
  transition: all 0.3s ease;
  color: #333;
}

.comment-item:hover {
  transform: translateX(-5px);
  background: rgba(240, 240, 240, 0.8);
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.comment-positive {
  border-left-color: #4CAF50;
}

.comment-negative {
  border-left-color: #F44336;
}

.comment-neutral {
  border-left-color: #FFC107;
}

.comment-text {
  margin-bottom: 0.5rem;
  color: #333;
}

.comment-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
}

.score {
  color: #666;
}

/* Tab Styling */
.nav-tabs {
  border-color: #e0e0e0;
  margin-bottom: 0;
}

.nav-tabs .nav-link {
  color: #444;
  border: none;
  padding: 0.8rem 1.2rem;
  border-radius: 8px 8px 0 0;
  transition: all 0.3s ease;
  margin-right: 5px;
}

.nav-tabs .nav-link:hover {
  background-color: rgba(0, 163, 163, 0.1);
  border-color: transparent;
}

.nav-tabs .nav-link.active {
  color: white;
  background: linear-gradient(to bottom, #00a3a3, #008080);
  border-color: transparent;
}

/* Scrollbar styling */
.comment-samples::-webkit-scrollbar {
  width: 6px;
}

.comment-samples::-webkit-scrollbar-track {
  background: rgba(240, 240, 240, 0.5);
  border-radius: 3px;
}

.comment-samples::-webkit-scrollbar-thumb {
  background: #00a3a3;
  border-radius: 3px;
}

.comment-samples::-webkit-scrollbar-thumb:hover {
  background: #008f8f;
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
</style> 