import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Request Interceptor: Attach Token
api.interceptors.request.use((config) => {
    // Cek token di sessionStorage atau localStorage sesuai logika AuthStore
    const token = sessionStorage.getItem('auth_token') || localStorage.getItem('auth_token');
    
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Let browser handle Content-Type for FormData (multipart with boundary)
    if (config.data instanceof FormData) {
        delete config.headers['Content-Type'];
    }

    // Cache buster for GET requests to prevent stale data
    if (config.method === 'get') {
        config.params = {
            ...config.params,
            _t: Date.now()
        };
    }

    return config;
}, (error) => {
    return Promise.reject(error);
});

// Response Interceptor: Handle Auth Errors
api.interceptors.response.use((response) => {
    return response;
}, (error) => {
    if (error.response && error.response.status === 401) {
        // Hapus token dari kedua storage
        sessionStorage.removeItem('auth_token');
        localStorage.removeItem('auth_token');
        
        // Hanya redirect jika kita tidak sedang di halaman login/register/public-menu
        const publicPaths = ['/login', '/register', '/menu'];
        if (!publicPaths.includes(window.location.pathname)) {
            window.location.href = '/login';
        }
    }
    return Promise.reject(error);
});

export default api;
