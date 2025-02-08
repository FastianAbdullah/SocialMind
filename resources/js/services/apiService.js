const API_BASE_URL = 'https://localhost:8443';

export async function fetchApi(endpoint, options = {}) {
    let url = `${API_BASE_URL}${endpoint}`;
    
    if (options.params) {
        url += `?${options.params.toString()}`;
    }
    
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...options.headers
            },
            mode: 'cors',
            credentials: 'include'
        });
        
        if (!response.ok) {
            if (response.status === 0) {
                throw new Error('Network error - CORS issue or server unreachable');
            }
            const errorData = await response.json().catch(() => null);
            throw new Error(errorData?.message || response.statusText);
        }
        
        return response.json();
    } catch (error) {
        console.error('API call error:', error);
        throw error instanceof Error ? error : new Error('An unknown error occurred');
    }
}
