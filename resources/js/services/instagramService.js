import axios from 'axios';
import router from '../router';

// Use the same Flask API instance from facebookService
const flaskApi = axios.create({
    baseURL: 'https://discountable.co.uk:8443',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    }
});

// For development only - handle self-signed certificate
if (process.env.NODE_ENV === 'development') {
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
        const response = await flaskApi.get('/auth/instagram', {
            params: {
                platform: 'instagram'  // Add this parameter
            }
        });
        
        if (!response.data?.auth_url) {
            throw new Error('Invalid response format: Missing auth_url');
        }
        console.log('Instagram auth URL:', response.data.auth_url);
        return response.data.auth_url;
    } catch (error) {
        if (error.response) {
            console.error('Server error:', error.response.data);
            throw new Error(`Server error: ${error.response.data.message || 'Unknown error'}`);
        } else if (error.request) {
            console.error('No response received:', error.request);
            throw new Error('No response received from server');
        } else {
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
        const response = await axios.get('/instagram/callback', {
            params: { token }
        });

        if (response.data.success) {
            // Make sure to update the store
            socialMediaStore.setInstagramConnection(response.data);
            
            router.push({ 
                path: '/social-links',
                query: { 
                    status: 'success',
                    message: 'Instagram connected successfully'
                }
            });
        }
        return response.data;
    } catch (error) {
        console.error('Instagram callback error:', error);
        throw error;
    }
}

export async function getAccounts() {
    try {
        const response = await flaskApi.get('/instagram/accounts', {
            headers: {
                'Authorization': localStorage.getItem('token')
            }
        });
        return response.data.accounts || [];
    } catch (error) {
        console.error('Get Instagram accounts error:', error);
        if (error.response?.status === 401) {
            await router.push('/login');
        }
        throw error;
    }
}

export async function disconnectInstagram() {
    try {
        const response = await axios.post('/instagram/disconnect');
        if (response.data.success) {
            router.push({
                path: '/social-links',
                query: { 
                    status: 'success',
                    message: 'Instagram disconnected successfully'
                }
            });
        }
        return response.data;
    } catch (error) {
        console.error('Instagram disconnect error:', error);
        throw error;
    }
}

export async function checkConnection() {
    try {
        const response = await axios.get('/instagram/check-connection');
        return response.data;
    } catch (error) {
        console.error('Check Instagram connection error:', error);
        if (error.response?.status === 401) {
            await router.push('/login');
        }
        throw error;
    }
}