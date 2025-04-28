import axios from 'axios';

export const getUserPosts = async () => {
  try {
    const response = await axios.get('/posts/user');
    
    if (response.data.success) {
      return response.data.posts;
    } else {
      throw new Error(response.data.message || 'Failed to fetch posts');
    }
  } catch (error) {
    console.error('Error fetching user posts:', error);
    throw error;
  }
};

export const getPostComments = async (postId, platform) => {
  try {
    const response = await axios.post('/post/comments', {
      post_id: postId,
      platform: platform
    });
    
    if (response.data.status === 'success') {
      return response.data.comments;
    } else {
      throw new Error(response.data.message || 'Failed to fetch comments');
    }
  } catch (error) {
    console.error('Error fetching post comments:', error);
    throw error;
  }
};

export const analyzeSentiment = async (postId, platform) => {
  try {
    // Validate required parameters
    if (!postId) {
      throw new Error('Post ID is required for sentiment analysis');
    }
    
    if (!platform) {
      throw new Error('Platform is required for sentiment analysis');
    }
    
    console.log('Sending sentiment analysis request with:', { post_id: postId, platform: platform });
    
    const response = await axios.post('/post/sentiment-analysis', {
      post_id: postId,
      platform: platform
    });
    
    console.log('Raw sentiment analysis response:', response);
    
    // Important: Check full response structure to ensure we have what we need
    if (response.data) {
      console.log('Sentiment analysis successful, response data:', response.data);
      return response.data;
    } else {
      console.error('Sentiment analysis API unexpected response format:', response.data);
      throw new Error('Failed to analyze sentiment - unexpected response format');
    }
  } catch (error) {
    console.error('Error analyzing sentiment:', error);
    
    // Extract more specific error message if available
    let errorMessage = 'Failed to analyze sentiment';
    
    if (error.response && error.response.data) {
      console.error('API error details:', error.response.data);
      
      if (error.response.status === 401) {
        errorMessage = 'Authentication error. Please reconnect your account.';
      } else if (error.response.data.errors && error.response.data.errors.post_id) {
        errorMessage = error.response.data.errors.post_id[0];
      } else if (error.response.data.message) {
        errorMessage = error.response.data.message;
      }
    } else if (error.message) {
      errorMessage = error.message;
    }
    
    throw new Error(errorMessage);
  }
}; 