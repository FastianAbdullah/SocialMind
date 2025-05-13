<!-- AI Assistant Component -->
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
                  <div class="title-wrapper">
                    <h4 class="animated-title">AI Assistant</h4>
                    <div class="title-underline"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <div class="col-12">
                <div class="card ai-card">
                  <div class="card-body p-0">
                    <!-- AI Assistant Interface -->
                    <div class="ai-chat-container">
                      <!-- Chat Header -->
                      <div class="chat-header" v-if="messages.length === 1 && messages[0].role === 'assistant'">
                        <div class="avatar-container text-center mb-4">
                          <div class="assistant-avatar mx-auto pulse-animation">
                            <i class="fas fa-robot"></i>
                          </div>
                        </div>
                        <h5 class="text-center mb-2 fade-in">Hello, {{ userName }}</h5>
                        <p class="text-center text-muted mb-4 slide-up">
                          I can help with content creation, scheduling, hashtags, and more.
                        </p>
                      </div>

                      <!-- Messages Area -->
                      <div class="chat-messages" ref="chatContainer">
                        <div class="messages-container">
                          <div v-for="(message, index) in messages" :key="index" 
                            class="message-wrapper"
                            v-show="!(index === 0 && message.role === 'assistant' && messages.length === 1)"
                            :class="[message.role === 'assistant' ? 'message-in' : 'message-out']">
                            <div :class="['message', message.role === 'assistant' ? 'assistant' : 'user']">
                              <div class="message-avatar">
                                <i :class="message.role === 'assistant' ? 'fas fa-robot' : 'fas fa-user'"></i>
                              </div>
                              <div class="message-content">
                                <div class="message-text" v-html="formatMessage(message.content)"></div>
                                
                                <!-- Action buttons -->
                                <div v-if="message.role === 'assistant' && message.actions && message.actions.length > 0" 
                                  class="message-actions">
                                  <button 
                                    v-for="(action, actionIndex) in message.actions" 
                                    :key="actionIndex"
                                    @click="sendDirectPrompt(action)"
                                    class="action-button"
                                  >
                                    {{ action }}
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                          
                          <!-- Loading Animation -->
                          <div v-if="chatLoading" class="typing-indicator">
                            <span></span>
                            <span></span>
                            <span></span>
                          </div>
                        </div>
                      </div>

                      <!-- Quick Suggestions -->
                      <div v-if="messages.length === 1 && messages[0].role === 'assistant'" class="quick-suggestions">
                        <h6 class="text-center mb-3">How can I help you with your social media today?</h6>
                        <div class="row">
                          <div class="col-md-3 col-sm-6 mb-3" v-for="(suggestion, index) in quickSuggestions" :key="index">
                            <div class="suggestion-card" 
                                 @click="sendDirectPrompt(suggestion.prompt)"
                                 :style="{ animationDelay: `${index * 0.1}s` }">
                              <div class="suggestion-icon">
                                <i :class="suggestion.icon"></i>
                              </div>
                              <span>{{ suggestion.title }}</span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Input Area -->
                      <div class="chat-input">
                        <div v-if="showImageUpload" class="image-upload-container">
                          <div class="image-upload-wrapper">
                            <div class="image-preview-section" :class="{ 'active': selectedImage }">
                              <div v-if="selectedImage" class="image-thumbnail">
                                <img :src="imagePreview" alt="Selected image" />
                                <button class="remove-image-btn" @click="removeImage">
                                  <i class="fas fa-times"></i>
                                </button>
                              </div>
                              <div v-if="imageError" class="image-error-message">
                                {{ imageError }}
                              </div>
                            </div>
                          </div>
                        </div>
                        <form @submit.prevent="sendMessage" class="input-form">
                          <!-- Image Upload Button -->
                          <div class="upload-media-btn" @click="triggerImageUpload">
                            <i class="fas fa-image"></i>
                            <div class="upload-tooltip">Click to upload media</div>
                            <input 
                              type="file" 
                              ref="imageInput"
                              @change="handleImageSelect"
                              accept="image/*"
                              class="d-none"
                            />
                          </div>
                          
                          <!-- Message Input Field -->
                          <textarea 
                            v-model="userInput" 
                            @keydown.enter.exact.prevent="sendMessage"
                            placeholder="Type your message here..."
                            class="form-control modern-input"
                            rows="1"
                            ref="inputField"
                            @input="autoResize"
                          ></textarea>
                          
                          <!-- Send Button -->
                          <button 
                            type="submit"
                            class="btn btn-primary send-btn"
                            :disabled="!userInput.trim() || chatLoading"
                            :class="{ 'btn-pulse': userInput.trim() && !chatLoading }"
                          >
                            <i class="fas fa-paper-plane"></i>
                          </button>
                        </form>
                        <!-- Input Features -->
                        <div class="input-features">
                          <div class="feature-hint" @click="toggleEmojiPicker">
                            <i class="far fa-smile"></i>
                          </div>
                          <div class="feature-hint" @click="sendDirectPrompt('Suggest trending hashtags')">
                            <i class="fas fa-hashtag"></i>
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
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import axios from 'axios';
import { marked } from 'marked';
import createDOMPurify from 'dompurify';
import DashboardSidebar from './DashboardSidebar.vue';
import Loader from './Loader.vue';
import { useDynamicResources } from '../composables/useDynamicResources';

