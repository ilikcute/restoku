import api from './axios';

export const reportsApi = {
  getSummary: (params) => api.get('/reports/summary', { params }),
  getDailyChart: (params) => api.get('/reports/daily-chart', { params }),
  getTopProducts: (params) => api.get('/reports/top-products', { params }),
  getTransactions: (params) => api.get('/reports/transactions', { params }),
  getPurchases: (params) => api.get('/reports/purchases', { params }),
  getSalesReturns: (params) => api.get('/reports/sales-returns', { params }),
  getPurchaseReturns: (params) => api.get('/reports/purchase-returns', { params }),
  getTax: (params) => api.get('/reports/tax', { params }),
  getTaxFixed: (params) => api.get('/reports/tax-fixed', { params }),
  getAuditLogs: (params) => api.get('/reports/audit-logs', { params }),
  
  exportExcel: (params) => api.get('/reports/export/excel', { params, responseType: 'blob' }),
  exportExcelSales: (params) => api.get('/reports/export/sales', { params, responseType: 'blob' }),
  exportExcelSalesDetail: (params) => api.get('/reports/export/sales-detail', { params, responseType: 'blob' }),
  exportExcelSalesShift: (params) => api.get('/reports/export/sales-shift', { params, responseType: 'blob' }),
  exportPdf: (params) => api.get('/reports/export/pdf', { params, responseType: 'blob' }),
  exportExcelTax: (params) => api.get('/reports/export/tax-excel', { params, responseType: 'blob' }),
  exportPdfTax: (params) => api.get('/reports/export/tax-pdf', { params, responseType: 'blob' }),
  exportExcelTaxFixed: (params) => api.get('/reports/export/tax-fixed-excel', { params, responseType: 'blob' }),
  exportPdfTaxFixed: (params) => api.get('/reports/export/tax-fixed-pdf', { params, responseType: 'blob' }),
  
  syncDpkad: (data) => api.post('/reports/sync-dpkad', data)
};
