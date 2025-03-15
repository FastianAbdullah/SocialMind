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
                  <h4>AI Assistant</h4>
                </div>
              </div>
            </div>

            <!-- Main Content -->
            <div class="row">
              <div class="col-12">
                <div class="card">
                  <div class="card-body p-0">
                    <!-- AI Assistant Interface -->
                    <div class="ai-chat-container">
                      <!-- Chat Header -->
                      <div class="chat-header" v-if="messages.length === 1 && messages[0].role === 'assistant'">
                        <div class="avatar-container text-center mb-4">
                          <div class="assistant-avatar mx-auto">
                            <i class="fas fa-robot"></i>
                          </div>
                        </div>
                        <h5 class="text-center mb-2">Hello, {{ userName }}</h5>
                        <p class="text-center text-muted mb-4">
                          I can help with content creation, scheduling, hashtags, and more.
                        </p>
                      </div>

                      <!-- Chat Messages -->
                      <div class="chat-messages" ref="chatContainer">
                        <div v-for="(message, index) in messages" :key="index" 
                          class="message-wrapper"
                          v-show="!(index === 0 && message.role === 'assistant' && messages.length === 1)">
                          <div :class="['message', message.role === 'assistant' ? 'assistant' : 'user']">
                            <div class="message-avatar">
                              <i :class="message.role === 'assistant' ? 'fas fa-robot' : 'fas fa-user'"></i>
                            </div>
                            <div class="message-content">
                              <div class="message-text" v-html="formatMessage(message.content)"></div>
                              
                              <!-- Move action buttons inside the message itself -->
                              <div v-if="message.role === 'assistant' && message.actions && message.actions.length > 0" 
                                class="message-actions">
                                <button 
                                  v-for="(action, actionIndex) in message.actions" 
                                  :key="actionIndex"
                                  @click="sendDirectPrompt(action)"
                                  class="btn btn-sm btn-outline-primary me-2 mb-2"
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

                      <!-- Quick Suggestions -->
                      <div v-if="messages.length === 1 && messages[0].role === 'assistant'" class="quick-suggestions">
                        <h6 class="text-center mb-3">How can I help you with your social media today?</h6>
                        <div class="row">
                          <div class="col-md-3 col-sm-6 mb-3">
                            <div class="suggestion-card" @click="sendDirectPrompt('Create social media content for my business')">
                              <i class="fas fa-pen-fancy"></i>
                              <span>Create Content</span>
                            </div>
                          </div>
                          <div class="col-md-3 col-sm-6 mb-3">
                            <div class="suggestion-card" @click="sendDirectPrompt('What are the best times to post on Instagram and Facebook?')">
                              <i class="fas fa-clock"></i>
                              <span>Posting Times</span>
                            </div>
                          </div>
                          <div class="col-md-3 col-sm-6 mb-3">
                            <div class="suggestion-card" @click="sendDirectPrompt('Find trending hashtags for my fitness business')">
                              <i class="fas fa-hashtag"></i>
                              <span>Hashtags</span>
                            </div>
                          </div>
                          <div class="col-md-3 col-sm-6 mb-3">
                            <div class="suggestion-card" @click="sendDirectPrompt('Analyze my social media performance')">
                              <i class="fas fa-chart-line"></i>
                              <span>Analytics</span>
                            </div>
                          </div>
                        </div>
                      </div>

                      <!-- Input Area -->
                      <div class="chat-input">
                        <form @submit.prevent="sendMessage" class="input-form">
                          <textarea 
                            v-model="userInput" 
                            @keydown.enter.exact.prevent="sendMessage"
                            placeholder="Type your message here..."
                            class="form-control"
                            rows="1"
                            ref="inputField"
                            @input="autoResize"
                          ></textarea>
                          <button 
                            type="submit"
                            class="btn btn-primary send-btn"
                            :disabled="!userInput.trim() || chatLoading"
                          >
                            <i class="fas fa-paper-plane"></i>
                          </button>
                        </form>
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
import { ref, onMounted, nextTick } from 'vue';
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
    chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
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
  
  // Scroll to show user message first
  nextTick(() => {
    scrollToBottom();
  });
  
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
    nextTick(() => {
      scrollToBottom();
    });
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
        // Content was already generated, no need to take action
        console.log('Content already generated by the agent');
      }
      break;
      
    case 'platform_selection':
      if (response.platforms && response.platforms.length > 0) {
        // Automatically progress with the first platform unless multiple options
        // were specifically requested by the user
        if (response.platforms.length === 1 || !response.message.includes('which platform')) {
          const platform = response.platforms[0];
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
      // Check if we have content ready to post (to_post data) and state is "posting"
      if (response.to_post && (response.state === 'posting' || response.state === 'confirmation')) {
        console.log('Content ready to post:', response.to_post);
        // Actually post the content using AIAssistantService
        try {
          chatLoading.value = true;
          
          // Import AIAssistantService if not already imported
          const AIAssistantService = (await import('../services/AIAssistantService')).default;
          
          const postResult = await AIAssistantService.postContent(
            response.to_post.content,
            response.to_post.platforms,
            null // No schedule time for immediate posting
          );
          
          console.log('Post result:', postResult);
          
          // Add a confirmation message about the posting
          messages.value.push({
            role: 'assistant',
            content: postResult.message || 'Your content has been posted successfully!',
            actions: ['Create new content', 'View post status'],
            state: 'posted'
          });
          
          nextTick(() => {
            scrollToBottom();
          });
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
  height: calc(100vh - 280px);
  max-height: 800px;
  position: relative;
  background: #f8f9fa;
  border-radius: 10px;
  overflow: hidden;
}

.chat-header {
  padding: 20px;
  background: white;
  border-bottom: 1px solid #eee;
  flex-shrink: 0;
}

.assistant-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #3f51b5;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  padding-bottom: 10px;
  scroll-behavior: smooth;
}

.message-wrapper {
  margin-bottom: 15px;
}

.message {
  display: flex;
  max-width: 80%;
  align-items: flex-start;
  gap: 10px;
}

.message.user {
  margin-left: auto;
  flex-direction: row-reverse;
}

.message-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.message.assistant .message-avatar {
  background: #3f51b5;
  color: white;
}

.message.user .message-avatar {
  background: #4caf50;
  color: white;
}

.message-content {
  background: white;
  padding: 12px 15px;
  border-radius: 12px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message.user .message-content {
  background: #4caf50;
  color: white;
}

.message-text {
  line-height: 1.5;
  white-space: pre-wrap;
  font-size: 0.95rem;
  margin-bottom: 8px;
}

.message.user .message-text :deep(a) {
  color: white;
  text-decoration: underline;
}

/* Move action buttons inside the message */
.message-actions {
  margin-top: 10px;
  display: flex;
  flex-wrap: wrap;
}

.quick-suggestions {
  padding: 15px 20px;
  border-top: 1px solid #eee;
  background: white;
  flex-shrink: 0;
}

.suggestion-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 15px 10px;
  cursor: pointer;
  transition: all 0.2s;
  height: 100px;
}

.suggestion-card:hover {
  background: #e9ecef;
  transform: translateY(-3px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.suggestion-card i {
  font-size: 24px;
  margin-bottom: 8px;
  color: #3f51b5;
}

.suggestion-card span {
  font-size: 14px;
  font-weight: 500;
}

.chat-input {
  padding: 15px;
  background: white;
  border-top: 1px solid #eee;
  flex-shrink: 0;
  position: sticky;
  bottom: 0;
  width: 100%;
  z-index: 10;
}

.input-form {
  display: flex;
  gap: 10px;
}

.input-form textarea {
  resize: none;
  border-radius: 20px;
  padding: 10px 15px;
  min-height: 46px;
  max-height: 120px;
}

.send-btn {
  align-self: flex-end;
  width: 46px;
  height: 46px;
  border-radius: 23px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.typing-indicator {
  display: flex;
  gap: 4px;
  padding: 10px 15px;
  background: white;
  border-radius: 12px;
  width: fit-content;
  margin-left: 46px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
  margin-bottom: 10px;
}

.typing-indicator span {
  width: 7px;
  height: 7px;
  background: #aaa;
  border-radius: 50%;
  animation: typing 1s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) {
  animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
  animation-delay: 0.4s;
}

@keyframes typing {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

/* Custom scrollbar */
.chat-messages::-webkit-scrollbar {
  width: 5px;
}

.chat-messages::-webkit-scrollbar-track {
  background: transparent;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: #aaa;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .ai-chat-container {
    height: calc(100vh - 240px);
  }
  
  .message {
    max-width: 90%;
  }
}
</style> 