// Sanitizer setup
const DOMPurify = createDOMPurify(window);

// State
const isLoading = ref(true);
const chatLoading = ref(false);
const userInput = ref('');
const messages = ref([]);
const userName = ref('User');
const chatContainer = ref(null);
const inputField = ref(null);
const showEmojiPicker = ref(false);
const selectedImage = ref(null);
const imagePreview = ref('');
const imageError = ref('');
const imageInput = ref(null);
const showImageUpload = ref(false);

// Quick suggestions data
const quickSuggestions = [
  {
    title: "Create Content",
    icon: "fas fa-pen-fancy",
    prompt: "Create social media content for my business"
  },
  {
    title: "Posting Times",
    icon: "fas fa-clock",
    prompt: "What are the best times to post on Instagram and Facebook?"
  },
  {
    title: "Hashtags",
    icon: "fas fa-hashtag",
    prompt: "Find trending hashtags for my fitness business"
  },
  {
    title: "Analytics",
    icon: "fas fa-chart-line",
    prompt: "Analyze my social media performance"
  }
];

// Load resources
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
];

const { removeDynamicCss, initializeCss, removeDynamicJs, initializeScripts } = useDynamicResources(isLoading, cssFiles, JsFiles);

// Methods
const scrollToBottom = () => {
  if (chatContainer.value) {
    // Use nextTick to ensure DOM is updated
    nextTick(() => {
      const container = chatContainer.value;
      
      // Explicitly calculate the maximum scroll position
      const maxScroll = container.scrollHeight - container.clientHeight;
      
      // Force scroll to the absolute bottom with a slight delay to ensure rendering is complete
      setTimeout(() => {
        container.scrollTo({
          top: maxScroll + 3000, // Add a large value to ensure we're at the bottom
          behavior: 'smooth'
        });
      }, 100);
    });
  }
};

const formatMessage = (content) => {
  // Convert markdown to HTML and sanitize
  const html = marked(content || '');
  return DOMPurify.sanitize(html);
};

const autoResize = (event) => {
  const textarea = event.target;
  textarea.style.height = 'auto';
  textarea.style.height = Math.min(120, textarea.scrollHeight) + 'px';
};

const toggleEmojiPicker = () => {
  showEmojiPicker.value = !showEmojiPicker.value;
};

const getUserInfo = async () => {
   try {
     const response = await axios.get('/user', {
       headers: { 'X-Requested-With': 'XMLHttpRequest' }
     });
     if (response.data && response.data.name) {
       userName.value = response.data.name;
     }
   } catch (error) {
     console.log('Could not retrieve user info', error);
   }
};

