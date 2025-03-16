import axios from 'axios';

/**
 * Schedule a post for later publishing
 * @param {Array} platformsData - Array of platform data objects
 * @param {string} content - The post content
 * @param {string} scheduledTime - ISO string of scheduled time
 * @param {boolean} useAiSuggestion - Whether to use AI suggested time
 * @returns {Promise<Object>} - Object containing schedule results
 */
export async function schedulePostService(platformsData, initialPostDescription, scheduledTime, useAiSuggestion = false) {
  try {
    // Create FormData to handle file uploads
    const formData = new FormData();
    
    // Add platforms data as JSON
    formData.append('platforms', JSON.stringify(platformsData));
    
    // Add content
    formData.append('initial_post_description', initialPostDescription);
    
    // Add scheduled time
    formData.append('scheduled_time', scheduledTime);
    
    // Add AI suggestion flag
    formData.append('use_ai_suggestion', useAiSuggestion ? '1' : '0');
    
    // Add media files if present
    platformsData.forEach((platform) => {
      if (platform.media) {
        formData.append(`media_${platform.platform_id}`, platform.media);
      }
    });
    
    const response = await axios.post('/posts/schedule', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    if (response.data.success) {
      return response.data.results;
    } else {
      throw new Error(response.data.message || 'Failed to schedule post');
    }
  } catch (error) {
    console.error('Schedule post error:', error);
    
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