import axios from 'axios';

const publicInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1',
  headers: {
    'Accept': 'application/json'
  }
});

export const publicApi = {
  getMenu: () => publicInstance.get('/catalog'),
  createOrder: (data) => publicInstance.post('/save-order', data)
};
