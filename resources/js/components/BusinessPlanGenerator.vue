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
              <h4 class="f-w-600">Business Plan Generator</h4>
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
                  <h4>Generate Professional Business Plan</h4>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <!-- Generator Form -->
              <div v-if="!businessPlan" class="col-12">
                <div class="card">
                  <div class="card-header">
                    <h5>Business Information</h5>
                  </div>
                  <div class="card-body">
                    <form @submit.prevent="generatePlan" class="needs-validation">
                      <div class="row">
                        <!-- Business Type -->
                        <div class="col-md-6 mb-4">
                          <label for="businessType" class="form-label">Business Type/Industry <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            id="businessType" 
                            class="form-control" 
                            v-model="formData.business_type" 
                            placeholder="e.g., Artisanal Bakery, Tech Startup, Marketing Agency"
                            required
                          >
                          <div class="form-text">Describe your business type or industry sector</div>
                        </div>
                        
                        <!-- Target Demographics -->
                        <div class="col-md-6 mb-4">
                          <label for="targetDemo" class="form-label">Target Demographics <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            id="targetDemo" 
                            class="form-control" 
                            v-model="formData.target_demographics" 
                            placeholder="e.g., Urban professionals aged 25-40"
                            required
                          >
                          <div class="form-text">Describe your target audience's demographics</div>
                        </div>
                        
                        <!-- Platform -->
                        <div class="col-md-6 mb-4">
                          <label for="platform" class="form-label">Platform <span class="text-danger">*</span></label>
                          <select 
                            id="platform" 
                            class="form-select" 
                            v-model="formData.platform"
                            required
                          >
                            <option value="" disabled>Select a platform</option>
                            <option value="Instagram">Instagram</option>
                            <option value="Facebook">Facebook</option>
                            <option value="LinkedIn">LinkedIn</option>
                            <option value="Twitter">Twitter</option>
                            <option value="TikTok">TikTok</option>
                            <option value="YouTube">YouTube</option>
                            <option value="Multiple Platforms">Multiple Platforms</option>
                          </select>
                          <div class="form-text">Primary social media platform for your business</div>
                        </div>
                        
                        <!-- Business Goals -->
                        <div class="col-md-6 mb-4">
                          <label for="businessGoals" class="form-label">Business Goals <span class="text-danger">*</span></label>
                          <input 
                            type="text" 
                            id="businessGoals" 
                            class="form-control" 
                            v-model="formData.business_goals" 
                            placeholder="e.g., Increase online orders by 30%"
                            required
                          >
                          <div class="form-text">What you want to achieve with your business</div>
                        </div>
                        
                        <!-- Content Preferences -->
                        <div class="col-md-6 mb-4">
                          <label for="contentPrefs" class="form-label">Content Preferences</label>
                          <input 
                            type="text" 
                            id="contentPrefs" 
                            class="form-control" 
                            v-model="formData.content_preferences" 
                            placeholder="e.g., Short videos, product photos"
                          >
                          <div class="form-text">Optional: Your preferred content types</div>
                        </div>
                        
                        <!-- Budget -->
                        <div class="col-md-6 mb-4">
                          <label for="budget" class="form-label">Budget</label>
                          <input 
                            type="text" 
                            id="budget" 
                            class="form-control" 
                            v-model="formData.budget" 
                            placeholder="e.g., $500 per month"
                          >
                          <div class="form-text">Optional: Your marketing budget</div>
                        </div>
                        
                        <!-- Timeframe -->
                        <div class="col-md-6 mb-4">
                          <label for="timeframe" class="form-label">Timeframe</label>
                          <input 
                            type="text" 
                            id="timeframe" 
                            class="form-control" 
                            v-model="formData.timeframe" 
                            placeholder="e.g., 6 months, 1 year"
                          >
                          <div class="form-text">Optional: Expected implementation timeline</div>
                        </div>
                        
                        <!-- Current Challenges -->
                        <div class="col-md-6 mb-4">
                          <label for="challenges" class="form-label">Current Challenges</label>
                          <input 
                            type="text" 
                            id="challenges" 
                            class="form-control" 
                            v-model="formData.current_challenges" 
                            placeholder="e.g., Low engagement, inconsistent posting"
                          >
                          <div class="form-text">Optional: Challenges you're currently facing</div>
                        </div>
                      </div>
                      
                      <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-light" @click="resetForm">
                          <i class="fas fa-redo me-2"></i>Reset Form
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="isGenerating">
                          <i class="fas fa-magic me-2"></i>
                          <span v-if="isGenerating">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Generating (This may take up to 2 minutes)...
                          </span>
                          <span v-else>Generate Business Plan</span>
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              
              <!-- Generated Plan Display -->
              <div v-if="businessPlan" class="col-12">
                <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Your Business Plan</h5>
                    <div>
                      <button class="btn btn-outline-primary me-2" @click="copyToClipboard">
                        <i class="fas fa-copy me-2"></i>Copy
                      </button>
                      <button class="btn btn-outline-secondary me-2" @click="printBusinessPlan">
                        <i class="fas fa-print me-2"></i>Print
                      </button>
                      <button class="btn btn-outline-danger" @click="businessPlan = null">
                        <i class="fas fa-times me-2"></i>Close
                      </button>
                    </div>
                  </div>
                  <div class="card-body p-0">
                    <ul class="nav nav-tabs" id="planTabs" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" id="full-tab" data-bs-toggle="tab" href="#full" role="tab">
                          Complete Plan
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="sections-tab" data-bs-toggle="tab" href="#sections" role="tab">
                          View By Sections
                        </a>
                      </li>
                    </ul>
                    
                    <div class="tab-content p-3" id="planTabContent">
                      <!-- Full view -->
                      <div class="tab-pane fade show active" id="full" role="tabpanel">
                        <div class="business-plan-content">
                          <div v-html="formattedBusinessPlan" class="business-plan-text"></div>
                        </div>
                      </div>
                      
                      <!-- Sectioned view -->
                      <div class="tab-pane fade" id="sections" role="tabpanel">
                        <div class="accordion" id="planSections">
                          <div v-for="(section, index) in businessPlanSections" :key="index" class="accordion-item">
                            <h2 class="accordion-header">
                              <button class="accordion-button" type="button" data-bs-toggle="collapse" :data-bs-target="'#section-' + index">
                                {{ section.title }}
                              </button>
                            </h2>
                            <div :id="'section-' + index" class="accordion-collapse collapse" data-bs-parent="#planSections">
                              <div class="accordion-body">
                                <div v-html="section.content" class="business-plan-text"></div>
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
import { ref, onMounted, computed, nextTick } from 'vue';
import DashboardSidebar from './DashboardSidebar.vue';
import { generateBusinessPlan } from '../services/BusinessPlanService';
import { useDynamicResources } from '../composables/useDynamicResources';

