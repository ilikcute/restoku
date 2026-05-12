import api from './axios';

export const profileApi = {
  get: () => api.get('/profile'),
  update: (data) => {
    if (data instanceof FormData) {
      data.append('_method', 'PUT');
      return api.post('/profile', data);
    }
    return api.put('/profile', data);
  },
  updatePassword: (data) => api.put('/profile/password', data)
};

export const userApi = {
  getAll: () => api.get('/users'),
  getById: (id) => api.get(`/users/${id}`),
  create: (data) => api.post('/users', data),
  update: (id, data) => {
    if (data instanceof FormData) {
      data.append('_method', 'PUT');
      return api.post(`/users/${id}`, data);
    }
    return api.put(`/users/${id}`, data);
  },
  delete: (id) => api.delete(`/users/${id}`),
  getRoles: () => api.get('/roles/list'),
  getPermissions: () => api.get('/permissions')
};

export const roleApi = {
  getAll: () => api.get('/roles'),
  getById: (id) => api.get(`/roles/${id}`),
  create: (data) => api.post('/roles', data),
  update: (id, data) => api.put(`/roles/${id}`, data),
  delete: (id) => api.delete(`/roles/${id}`)
};
