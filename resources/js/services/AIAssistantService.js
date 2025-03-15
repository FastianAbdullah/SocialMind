import axios from 'axios';

class AIAssistantService {
    constructor() {
        this.baseURL = '/ai-assistant';
        this.conversationContext = {
            currentTask: null,
            state: 'idle'
        };
    }

    async processQuery(query) {
        try {
            console.log('Processing query with context:', this.conversationContext);
            const response = await axios.post(`${this.baseURL}/process-query`, {
                query,
                context: this.conversationContext
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('Query response:', response.data);
            
            // Update conversation context based on response
            if (response.data) {
                this.updateContext(response.data);
            }
            
            return response.data;
        } catch (error) {
            console.error('Error processing query:', error);
            return {
                message: 'Sorry, I encountered an error processing your request.',
                actions: ['Try again'],
                state: 'error',
                intent: 'error'
            };
        }
    }

    // Update internal context based on agent response
    updateContext(response) {
        if (response.state) {
            this.conversationContext.state = response.state;
        }
        
        if (response.intent) {
            this.conversationContext.intent = response.intent;
        }
        
        // If we have content, store it for later use
        if (response.content) {
            if (!this.conversationContext.currentTask) {
                this.conversationContext.currentTask = {};
            }
            this.conversationContext.currentTask.content = response.content;
        }
        
        // Store platform information if available
        if (response.platforms) {
            if (!this.conversationContext.currentTask) {
                this.conversationContext.currentTask = {};
            }
            this.conversationContext.currentTask.platforms = response.platforms;
        }
        
        // Store scheduling information if available
        if (response.suggestions) {
            if (!this.conversationContext.currentTask) {
                this.conversationContext.currentTask = {};
            }
            this.conversationContext.currentTask.suggestions = response.suggestions;
        }
        
        console.log('Updated context:', this.conversationContext);
    }

    async generateContent(topic, platform = null) {
        try {
            const response = await axios.post(`${this.baseURL}/generate-content`, {
                topic,
                platform,
                context: this.conversationContext
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.data) {
                this.updateContext(response.data);
            }
            
            return response.data;
        } catch (error) {
            console.error('Error generating content:', error);
            return {
                message: 'Sorry, I encountered an error generating content.',
                actions: ['Try again'],
                state: 'error',
                intent: 'error'
            };
        }
    }

    async suggestPostingTimes(platform) {
        try {
            const response = await axios.get(`${this.baseURL}/suggest-times/${platform}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.data) {
                this.updateContext(response.data);
            }
            
            return response.data;
        } catch (error) {
            console.error('Error getting posting times:', error);
            return {
                message: `Sorry, I couldn't retrieve optimal posting times for ${platform}.`,
                actions: ['Try again'],
                state: 'error',
                intent: 'error'
            };
        }
    }

    async analyzePerformance(postId, platform) {
        try {
            const response = await axios.post(`${this.baseURL}/analyze-performance`, {
                post_id: postId,
                platform,
                context: this.conversationContext
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.data) {
                this.updateContext(response.data);
            }
            
            return response.data;
        } catch (error) {
            console.error('Error analyzing performance:', error);
            return {
                message: 'Sorry, I encountered an error analyzing performance data.',
                actions: ['Try again'],
                state: 'error',
                intent: 'error'
            };
        }
    }
    
    // Method to post content automatically when ready
    async postContent(content, platforms, scheduleTime = null) {
        try {
            const response = await axios.post(`${this.baseURL}/post-content`, {
                content,
                platforms,
                schedule_time: scheduleTime,
                context: this.conversationContext
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.data) {
                this.updateContext(response.data);
            }
            
            return response.data;
        } catch (error) {
            console.error('Error posting content:', error);
            return {
                message: 'Sorry, I encountered an error posting your content.',
                actions: ['Try again'],
                state: 'error',
                intent: 'error'
            };
        }
    }
    
    // Reset conversation context
    resetContext() {
        this.conversationContext = {
            currentTask: null,
            state: 'idle'
        };
    }
}

export default new AIAssistantService(); 