// State
const isLoading = ref(true);
const isGenerating = ref(false);
const businessPlan = ref(null);
const formData = ref({
  business_type: '',
  target_demographics: '',
  platform: '',
  business_goals: '',
  content_preferences: '',
  budget: '',
  timeframe: '',
  current_challenges: ''
});

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

// Format business plan for display
const formattedBusinessPlan = computed(() => {
  if (!businessPlan.value) return '';
  
  // First, remove LaTeX-style boxes and markdown code blocks
  let formatted = businessPlan.value
    .replace(/\\boxed\{\s*```markdown/g, '')
    .replace(/```\s*\}/g, '')
    .replace(/```markdown/g, '')
    .replace(/```/g, '');
  
  // Replace headers with styled headers
  formatted = formatted
    .replace(/####\s*(.*)/g, '<h4 class="business-plan-h4">$1</h4>')
    .replace(/###\s*(.*)/g, '<h3 class="business-plan-h3">$1</h3>')
    .replace(/##\s*(.*)/g, '<h2 class="business-plan-h2">$1</h2>')
    .replace(/#\s*(.*)/g, '<h1 class="business-plan-h1">$1</h1>')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.*?)\*/g, '<em>$1</em>');
  
  // Convert bullet points to proper HTML
  formatted = formatted.replace(/- (.*)/g, '<li>$1</li>');
  formatted = formatted.replace(/(?:<li>.*?<\/li>)+/g, function(match) {
    return '<ul class="business-plan-list">' + match + '</ul>';
  });
  
  // Convert numbered lists
  formatted = formatted.replace(/(\d+)\.\s+(.*)/g, '<li class="numbered-list-item">$2</li>');
  formatted = formatted.replace(/(?:<li class="numbered-list-item">.*?<\/li>)+/g, function(match) {
    return '<ol class="business-plan-numbered-list">' + match + '</ol>';
  });
  
  // Convert line breaks to paragraphs
  formatted = formatted.split('\n\n').map(para => {
    // Skip if it's already an HTML element
    if (para.trim().startsWith('<')) return para;
    // Skip if it's empty
    if (!para.trim()) return '';
    return `<p>${para}</p>`;
  }).join('');
  
  // Clean up any remaining newlines inside paragraphs
  formatted = formatted.replace(/<p>(.*?)<\/p>/gs, function(match, content) {
    return '<p>' + content.replace(/\n/g, '<br>') + '</p>';
  });
  
  return formatted;
});

// Add this computed property to the script section
const businessPlanSections = computed(() => {
  if (!businessPlan.value) return [];
  
  const sections = [];
  // First clean up the markdown
  let cleanText = businessPlan.value
    .replace(/\\boxed\{\s*```markdown/g, '')
    .replace(/```\s*\}/g, '')
    .replace(/```markdown/g, '')
    .replace(/```/g, '');
    
  const lines = cleanText.split('\n');
  
  let currentSection = { title: 'Introduction', content: '' };
  
  for (const line of lines) {
    if (line.startsWith('##') && !line.startsWith('###')) {
      // If we already have content in the current section, push it
      if (currentSection.content) {
        sections.push({...currentSection});
      }
      
      // Start a new section
      currentSection = {
        title: line.replace(/##\s+/, ''),
        content: `<h2 class="business-plan-h2">${line.replace(/##\s+/, '')}</h2>`
      };
    } else if (line.startsWith('###')) {
      // Add subheader to current section
      currentSection.content += `<h3 class="business-plan-h3">${line.replace(/###\s+/, '')}</h3>`;
    } else if (line.startsWith('####')) {
      // Add sub-subheader to current section
      currentSection.content += `<h4 class="business-plan-h4">${line.replace(/####\s+/, '')}</h4>`;
    } else if (line.trim().startsWith('-')) {
      // Add list item
      currentSection.content += `<li>${line.substring(line.indexOf('-') + 1).trim()}</li>`;
    } else if (line.trim().match(/^\d+\.\s+/)) {
      // Add numbered list item
      currentSection.content += `<li class="numbered-list-item">${line.replace(/^\d+\.\s+/, '')}</li>`;
    } else if (line.trim()) {
      // Add regular paragraph
      currentSection.content += `<p>${line}</p>`;
    } else if (line.trim() === '') {
      // Add spacing only between content
      if (currentSection.content && !currentSection.content.endsWith('<br>')) {
        currentSection.content += '<br>';
      }
    }
  }
  
  // Add the last section
  if (currentSection.content) {
    sections.push(currentSection);
  }
  
  return sections;
});

// Methods
const generatePlan = async () => {
  if (isGenerating.value) return;
  
  // Validate required fields
  const requiredFields = ['business_type', 'target_demographics', 'platform', 'business_goals'];
  for (const field of requiredFields) {
    if (!formData.value[field]) {
      if (window.$) {
        window.$.notify({
          title: 'Error',
          message: `Please fill in all required fields`
        }, {
          type: 'danger'
        });
      }
      return;
    }
  }
  
  isGenerating.value = true;
  
  // Set a timeout to show a persistent notification after 15 seconds
  const timeoutNotification = setTimeout(() => {
    if (window.$) {
      window.$.notify({
        title: 'Still Working',
        message: 'Your business plan is being generated. This process can take up to 2 minutes.'
      }, {
        type: 'info',
        delay: 0 // Don't auto-close
      });
    }
  }, 15000);
  
  try {
    const response = await generateBusinessPlan(formData.value);
    console.log('Business plan response:', response); // Add this debug line
    businessPlan.value = response.businessPlan;
    
    // Add immediate check
    console.log('Business plan value:', businessPlan.value); // Add this debug line
    
    // Force a re-render
    nextTick(() => {
      console.log('Formatted plan:', formattedBusinessPlan.value); // Add this debug line
    });
    
    // Show success notification
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Business plan generated successfully!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    clearTimeout(timeoutNotification); // Clear the timeout notification
    console.error('Generation error:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: error.message || 'Failed to generate business plan'
      }, {
        type: 'danger'
      });
    }
  } finally {
    isGenerating.value = false;
  }
};

const resetForm = () => {
  formData.value = {
    business_type: '',
    target_demographics: '',
    platform: '',
    business_goals: '',
    content_preferences: '',
    budget: '',
    timeframe: '',
    current_challenges: ''
  };
};

const copyToClipboard = async () => {
  try {
    await navigator.clipboard.writeText(businessPlan.value);
    if (window.$) {
      window.$.notify({
        title: 'Success',
        message: 'Business plan copied to clipboard!'
      }, {
        type: 'success'
      });
    }
  } catch (error) {
    console.error('Copy error:', error);
    if (window.$) {
      window.$.notify({
        title: 'Error',
        message: 'Failed to copy to clipboard'
      }, {
        type: 'danger'
      });
    }
  }
};

const printBusinessPlan = () => {
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <html>
      <head>
        <title>Business Plan</title>
        <style>
          body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
          h1 { color: #006666; border-bottom: 2px solid #006666; padding-bottom: 10px; margin-top: 30px; }
          h2 { color: #008080; margin-top: 25px; border-bottom: 1px solid #e0e0e0; padding-bottom: 5px; }
          h3 { color: #00a3a3; margin-top: 20px; }
          h4 { color: #4a4a4a; margin-top: 15px; font-weight: 600; }
          ul, ol { margin-left: 20px; margin-bottom: 20px; }
          li { margin-bottom: 8px; }
          p { margin-bottom: 15px; }
          .header { text-align: center; margin-bottom: 30px; }
          .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #666; }
          
          /* Add some special styling for print */
          @media print {
            body { font-size: 12pt; }
            h1 { font-size: 18pt; }
            h2 { font-size: 16pt; }
            h3 { font-size: 14pt; }
            h4 { font-size: 13pt; }
            p, li { font-size: 12pt; }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>Business Plan</h1>
          <p>Generated by SocialMind Business Plan Generator</p>
        </div>
        ${formattedBusinessPlan.value}
        <div class="footer">
          <p>Generated on ${new Date().toLocaleDateString()} | SocialMind © 2024</p>
        </div>
      </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => {
    printWindow.print();
  }, 500); // Short delay to ensure everything loads properly
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
.business-plan-content {
  padding: 2rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.business-plan-text {
  font-size: 1rem;
  line-height: 1.8;
  word-wrap: break-word;
  color: #333;
}

.business-plan-text :deep(.business-plan-h1) {
  color: #006666;
  font-size: 1.8rem;
  margin-bottom: 1.5rem;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 0.5rem;
}

.business-plan-text :deep(.business-plan-h2) {
  color: #008080;
  font-size: 1.5rem;
  margin-top: 1.5rem;
  margin-bottom: 1rem;
  border-bottom: 1px solid #e0e0e0;
  padding-bottom: 0.3rem;
}

.business-plan-text :deep(.business-plan-h3) {
  color: #00a3a3;
  font-size: 1.2rem;
  margin-top: 1.2rem;
  margin-bottom: 0.8rem;
}

.business-plan-text :deep(.business-plan-h4) {
  color: #4a4a4a;
  font-size: 1.1rem;
  margin-top: 1rem;
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.business-plan-text :deep(.business-plan-list) {
  margin-left: 1.5rem;
  margin-bottom: 1.5rem;
  list-style-type: disc;
}

.business-plan-text :deep(.business-plan-numbered-list) {
  margin-left: 1.5rem;
  margin-bottom: 1.5rem;
  list-style-type: decimal;
}

.business-plan-text :deep(p) {
  margin-bottom: 1rem;
  color: #333;
}

/* Add this for proper spacing between items */
.business-plan-text :deep(li) {
  margin-bottom: 0.5rem;
}

/* Style any separators in the content */
.business-plan-text :deep(hr) {
  border: none;
  border-top: 1px solid #e0e0e0;
  margin: 2rem 0;
}

/* Add extra styles for the print version */
@media print {
  .business-plan-content {
    padding: 0;
    background: none;
    box-shadow: none;
  }
  
  .business-plan-text {
    font-size: 12pt;
  }
  
  .business-plan-text :deep(.business-plan-h1) {
    font-size: 18pt;
  }
  
  .business-plan-text :deep(.business-plan-h2) {
    font-size: 16pt;
  }
  
  .business-plan-text :deep(.business-plan-h3) {
    font-size: 14pt;
  }
  
  .business-plan-text :deep(.business-plan-h4) {
    font-size: 13pt;
  }
}

.form-label {
  font-weight: 500;
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

/* Fix sidebar layout */
.page-header, .page-body, .footer {
  margin-left: 230px !important;
  width: calc(100% - 230px) !important;
}

.card {
  border-radius: 12px;
  border: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  margin-bottom: 2rem;
}

.card-header {
  background: linear-gradient(145deg, #f8f9fa, #ffffff);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 1.25rem 1.5rem;
}

.card-body {
  padding: 1.5rem;
}
</style> 