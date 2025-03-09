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
                  <h4>AI Description Generator</h4>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <!-- Input Section -->
              <div class="col-xl-8 col-lg-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Generate Description</h5>
                  </div>
                  <div class="card-body">
                    <div class="mb-4">
                      <label class="form-label">What would you like to write about?</label>
                      <textarea 
                        class="form-control" 
                        rows="4"
                        v-model="userInput"
                        placeholder="Enter your topic or idea here..."
                        :disabled="isGenerating"
                      ></textarea>
                    </div>
                    
                    <!-- Example Topics -->
                    <div class="mb-4">
                      <label class="form-label">Example Topics:</label>
                      <div class="example-topics">
                        <button 
                          v-for="topic in exampleTopics" 
                          :key="topic"
                          class="btn btn-outline-primary btn-sm me-2 mb-2"
                          @click="useExampleTopic(topic)"
                          :disabled="isGenerating"
                        >
                          {{ topic }}
                        </button>
                      </div>
                    </div>

                    <!-- Generate Button -->
                    <button 
                      class="btn btn-primary w-100" 
                      @click="generateDescription"
                      :disabled="!userInput || isGenerating"
                    >
                      <i class="fas fa-magic me-2"></i>
                      {{ isGenerating ? 'Generating...' : 'Generate Description' }}
                    </button>
                  </div>
                </div>

                <!-- Generated Content -->
                <div v-if="generatedContent" class="card mt-4">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Generated Description</h5>
                    <button 
                      class="btn btn-primary btn-sm"
                      @click="copyToClipboard"
                    >
                      <i class="fas fa-copy me-1"></i>
                      Copy
                    </button>
                  </div>
                  <div class="card-body">
                    <div class="generated-content">
                      <div class="content-section mb-4">
                        <textarea 
                          class="form-control"
                          v-model="generatedContent.optimized_content"
                          rows="6"
                        ></textarea>
                      </div>
                      
                      <!-- Hashtags -->
                      <div class="hashtags-section mb-4">
                        <label class="form-label fw-bold mb-3">Suggested Hashtags</label>
                        <div class="hashtag-container">
                          <span 
                            v-for="hashtag in generatedContent.analysis.suggested_hashtags" 
                            :key="hashtag"
                            class="hashtag-pill"
                          >
                            #{{ hashtag }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Analysis Section -->
              <div class="col-xl-4 col-lg-12">
                <div v-if="generatedContent" class="card">
                  <div class="card-header">
                    <h5>Content Analysis</h5>
                  </div>
                  <div class="card-body">
                    <!-- Purpose -->
                    <div class="analysis-item mb-4">
                      <h6 class="text-primary mb-2">Content Purpose</h6>
                      <p>{{ generatedContent.analysis.purpose }}</p>
                    </div>

                    <!-- Common Phrases -->
                    <div class="analysis-item mb-4">
                      <h6 class="text-primary mb-2">Common Phrases</h6>
                      <ul class="list-unstyled">
                        <li v-for="(phrase, index) in generatedContent.analysis.common_phrases" 
                            :key="index"
                            class="mb-2"
                        >
                          <i class="fas fa-check-circle text-success me-2"></i>
                          {{ phrase }}
                        </li>
                      </ul>
                    </div>

                    <!-- Emoji Usage -->
                    <div class="analysis-item mb-4">
                      <h6 class="text-primary mb-2">Recommended Emojis</h6>
                      <div class="emoji-list">
                        <span 
                          v-for="(emoji, index) in generatedContent.analysis.emoji_usage" 
                          :key="index"
                          class="me-2"
                        >
                          {{ emoji }}
                        </span>
                      </div>
                    </div>

                    <!-- Structure Patterns -->
                    <div class="analysis-item">
                      <h6 class="text-primary mb-2">Structure Patterns</h6>
                      <ul class="list-unstyled">
                        <li v-for="(pattern, index) in generatedContent.analysis.structure_patterns" 
                            :key="index"
                            class="mb-2"
                        >
                          <i class="fas fa-list text-primary me-2"></i>
                          {{ pattern }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <!-- Tips Card -->
                <div class="card mt-4">
                  <div class="card-header">
                    <h5>Writing Tips</h5>
                  </div>
                  <div class="card-body">
                    <ul class="list-unstyled">
                      <li v-for="(tip, index) in writingTips" 
                          :key="index"
                          class="mb-3"
                      >
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        {{ tip }}
                      </li>
                    </ul>
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
import Loader from './Loader.vue';
import DashboardSidebar from './DashboardSidebar.vue';
import { useDynamicResources } from '../composables/useDynamicResources';
import { generateOptimizedContent } from '../services/DescriptionService';

// State
const isLoading = ref(true);
const isGenerating = ref(false);
const userInput = ref('');
const generatedContent = ref(null);

// Example topics
const exampleTopics = [
  'Product Launch Announcement',
  'Behind the Scenes',
  'Customer Success Story',
  'Industry Tips & Tricks',
  'Company Culture'
];

// Writing tips
const writingTips = [
  'Start with a hook to grab attention',
  'Use emojis strategically to increase engagement',
  'Include a clear call-to-action',
  'Keep paragraphs short and scannable',
  'Use relevant hashtags but don\'t overdo it'
];

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

// Methods
const useExampleTopic = (topic) => {
  userInput.value = topic;
};

const generateDescription = async () => {
  if (!userInput.value || isGenerating.value) return;
  
  isGenerating.value = true;
  try {
    const result = await generateOptimizedContent(userInput.value);
    generatedContent.value = result;
    
    // Show success notification
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Description generated successfully!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    console.error('Generation error:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: error.message || 'Failed to generate description'
      }, {
        type: 'danger'
      });
    }
  } finally {
    isGenerating.value = false;
  }
};

