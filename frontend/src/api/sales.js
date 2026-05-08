import api from './axios';

export const shiftApi = {
  getCurrent: () => api.get('/shifts/current'),
  getAll: (params) => api.get('/shifts', { params }),
  open: (data) => api.post('/shifts/open', data),
  close: (data) => api.post('/shifts/close', data)
};

export const orderApi = {
  getAll: (params) => api.get('/orders', { params }),
  getById: (id) => api.get(`/orders/${id}`),
  create: (data) => api.post('/orders', data),
  fetchPending: (token) => api.get(`/orders/pending/${token}`)
};

export const purchaseApi = {
  getAll: (params) => api.get('/purchases', { params }),
  getById: (id) => api.get(`/purchases/${id}`),
  create: (data) => api.post('/purchases', data)
};
