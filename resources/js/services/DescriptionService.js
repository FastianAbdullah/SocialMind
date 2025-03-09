import axios from 'axios';

// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

/**
 * Generate optimized content using AI
 * @param {string} text - The text to analyze and optimize
 * @returns {Promise<Object>} - Optimized content and analysis
 */
export async function generateOptimizedContent(text) {
    try {
        const response = await axios.post('/content/generate-optimized', { text });
        
        if (response.data.success) {
            return response.data;
        } else {
            throw new Error(response.data.message || 'Failed to generate content');
        }
    } catch (error) {
        console.error('Content generation error:', error);
        throw error;
    }
}

/**
 * Analyze text content for purpose and hashtags
 * @param {string} text - The text to analyze
 * @returns {Promise<Object>} - Analysis results
 */
export async function analyzeContent(text) {
    try {
        const response = await axios.post('/content/analyze', { text });
        
        if (response.data.success) {
            return response.data;
        } else {
            throw new Error(response.data.message || 'Failed to analyze content');
        }
    } catch (error) {
        console.error('Content analysis error:', error);
        throw error;
    }
}

/**
 * Optimize content based on purpose and examples
 * @param {string} purpose - The content purpose
 * @param {Array} descriptions - Example descriptions to learn from
 * @returns {Promise<Object>} - Optimized content
 */
export async function optimizeContent(purpose, descriptions = []) {
    try {
        const response = await axios.post('/content/optimize', { 
            purpose,
            descriptions 
        });
        
        if (response.data.success) {
            return response.data;
        } else {
            throw new Error(response.data.message || 'Failed to optimize content');
        }
    } catch (error) {
        console.error('Content optimization error:', error);
        throw error;
    }
} 