const addWelcomeMessage = () => {
  const welcomeMessage = {
    role: 'assistant',
    content: 'Hello! I\'m your AI Assistant for social media management. How can I help you today?',
    actions: [
      'Create new content',
      'Suggest optimal posting times',
      'Find trending hashtags',
      'Analyze performance'
    ]
  };
  messages.value.push(welcomeMessage);
};

const sendMessage = async (event) => {
  if (event) event.preventDefault();
  if (!userInput.value.trim() || chatLoading.value) return;
  
  // Add user message
  messages.value.push({
    role: 'user',
    content: userInput.value.trim()
  });
  
  const query = userInput.value.trim();
  userInput.value = '';
  
  // Auto resize the input field
  if (inputField.value) {
    inputField.value.style.height = 'auto';
  }
  
  // Scroll to show user message
  scrollToBottom();
  
  chatLoading.value = true;
  
  try {
    console.log('Sending query:', query);
    
    const response = await axios.post('/ai-assistant/process-query', 
      { query },
      {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
    );
    
    console.log('Response received:', response.data);
    
    if (response.data) {
      // Add assistant response
      messages.value.push({
        role: 'assistant',
        content: response.data.message || 'Sorry, I could not process your request.',
        actions: response.data.actions || [],
        state: response.data.state || 'idle',
        intent: response.data.intent || null
      });
      
      // Scroll to new message after it's added
      scrollToBottom();
      
      // Automatically handle certain intents without requiring button clicks
      if (response.data.intent) {
        await handleIntent(response.data);
      }
    } else {
      throw new Error('Invalid response format');
    }
  } catch (error) {
    console.error('Error in sendMessage:', error);
    
    // Show error message in the UI
    messages.value.push({
      role: 'assistant',
      content: 'I apologize, but I encountered an error. Please try again.',
      actions: ['Try again']
    });
    
    // Scroll to error message
    scrollToBottom();
    
    // Show error notification
    if (window.$ && window.$.notify) {
      window.$.notify({
        title: 'Error',
        message: 'Failed to process your message'
      }, {
        type: 'danger'
      });
    }
  } finally {
    chatLoading.value = false;
  }
};

// New function to handle intents automatically
const handleIntent = async (response) => {
  const intent = response.intent;
  console.log('Handling intent:', intent);
  
  // Wait a moment to let the user read the assistant's message
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  switch (intent) {
    case 'content_generation':
      if (response.content) {
        showImageUpload.value = true;
        scrollToBottom();
      }
      break;
      
    case 'platform_selection':
      console.log("Platform selection is required")
      if (response.platforms && response.platforms.length > 0) {
        const requiresImage = response.platforms.some(p => 
          ['facebook', 'instagram'].includes(p.toLowerCase())
        );
        // if its facebook and instagram then show image upload should be set to true so that user can upload image.
        showImageUpload.value = true;
        scrollToBottom();
        
        if (response.platforms.length === 1 || !response.message.includes('which platform')) {
          const platform = response.platforms[0];
          if (requiresImage && !selectedImage.value) {
            return;
          }
          sendDirectPrompt(`Let's post to ${platform}`);
        }
      }
      break;
      
    case 'scheduling':
      if (response.suggestions) {
        // By default, suggest the first time option
        const firstPlatform = Object.keys(response.suggestions)[0];
        if (response.suggestions[firstPlatform] && response.suggestions[firstPlatform].length > 0) {
          const suggestion = response.suggestions[firstPlatform][0];
          sendDirectPrompt(`Schedule it for ${suggestion.time}`);
        }
      }
      break;
      
    case 'confirmation':
      // Check if we have content ready to post and state is "posting"
      if (response.to_post && (response.state === 'posting' || response.state === 'confirmation')) {
        console.log('Content ready to post:', response.to_post);
        
        // Check if image is required but not provided
        const requiresImage = response.to_post.platforms.some(p => 
          ['facebook', 'instagram'].includes(p.toLowerCase())
        );
        
        if (requiresImage && !selectedImage.value) {
          messages.value.push({
            role: 'assistant',
            content: 'Please upload an image before posting to Facebook/Instagram.',
            actions: ['Upload Image'],
            state: 'waiting_for_image'
          });
          return;
        }
        
        // Actually post the content using AIAssistantService
        try {
          chatLoading.value = true;
          
          // Import AIAssistantService if not already imported
          const AIAssistantService = (await import('../services/AIAssistantService')).default;
          console.log("Response is: ",response)
          console.log("Content To post is: ",response.to_post.content)
          console.log("Platforms are: ",response.to_post.platforms)
          console.log("Selected Image is:",selectedImage.value)
          const postResult = await AIAssistantService.postContent(
            response.to_post.content,
            response.to_post.platforms,
            null, // No schedule time for immediate posting
            selectedImage.value // Pass the selected image file
          );
          
          console.log('Post result:', postResult);
          
          // Reset image upload state after successful posting
          showImageUpload.value = false;
          removeImage();
          
          // Add a confirmation message about the posting
          messages.value.push({
            role: 'assistant',
            content: postResult.message || 'Your content has been posted successfully!',
            actions: ['Create new content', 'View post status'],
            state: 'posted'
          });
          
          scrollToBottom();
        } catch (error) {
          console.error('Error posting content:', error);
          messages.value.push({
            role: 'assistant',
            content: 'I encountered an error while trying to post your content. Please try again.',
            actions: ['Try again'],
            state: 'error'
          });
        } finally {
          chatLoading.value = false;
        }
      }
      break;
      
    case 'error':
      // No automatic action for errors, let user respond
      break;
      
    default:
      // For other intents, no automatic action
      break;
  }
};

// Function to send a prompt directly (used by suggestion cards and auto-responses)
const sendDirectPrompt = (prompt) => {
  userInput.value = prompt;
  sendMessage();
};

// Add these methods for image handling
const triggerImageUpload = () => {
  console.log('triggerImageUpload');
  imageInput.value.click();
};

const handleImageSelect = (event) => {
  const file = event.target.files[0];
  console.log("GOT THE FILE", file);
  if (file) {
    // Validate file type and size
    if (!file.type.match(/^image\/(jpeg|png|gif|jpg)$/)) {
      imageError.value = 'Please select a valid image file (JPEG, PNG, or GIF)';
      return;
    }
    
    if (file.size > 10 * 1024 * 1024) { // 10MB limit
      imageError.value = 'Image size should be less than 10MB';
      return;
    }
    
    // Clear any previous errors
    imageError.value = '';
    
    // Create preview
    selectedImage.value = file;
    console.log("Selected Image", selectedImage.value);
    const reader = new FileReader();
    reader.onload = (e) => {
      imagePreview.value = e.target.result;
      console.log("Image Preview", imagePreview.value);
    };
    reader.readAsDataURL(file);
    
    // Focus back to input field after selecting image
    nextTick(() => {
      if (inputField.value) {
        inputField.value.focus();
      }
    });
  }
};

const removeImage = () => {
  selectedImage.value = null;
  imagePreview.value = '';
  imageError.value = '';
  if (imageInput.value) {
    imageInput.value.value = '';
  }
};

// Add a watcher for messages to ensure auto-scroll on any changes
watch(messages, () => {
  scrollToBottom();
}, { deep: true });

// Initialize component
onMounted(async () => {
  await removeDynamicCss();
  await removeDynamicJs();
  await initializeCss();
  await initializeScripts();
  await getUserInfo();
  addWelcomeMessage();
  nextTick(() => {
    scrollToBottom();
  });
});

</script>

<style scoped>
.ai-chat-container {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 180px);
  max-height: 1000px;
  position: relative;
  background: #f8f9fa;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.ai-card {
  border: none;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 6px 15px rgba(0,0,0,0.05);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.ai-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(0,0,0,0.1);
}

