import axios from '@/api/axios';

export const tenantApi = {
  get: () => axios.get('/settings/tenant'),
  update: (data) => axios.post('/settings/tenant', data),
  getPrinter: () => axios.get('/settings/printer'),
  updatePrinter: (data) => axios.put('/settings/printer', data),
  testPrinter: () => axios.post('/settings/printer/test'),
  scanReadyPrinters: () => axios.get('/settings/printer/scan'),
};
