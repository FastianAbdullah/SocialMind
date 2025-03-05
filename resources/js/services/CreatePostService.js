import axios from 'axios';

// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

/**
 * Create a post by analyzing content and generating optimized versions
 * @param {string} content - The content to analyze and optimize
 * @returns {Promise<Array>} - Array of generated posts
 */
export async function createPost(content) {
    try {
        const response = await axios.post('/posts/create', { content });
        
        if (response.data.success) {
            return response.data.posts;
        } else {
            throw new Error(response.data.message || 'Failed to create post');
        }
    } catch (error) {
        console.error('Create post error:', error);
        
        if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            console.error('Server error:', error.response.data);
            throw new Error(error.response.data.message || 'Server error occurred');
        } else if (error.request) {
            // The request was made but no response was received
            console.error('No response received:', error.request);
            throw new Error('No response received from server');
        } else {
            // Something happened in setting up the request that triggered an Error
            console.error('Request setup error:', error.message);
            throw error;
        }
    }
}

/**
 * Save a selected post
 * @param {Object} post - The post to save
 * @returns {Promise<Object>} - The saved post data
 */
export async function savePost(post) {
    try {
        const response = await axios.post('/posts/save', post);
        
        if (response.data.success) {
            return response.data.post;
        } else {
            throw new Error(response.data.message || 'Failed to save post');
        }
    } catch (error) {
        console.error('Save post error:', error);
        throw error;
    }
} 