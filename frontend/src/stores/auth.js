import { defineStore } from 'pinia';
import api from '@/api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: sessionStorage.getItem('auth_token') || localStorage.getItem('auth_token') || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                const { remember, ...loginData } = credentials;
                const response = await api.post('/auth/login', loginData);
                const { user, token } = response.data.data;
                
                this.user = user;
                this.token = token;

                if (remember) {
                    localStorage.setItem('auth_token', token);
                    sessionStorage.removeItem('auth_token');
                } else {
                    sessionStorage.setItem('auth_token', token);
                    localStorage.removeItem('auth_token');
                }
                
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Login failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/auth/register', userData);
                const { user, token } = response.data.data;
                
                this.user = user;
                this.token = token;
                
                sessionStorage.setItem('auth_token', token);
                localStorage.removeItem('auth_token');
                
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Registration failed';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async logout(skipApi = false) {
            const currentToken = this.token;
            
            // Bersihkan state dan storage terlebih dahulu untuk menghentikan loop
            this.user = null;
            this.token = null;
            sessionStorage.removeItem('auth_token');
            localStorage.removeItem('auth_token');

            // Hanya panggil API logout jika token ada dan tidak diminta skip (misal karena 401)
            if (!skipApi && currentToken) {
                try {
                    await api.post('/auth/logout');
                } catch (err) {
                    console.error('Logout API error:', err);
                }
            }
        },

        async fetchUser() {
            if (!this.token) return;
            
            try {
                const response = await api.get('/auth/me');
                this.user = response.data.data;
            } catch (err) {
                // Jika error 401, interceptor sudah menangani pembersihan storage
                // Kita cukup bersihkan state di sini tanpa panggil API logout lagi
                this.user = null;
                this.token = null;
                sessionStorage.removeItem('auth_token');
                localStorage.removeItem('auth_token');
                
                // Jangan panggil this.logout() di sini untuk menghindari loop dengan interceptor
            }
        }
    }
});
