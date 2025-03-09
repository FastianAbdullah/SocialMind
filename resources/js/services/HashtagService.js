import axios from 'axios';

// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

/**
 * Search hashtags based on a seed hashtag
 * @param {string} hashtag - The seed hashtag to search for
 * @returns {Promise<Object>} - Trending hashtags results
 */
export async function searchHashtags(hashtag) {
    try {
        const response = await axios.post('/hashtags/search', { 
            hashtag
        });
        
        if (response.data.success) {
            return response.data.data;
        } else {
            throw new Error(response.data.message || 'Failed to search hashtags');
        }
    } catch (error) {
        console.error('Search hashtags error:', error);
        
        if (error.response) {
            console.error('Server error:', error.response.data);
            throw new Error(error.response.data.message || 'Server error occurred');
        } else if (error.request) {
            console.error('No response received:', error.request);
            throw new Error('No response received from server');
        } else {
            console.error('Request setup error:', error.message);
            throw error;
        }
    }
}

/**
 * Analyze content for hashtag suggestions
 * @param {string} text - The text to analyze
 * @returns {Promise<Object>} - Analysis results with hashtag suggestions
 */
export async function analyzeContent(text) {
   
    try {
        const response = await axios.post('/hashtags/analyze', { text });
        
        if (response.data.success) {
            return response.data.data;
        } else {
            throw new Error(response.data.message || 'Failed to analyze content');
        }
    } catch (error) {
        console.error('Content analysis error:', error);
        throw error;
    }
} 