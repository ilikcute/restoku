import api from './axios';

export const financeApi = {
  getAccounts: () => api.get('/finance/accounts'),
  createAccount: (data) => api.post('/finance/accounts', data),
  updateAccount: (id, data) => api.put(`/finance/accounts/${id}`, data),
  deleteAccount: (id) => api.delete(`/finance/accounts/${id}`),
  getTransactions: (params) => api.get('/finance/transactions', { params }),
  createTransaction: (data) => api.post('/finance/transactions', data),
  getExpenseCategories: () => api.get('/finance/expense-categories'),
  getIncomeCategories: () => api.get('/finance/income-categories')
};

export const closingApi = {
  getAll: (params) => api.get('/daily-closings', { params }),
  getById: (id) => api.get(`/daily-closings/${id}`),
  create: (data) => api.post('/daily-closings', data)
};
