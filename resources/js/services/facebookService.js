import axios from 'axios';
import router from '../router';

// Configure axios defaults
axios.defaults.withCredentials = true;
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;


// Create a custom axios instance for Flask backend
const flaskApi = axios.create({
    baseURL: 'https://localhost:8443',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// For development only - handle self-signed certificate
if (process.env.NODE_ENV === 'development') {
    // Add custom error handling for SSL certificate issues
    flaskApi.interceptors.request.use((config) => {
        return config;
    }, (error) => {
        if (error.code === 'CERT_HAS_EXPIRED' || error.code === 'CERT_INVALID') {
            console.warn('Certificate validation error - continuing in development mode');
            return Promise.resolve();
        }
        return Promise.reject(error);
    });
}

export async function getAuthUrl() {
    try {
        const response = await flaskApi.get('/auth/facebook');
        
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
export async function handleAuthCallback(token) {
    if (!token) {
        throw new Error('No token provided');
    }
    
    try {
        console.log('Handling OAuth callback with token:', token);
        const response = await axios.get('/facebook/callback', {
            params: { token },
            headers: {
                'Accept': 'application/json'
            }
        });

        console.log('Callback response:', response);

        if (response.data.success) {
            router.push({ 
                path: '/social-links',
                query: { 
                    status: 'success',
                    message: 'Facebook connected successfully'
                }
            });
        }
        return response.data;
    } catch (error) {
        console.error('Callback error:', error);
        router.push({ 
            path: '/social-links',
            query: { 
                status: 'error',
                message: error.message
            }
        });
        throw error;
    }
}

// Update other service methods to use web routes
export async function getPages() {
    try {
        const response = await axios.get('/facebook/pages');
        return response.data.pages || [];
    } catch (error) {
        console.error('Get pages error:', error);
        if (error.response?.status === 401) {
            await router.push('/login');
        }
        throw error;
    }
}

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
