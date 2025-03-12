<template>
  <Loader v-show="isLoading"></Loader>
  <div v-show="!isLoading">
    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->
    <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
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
                  <h4>Hashtag Finder</h4>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <!-- Search Section -->
              <div class="col-12 mb-4">
                <div class="card">
                  <div class="card-body">
                    <div class="input-group">
                      <input 
                        type="text" 
                        class="form-control" 
                        v-model="searchQuery"
                        placeholder="Enter a topic to find relevant hashtags..."
                        @keyup.enter="searchHashtags"
                      >
                      <button 
                        class="btn btn-primary" 
                        @click="searchHashtags"
                        :disabled="isSearching || !searchQuery"
                      >
                        <i class="fas fa-search me-2"></i>
                        {{ isSearching ? 'Searching...' : 'Find Hashtags' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Results Section -->
              <div class="col-12">
                <div class="row">
                  <!-- Popular Hashtags -->
                  <div class="col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header">
                        <h5 class="mb-0">Popular Hashtags</h5>
                      </div>
                      <div class="card-body">
                        <div v-if="popularHashtags.length" class="hashtag-grid">
                          <div 
                            v-for="tag in popularHashtags" 
                            :key="tag.name"
                            class="hashtag-item"
                            @click="toggleHashtagSelection(tag)"
                            :class="{ 'selected': selectedHashtags.includes(tag.name) }"
                          >
                            <span class="hashtag-name">{{ tag.name }}</span>
                            <span class="hashtag-count">{{ formatCount(tag.count) }}</span>
                          </div>
                        </div>
                        <div v-else class="text-center text-muted py-4">
                          <i class="fas fa-hashtag fa-2x mb-2"></i>
                          <p>Search for a topic to see popular hashtags</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Related Hashtags -->
                  <div class="col-md-6 mb-4">
                    <div class="card h-100">
                      <div class="card-header">
                        <h5 class="mb-0">Related Hashtags</h5>
                      </div>
                      <div class="card-body">
                        <div v-if="relatedHashtags.length" class="hashtag-grid">
                          <div 
                            v-for="tag in relatedHashtags" 
                            :key="tag.name"
                            class="hashtag-item"
                            @click="toggleHashtagSelection(tag)"
                            :class="{ 'selected': selectedHashtags.includes(tag.name) }"
                          >
                            <span class="hashtag-name">{{ tag.name }}</span>
                            <span class="hashtag-count">{{ formatCount(tag.count) }}</span>
                          </div>
                        </div>
                        <div v-else class="text-center text-muted py-4">
                          <i class="fas fa-tags fa-2x mb-2"></i>
                          <p>Related hashtags will appear here</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Selected Hashtags -->
              <div class="col-12 mb-4">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Selected Hashtags</h5>
                    <div>
                      <button 
                        class="btn btn-sm btn-outline-danger me-2" 
                        @click="clearSelection"
                        :disabled="!selectedHashtags.length"
                      >
                        <i class="fas fa-trash-alt me-1"></i>
                        Clear All
                      </button>
                      <button 
                        class="btn btn-sm btn-primary" 
                        @click="copyHashtags"
                        :disabled="!selectedHashtags.length"
                      >
                        <i class="fas fa-copy me-1"></i>
                        Copy
                      </button>
                    </div>
                  </div>
                  <div class="card-body">
                    <div v-if="selectedHashtags.length" class="selected-hashtags">
                      <span 
                        v-for="tag in selectedHashtags" 
                        :key="tag"
                        class="badge bg-light text-dark me-2 mb-2 p-2"
                      >
                        {{ tag }}
                        <i 
                          class="fas fa-times-circle ms-1" 
                          @click.stop="removeHashtag(tag)"
                        ></i>
                      </span>
                    </div>
                    <div v-else class="text-center text-muted py-4">
                      <i class="fas fa-info-circle fa-2x mb-2"></i>
                      <p>Click on hashtags above to select them</p>
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { searchHashtags as searchHashtagsAPI, analyzeContent } from '../services/HashtagService';
import DashboardSidebar from './DashboardSidebar.vue';
import Loader from './Loader.vue';
import { useDynamicResources } from '../composables/useDynamicResources';

// State
const isLoading = ref(true);
const searchQuery = ref('');
const isSearching = ref(false);
const popularHashtags = ref([]);
const relatedHashtags = ref([]);
const selectedHashtags = ref([]);
const copySuccess = ref(false);

// Methods
const searchHashtags = async () => {
  if (!searchQuery.value || isSearching.value) return;
  
  isSearching.value = true;
  try {
    // First analyze the content if it's a phrase
    if (searchQuery.value.includes(' ')) {
      const analysisResult = await analyzeContent(searchQuery.value);
      popularHashtags.value = analysisResult.hashtags.map(tag => ({
        name: `#${tag.replace(/^#/, '')}`, // Ensure single # and add if missing
        count: 0
      }));
    } else {
      // If it's a single hashtag, search trending
      const result = await searchHashtagsAPI(
        searchQuery.value.replace(/^#/, '') // Remove # if present
      );
      
      console.log('API response:', result);
      
      if (result && result.trending_hashtags) {
        // Process the hashtags
        const allHashtags = result.trending_hashtags.map(tag => ({
          name: `#${tag.hashtag.replace(/^#/, '')}`, // Ensure single # and add if missing
          count: tag.count
        }));
        
        // Sort by count
        allHashtags.sort((a, b) => b.count - a.count);
        
        // Split between popular and related
        if (allHashtags.length > 0) {
          popularHashtags.value = allHashtags.slice(0, 5);
          relatedHashtags.value = allHashtags.length > 5 ? allHashtags.slice(5) : [];
        } else {
          popularHashtags.value = [];
          relatedHashtags.value = [];
        }
      } else {
        throw new Error('No hashtags found or invalid response format');
      }
    }
  } catch (error) {
    console.error('Error searching hashtags:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: error.message || 'Failed to fetch hashtags'
      }, {
        type: 'danger'
      });
    }
  } finally {
    isSearching.value = false;
  }
};

const toggleHashtagSelection = (tag) => {
  const tagName = tag.name || tag;
  
  if (selectedHashtags.value.includes(tagName)) {
    selectedHashtags.value = selectedHashtags.value.filter(t => t !== tagName);
  } else {
    selectedHashtags.value.push(tagName);
  }
};

const removeHashtag = (tag) => {
  selectedHashtags.value = selectedHashtags.value.filter(t => t !== tag);
};

const clearSelection = () => {
  selectedHashtags.value = [];
};

const copyHashtags = async () => {
  const hashtags = selectedHashtags.value.join(' '); // No need to add # as it's already in the tags
  try {
    await navigator.clipboard.writeText(hashtags);
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Hashtags copied to clipboard!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    console.error('Failed to copy hashtags:', error);
  }
};

const formatCount = (count) => {
  if (count >= 1000000) {
    return `${(count / 1000000).toFixed(1)}M`;
  } else if (count >= 1000) {
    return `${(count / 1000).toFixed(1)}K`;
  }
  return count;
};

// Initialize
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

const { removeDynamicCss, initializeCss, removeDynamicJs, initializeScripts } = useDynamicResources(isLoading, cssFiles, JsFiles);

onMounted(async () => {
  await removeDynamicCss();
  await removeDynamicJs();
  await initializeCss();
  await initializeScripts();
});
</script>

<style scoped>
.hashtag-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 1rem;
}

.hashtag-item {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 0.75rem;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.hashtag-item:hover {
  background: #ffffff;
  border-color: #74c0fc;
  transform: translateY(-2px);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.hashtag-item.selected {
  background: #e7f5ff;
  border-color: #74c0fc;
}

.hashtag-name {
  font-weight: 500;
  margin-bottom: 0.25rem;
  color: #000000;
  font-size: 0.95rem;
}

.hashtag-count {
  font-size: 0.875rem;
  color: #6c757d;
}

.selected-hashtags .badge {
  font-size: 0.95rem;
  background-color: #f8f9fa !important;
  color: #000000 !important;
  border: 1px solid #dee2e6;
  padding: 0.5rem 0.75rem !important;
  border-radius: 6px;
}

.selected-hashtags .badge i {
  cursor: pointer;
  opacity: 0.7;
  transition: opacity 0.2s ease;
  color: #dc3545;
}

.selected-hashtags .badge i:hover {
  opacity: 1;
}
</style> 