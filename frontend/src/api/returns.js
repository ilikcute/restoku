import api from './axios';

export const returnsApi = {
  search: (params) => api.get('/returns/search', { params }),
  storeOrderReturn: (data) => api.post('/returns/orders', data),
  storePurchaseReturn: (data) => api.post('/returns/purchases', data)
};
