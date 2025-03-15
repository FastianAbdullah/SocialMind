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

                      <!-- Chat Messages -->
                      <div class="chat-messages" ref="chatContainer">
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
                              
                              <!-- Move action buttons inside the message itself -->
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
                        <form @submit.prevent="sendMessage" class="input-form">
                          <textarea 
                            v-model="userInput" 
                            @keydown.enter.exact.prevent="sendMessage"
                            placeholder="Type your message here..."
                            class="form-control modern-input"
                            rows="1"
                            ref="inputField"
                            @input="autoResize"
                          ></textarea>
                          <button 
                            type="submit"
                            class="btn btn-primary send-btn"
                            :disabled="!userInput.trim() || chatLoading"
                            :class="{ 'btn-pulse': userInput.trim() && !chatLoading }"
                          >
                            <i class="fas fa-paper-plane"></i>
                          </button>
                        </form>
                        <div class="input-features">
                          <div class="feature-hint" @click="toggleEmojiPicker">
                            <i class="far fa-smile"></i>
                          </div>
                          <div class="feature-hint" @click="sendDirectPrompt('Create a social media post with an image')">
                            <i class="far fa-image"></i>
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
import { ref, onMounted, nextTick, computed } from 'vue';
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
  padding: 25px;
  padding-bottom: 10px;
  scroll-behavior: smooth;
  background: #f9fafc;
}

.message-wrapper {
  margin-bottom: 20px;
  opacity: 0;
  transition: all 0.5s ease;
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
  gap: 12px;
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
  padding: 14px 18px;
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
  margin-bottom: 8px;
}

.message.user .message-text :deep(a) {
  color: white;
  text-decoration: underline;
}

/* Action buttons styling */
.message-actions {
  margin-top: 12px;
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.action-button {
  background: #f0f2f5;
  border: none;
  border-radius: 18px;
  padding: 6px 14px;
  font-size: 0.85rem;
  color: #4361ee;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-button:hover {
  background: #e4e6eb;
  transform: translateY(-2px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.quick-suggestions {
  padding: 20px;
  border-top: 1px solid #eee;
  background: white;
  flex-shrink: 0;
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
  padding: 20px;
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
  gap: 12px;
}

.modern-input {
  resize: none;
  border-radius: 24px;
  padding: 12px 18px;
  min-height: 50px;
  max-height: 120px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  border: 1px solid #e0e0e0;
  transition: all 0.3s ease;
}

.modern-input:focus {
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  border-color: #667eea;
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
  margin-top: 10px;
  padding-left: 10px;
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
  display: flex;
  gap: 4px;
  padding: 12px 18px;
  background: white;
  border-radius: 18px;
  width: fit-content;
  margin-left: 52px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  margin-bottom: 12px;
  position: relative;
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

/* Custom scrollbar */
.chat-messages::-webkit-scrollbar {
  width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
  background: rgba(0,0,0,0.02);
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.1);
  border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
  background: rgba(0,0,0,0.2);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .ai-chat-container {
    height: calc(100vh - 240px);
  }
  
  .message {
    max-width: 90%;
  }
  
  .suggestion-card {
    height: 100px;
  }
  
  .suggestion-icon {
    width: 50px;
    height: 50px;
    font-size: 22px;
  }
}
</style> 