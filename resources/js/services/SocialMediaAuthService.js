import axios from 'axios';
import router from '../router';

// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

// Get the Auth URL.
export async function getAuthUrl(platform) {
    try {
        let response;
        if (platform === 'facebook') {
            response = await axios.get('/facebook/auth');
        } else if (platform === 'linkedin') {
            response = await axios.get('/linkedin/auth');
        }
        console.log('Auth URL Response:', response.data);
        
        if (!response.data?.auth_url) {
            throw new Error('Invalid response format: Missing auth_url');
        }
        return response.data.auth_url;
    } catch (error) {
        if (error.response) {
            // The request was made and the server responded with a status code
            // that falls out of the range of 2xx
            console.error('Server error:', error.response.data);
            throw new Error(`Server error: ${error.response.data.message || 'Unknown error'}`);
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

// Update other service methods to use web routes
export async function getPages(platform) {
    try {
        if (platform === 'facebook') {
            const response = await axios.get('/facebook/pages');
            return response.data.pages || [];
        }
        else {
            return [];
        }
    } catch (error) {
        console.error('Get pages error:', error);
        if (error.response?.status === 401) {
            await router.push('/login');
        }
        throw error;
    }
}

// Disconnect Facebook.
export async function disconnectFacebook() {
    try {
        const response = await axios.post('/facebook/disconnect');
        if (response.data.success) {
            router.push({
                path: '/social-links',
                query: { 
                    status: 'success',
                    message: 'Facebook disconnected successfully'
                }
            });
        }
        return response.data;
    } catch (error) {
        console.error('Facebook disconnect error:', error);
        throw error;
    }
}

// Disconnect Linkedin.
export async function disconnectLinkedin() {
    try {
        const response = await axios.post('/linkedin/disconnect');
        if (response.data.success) {
            router.push({
                path: '/social-links',
                query: { status: 'success', message: 'Linkedin disconnected successfully' }
            });
        }
        return response.data;
    } catch (error) {
        console.error('Linkedin disconnect error:', error);
        throw error;
    }
}