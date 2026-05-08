import api from './axios';

export const inventoryApi = {
  getStocks: (params) => api.get('/inventory/stocks', { params }),
  getMovements: (params) => api.get('/inventory/movements', { params }),
  getMovementDetail: (id, params) => api.get(`/inventory/movements/${id}`, { params }),
  getAdjustments: (params) => api.get('/inventory/adjustments', { params }),
  getAdjustmentById: (id) => api.get(`/inventory/adjustments/${id}`),
  createAdjustment: (data) => api.post('/inventory/adjustments', data),
  getRecommendations: (params) => api.get('/inventory/recommendations', { params }),
  getAlerts: () => api.get('/inventory/alerts')
};
