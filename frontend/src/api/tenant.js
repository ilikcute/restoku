import axios from '@/api/axios';

export const tenantApi = {
  get: () => axios.get('/settings/tenant'),
  update: (data) => axios.post('/settings/tenant', data),
  getPrinter: () => axios.get('/settings/printer'),
  updatePrinter: (data) => axios.put('/settings/printer', data),
  updateKitchenPrinter: (data) => axios.put('/settings/printer/kitchen', data),
  testPrinter: (type = 'cashier') => axios.post('/settings/printer/test', { type }),
  scanReadyPrinters: () => axios.get('/settings/printer/scan'),
};