const copyToClipboard = async () => {
  if (!generatedContent.value?.optimized_content) return;
  
  try {
    await navigator.clipboard.writeText(generatedContent.value.optimized_content);
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Content copied to clipboard!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    console.error('Copy error:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: 'Failed to copy content'
      }, {
        type: 'danger'
      });
    }
  }
};

const { removeDynamicCss, initializeCss, removeDynamicJs, initializeScripts } = useDynamicResources(isLoading, cssFiles, JsFiles);

// Lifecycle hooks
onMounted(async () => {
  await removeDynamicCss();
  await removeDynamicJs();
  await initializeCss();
  await initializeScripts();
});
</script>

<style scoped>
.example-topics {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.hashtag-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.badge {
  font-size: 0.9rem;
  padding: 0.5rem 1rem;
}

.generated-content {
  background-color: #21272c;
  border-radius: 8px;
  padding: 1rem;
}

.content-section textarea {
  font-size: 1rem;
  line-height: 1.6;
  resize: vertical;
}

.analysis-item {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.analysis-item:last-child {
  border-bottom: none;
}

.emoji-list {
  font-size: 1.2rem;
  letter-spacing: 0.5rem;
}

.card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  overflow: hidden;
}

.card:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.btn-primary {
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  border: none;
  font-weight: 600;
  padding: 0.75rem 1.5rem;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 102, 102, 0.2);
}

.btn-outline-primary {
  border-color: #006666;
  color: #006666;
}

.btn-outline-primary:hover:not(:disabled) {
  background-color: #006666;
  color: white;
}

.hashtags-section {
  background: linear-gradient(145deg, #f8f9fa, #1c1c29);
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.hashtag-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.hashtag-pill {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: linear-gradient(135deg, #006666 0%, #00a3a3 100%);
  color: white;
  border-radius: 25px;
  font-size: 0.9rem;
  font-weight: 500;
  transition: all 0.3s ease;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 102, 102, 0.2);
}

.hashtag-pill:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 102, 102, 0.3);
  background: linear-gradient(135deg, #005555 0%, #008f8f 100%);
}

.form-control {
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #006666;
  box-shadow: 0 0 0 3px rgba(0, 102, 102, 0.1);
}

.generated-content {
  animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style> 