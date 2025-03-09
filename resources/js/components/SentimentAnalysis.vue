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
              <!-- Search Section -->
              <div class="col-12 mb-4">
                <div class="card">
                  <div class="card-header">
                    <h5>Analyze Post Comments</h5>
                  </div>
                  <div class="card-body">
                    <!-- Platform Selection -->
                    <div class="row mb-4">
                      <div class="col-md-6">
                        <label class="form-label">Platform</label>
                        <select 
                          class="form-select" 
                          v-model="selectedPlatform"
                        >
                          <option value="instagram">Instagram</option>
                          <option value="facebook">Facebook</option>
                        </select>
                      </div>
                      <div class="col-md-6">
                        <label class="form-label">Post ID</label>
                        <input 
                          type="text" 
                          class="form-control" 
                          v-model="postId"
                          placeholder="Enter post ID"
                        />
                      </div>
                    </div>
                    
                    <button 
                      class="btn btn-primary w-100" 
                      @click="analyzeSentiment"
                      :disabled="!postId || isAnalyzing"
                    >
                      <i class="fas fa-chart-bar me-2"></i>
                      {{ isAnalyzing ? 'Analyzing...' : 'Analyze Sentiment' }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Analysis Results -->
              <div v-if="sentimentData" class="col-12">
                <div class="row">
                  <!-- Overview -->
                  <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
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
                          <div class="col-4">
                            <h3 class="text-success">{{ sentimentData.analysis.sentiment_distribution.positive || 0 }}</h3>
                            <p>Positive</p>
                          </div>
                          <div class="col-4">
                            <h3 class="text-warning">{{ sentimentData.analysis.sentiment_distribution.neutral || 0 }}</h3>
                            <p>Neutral</p>
                          </div>
                          <div class="col-4">
                            <h3 class="text-danger">{{ sentimentData.analysis.sentiment_distribution.negative || 0 }}</h3>
                            <p>Negative</p>
                          </div>
                        </div>
                        
                        <div class="text-center mt-3">
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
                  <div class="col-lg-8 col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header">
                        <h5>Sentiment Charts</h5>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <div class="chart-container">
                              <img v-if="sentimentData.charts.sentiment_distribution" 
                                   :src="`data:image/png;base64,${sentimentData.charts.sentiment_distribution}`" 
                                   class="img-fluid" 
                                   alt="Sentiment Distribution" />
                            </div>
                          </div>
                          <div class="col-md-6 mb-3">
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
                  <div class="col-lg-6 mb-4">
                    <div class="card h-100">
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
                                  class="mb-2"
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
                                  class="mb-2"
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
                  <div class="col-lg-6 mb-4">
                    <div class="card h-100">
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
import { analyzeSentiment as analyzePostSentiment } from '../services/SentimentAnalysisService';
import { useDynamicResources } from '../composables/useDynamicResources';

// State
const selectedPlatform = ref('instagram');
const postId = ref('');
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
const getSentimentBadgeClass = (sentiment) => {
  if (sentiment === 'positive') return 'bg-success';
  if (sentiment === 'negative') return 'bg-danger';
  return 'bg-warning';
};

const analyzeSentiment = async () => {
  if (!postId.value || isAnalyzing.value) return;
  
  isAnalyzing.value = true;
  try {
    const result = await analyzePostSentiment(postId.value, selectedPlatform.value);
    sentimentData.value = result;
    
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
  } finally {
    isAnalyzing.value = false;
  }
};

// Lifecycle hooks
onMounted(async () => {
  await removeDynamicCss();
  await removeDynamicJs();
  await initializeCss();
  await initializeScripts();
  isLoading.value = false;
});
</script>

<style scoped>
/* Keep all existing styles from your component */

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

/* Make sure other styles remain the same */
.sentiment-badge {
  display: inline-block;
  padding: 1rem 2rem;
  border-radius: 50px;
  font-size: 1.2rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.sentiment-positive {
  background: linear-gradient(135deg, #4CAF50, #8BC34A);
  color: white;
}

.sentiment-negative {
  background: linear-gradient(135deg, #F44336, #FF5722);
  color: white;
}

.sentiment-neutral {
  background: linear-gradient(135deg, #FFC107, #FFEB3B);
  color: #333;
}

.chart-container {
  background: #1c1c29;
  border-radius: 8px;
  padding: 1rem;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.comment-samples {
  max-height: 400px;
  overflow-y: auto;
}

.comment-item {
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  background: #1c1c29;
  border-left: 4px solid #9E9E9E;
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
  color: #f8f9fa;
}

.comment-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
}

.score {
  color: #9E9E9E;
}

/* Scrollbar styling */
.comment-samples::-webkit-scrollbar {
  width: 6px;
}

.comment-samples::-webkit-scrollbar-track {
  background: #1c1c29;
}

.comment-samples::-webkit-scrollbar-thumb {
  background: #00a3a3;
  border-radius: 3px;
}

.comment-samples::-webkit-scrollbar-thumb:hover {
  background: #008f8f;
}
</style> 