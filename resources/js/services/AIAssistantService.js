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
            // Ensure platforms are properly formatted
            const formattedPlatforms = platforms.map(platform => {
                // If platform is already an object with platform_id, return it as is
                if (typeof platform === 'object' && platform.platform_id) {
                    return platform;
                }
                
                // If it's just a platform ID number, convert to object format
                if (typeof platform === 'number') {
                    return { platform_id: platform };
                }
                
                // If it's a string platform ID, convert to number then to object
                if (typeof platform === 'string' && !isNaN(Number(platform))) {
                    return { platform_id: Number(platform) };
                }
                
                // If it's a string platform name, try to map to correct ID
                const platformIdMap = {
                    'facebook': 1,
                    'instagram': 2, 
                    'linkedin': 3
                };
                
                if (typeof platform === 'string' && platformIdMap[platform.toLowerCase()]) {
                    return { platform_id: platformIdMap[platform.toLowerCase()] };
                }
                
                // If all else fails, return the original platform (server will validate)
                return platform;
            });

            console.log('Formatted platforms for posting:', formattedPlatforms);
            
            // Ensure we have the context with original prompt
            const updatedContext = { 
                ...this.conversationContext,
                originalPrompt: this.conversationContext.currentTask?.topic || 
                                this.conversationContext.query || 
                                'Content generation request'
            };
            
            console.log('Context for posting with original prompt:', updatedContext);
            
            const response = await axios.post(`${this.baseURL}/post-content`, {
                content,
                platforms: formattedPlatforms,
                schedule_time: scheduleTime,
                context: updatedContext
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