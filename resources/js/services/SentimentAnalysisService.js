import axios from 'axios';

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
    const response = await axios.post('/post/sentiment-analysis', {
      post_id: postId,
      platform: platform
    });
    
    if (response.data.status === 'success') {
      return response.data;
    } else {
      throw new Error(response.data.message || 'Failed to analyze sentiment');
    }
  } catch (error) {
    console.error('Error analyzing sentiment:', error);
    throw error;
  }
}; 