.chat-header {
  padding: 24px;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-bottom: 1px solid #eee;
  flex-shrink: 0;
}

.assistant-avatar {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  transition: all 0.3s ease;
}

.pulse-animation {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.6);
  }
  70% {
    box-shadow: 0 0 0 15px rgba(102, 126, 234, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
  }
}

.title-wrapper {
  position: relative;
  margin-bottom: 20px;
  display: inline-block;
}

.animated-title {
  color: #333;
  position: relative;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.title-underline {
  height: 3px;
  width: 0;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  position: absolute;
  bottom: -8px;
  left: 50%;
  transform: translateX(-50%);
  animation: underline-grow 1.5s forwards;
}

@keyframes underline-grow {
  0% { width: 0; }
  100% { width: 50px; }
}

.fade-in {
  animation: fadeIn 1s ease-in forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.slide-up {
  animation: slideUp 0.8s ease forwards;
  opacity: 0;
}

@keyframes slideUp {
  from { 
    transform: translateY(20px);
    opacity: 0;
  }
  to { 
    transform: translateY(0);
    opacity: 1;
  }
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  padding-bottom: 150px;
  scroll-behavior: smooth;
  background: #f9fafc;
  min-height: 400px;
  position: relative;
  z-index: 1;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-y: contain;
  scroll-snap-type: y proximity;
}

.message-wrapper {
  scroll-snap-align: end;
}

.message-wrapper:last-child {
  padding-bottom: 250px;
  margin-bottom: 0;
}

.message-in {
  animation: slideInLeft 0.5s forwards;
}

.message-out {
  animation: slideInRight 0.5s forwards;
}

@keyframes slideInLeft {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.message {
  display: flex;
  max-width: 80%;
  align-items: flex-start;
  gap: 8px; /* Reduced from 12px */
}

.message.user {
  margin-left: auto;
  flex-direction: row-reverse;
}

.message-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}

.message.assistant .message-avatar {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.message.user .message-avatar {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
}

.message-content {
  background: white;
  padding: 10px 14px; /* Reduced from 14px 18px */
  border-radius: 18px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  position: relative;
  transition: transform 0.3s ease;
}

.message-content:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.message.assistant .message-content {
  border-top-left-radius: 4px;
}

.message.user .message-content {
  background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
  color: white;
  border-top-right-radius: 4px;
}

.message-text {
  line-height: 1.6;
  white-space: pre-wrap;
  font-size: 0.95rem;
  margin-bottom: 0px;
}

.message.user .message-text :deep(a) {
  color: white;
  text-decoration: underline;
}

/* Action buttons styling */
.message-actions {
  margin-top: 5px;
  gap: 5px;
  flex-wrap: wrap;
}

.action-button {
  padding: 4px 10px;
  font-size: 0.8rem;
  white-space: nowrap;
}

.quick-suggestions {
  position: relative;
  padding: 10px;
  border-top: 1px solid #eee;
  background: white;
  z-index: 2;
}

.suggestion-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: white;
  border-radius: 12px;
  padding: 18px 15px;
  cursor: pointer;
  transition: all 0.3s ease;
  height: 110px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  animation: cardEnter 0.6s forwards;
  opacity: 0;
  transform: translateY(20px);
}

@keyframes cardEnter {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.suggestion-card:hover {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.suggestion-icon {
  font-size: 28px;
  margin-bottom: 12px;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
  color: white;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.suggestion-card:hover .suggestion-icon {
  transform: scale(1.1);
}

.suggestion-card span {
  font-size: 14px;
  font-weight: 500;
  color: #333;
}

.chat-input {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  border-top: 1px solid #eee;
  z-index: 2;
  padding: 5px;
  margin: 0;
  transform: translateZ(0);
}

/* Container for messages to prevent overlap with input */
.messages-container {
  position: relative;
  width: 100%;
  height: 100%;
}

.input-form {
  display: flex;
  gap: 8px;
  padding: 5px 10px;
  background: white;
  position: relative;
  z-index: 2;
}

.modern-input {
  resize: none;
  border-radius: 20px;
  padding: 8px 15px;
  min-height: 40px;
  max-height: 100px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
  background: white;
  width: 100%;
}

.send-btn {
  align-self: flex-end;
  width: 50px;
  height: 50px;
  border-radius: 25px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-3px);
  box-shadow: 0 6px 15px rgba(102, 126, 234, 0.3);
}

.send-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.btn-pulse {
  animation: btnPulse 2s infinite;
}

@keyframes btnPulse {
  0% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.6);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(102, 126, 234, 0);
  }
}

.input-features {
  display: flex;
  gap: 15px;
  margin-top: 5px;
  padding: 5px 10px;
  background: white;
}

.feature-hint {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f5f7fa;
  color: #667eea;
  cursor: pointer;
  transition: all 0.2s ease;
}

.feature-hint:hover {
  background: #e0e7ff;
  transform: scale(1.1);
}

.typing-indicator {
  margin-bottom: 8px;
  margin-left: 40px;
  padding: 8px 14px;
}

.typing-indicator::before {
  content: '';
  position: absolute;
  bottom: 8px;
  left: -8px;
  width: 16px;
  height: 16px;
  background: white;
  transform: rotate(45deg);
  box-shadow: -1px 1px 2px rgba(0,0,0,0.05);
}

.typing-indicator span {
  width: 8px;
  height: 8px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 50%;
  animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(1) {
  animation-delay: 0s;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.3s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.6s;
}

@keyframes typing {
  0% { 
    transform: translateY(0); 
    opacity: 0.8;
  }
  50% { 
    transform: translateY(-8px);
    opacity: 1; 
  }
  100% { 
    transform: translateY(0);
    opacity: 0.8; 
  }
}

/* Improved scrollbar visibility */
.chat-messages::-webkit-scrollbar {
  width: 8px;
}

.chat-messages::-webkit-scrollbar-track {
  background: rgba(0,0,0,0.03);
  border-radius: 4px;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: rgba(102, 126, 234, 0.2);
  border-radius: 4px;
  border: 2px solid transparent;
  background-clip: padding-box;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(102, 126, 234, 0.3);
  border: 2px solid transparent;
  background-clip: padding-box;
}

/* Prevent content shift when scrollbar appears */
.chat-messages {
  scrollbar-gutter: stable;
  margin-right: 0;
  padding-right: 15px;
}

/* Mobile adjustments */
@media (max-width: 768px) {
  .ai-chat-container {
    height: calc(100vh - 140px);
    border-radius: 0;
  }

  .chat-messages {
    padding-bottom: 120px; /* More space on mobile */
  }

  .chat-input {
    padding: 3px;
  }
}

.input-form {
  align-items: flex-end;
}

/* Upload icon in input area */
.upload-media-btn {
  position: relative;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  background: #f5f7fa;
  color: #667eea;
  flex-shrink: 0;
}

.upload-media-btn:hover {
  background: #e0e7ff;
  transform: scale(1.1);
}

/* Upload tooltip */
.upload-tooltip {
  position: absolute;
  top: -40px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(51, 51, 51, 0.9);
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.3s ease;
  z-index: 11;
}

.upload-media-btn:hover .upload-tooltip {
  opacity: 1;
}

/* Triangle pointer for tooltip */
.upload-tooltip::after {
  content: '';
  position: absolute;
  bottom: -5px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid rgba(51, 51, 51, 0.9);
}

/* Image preview section */
.image-preview-section {
  padding: 8px 15px;
  background: #f5f7fa;
  border-top: 1px solid #e0e0e0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease, padding 0.3s ease;
}

.image-preview-section.active {
  max-height: 120px;
  padding: 8px 15px;
}

/* Image preview thumbnail */
.image-thumbnail {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.image-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Remove image button */
.remove-image-btn {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.6);
  border: none;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  z-index: 2;
  padding: 0;
  font-size: 12px;
}

.remove-image-btn:hover {
  background: rgba(255, 0, 0, 0.8);
  transform: scale(1.1);
}

/* Image uploading progress indicator */
.image-uploading {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.spinner {
  width: 24px;
  height: 24px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Error message styling */
.image-error-message {
  color: #dc3545;
  font-size: 12px;
  margin-top: 5px;
  align-self: center;
}

</style> 