import { defineStore } from 'pinia';
import { shiftApi } from '@/api/sales';

export const useShiftStore = defineStore('shift', {
  state: () => ({
    activeShift: null,
    shifts: [],
    loading: false
  }),
  getters: {
    hasActiveShift: (state) => Boolean(state.activeShift)
  },
  actions: {
    async fetchCurrentShift() {
      this.loading = true;
      try {
        const response = await shiftApi.getCurrent();
        this.activeShift = response?.data?.data || null;
      } finally {
        this.loading = false;
      }
    },
    async fetchShifts(page = 1) {
      const response = await shiftApi.getAll({ page });
      const collection = response?.data?.data;
      this.shifts = collection?.data || [];
      return collection;
    },
    async openShift(startingCash) {
      const response = await shiftApi.open({ starting_cash: startingCash });
      this.activeShift = response?.data?.data || null;
      return response?.data;
    },
    async closeShift(payload) {
      const response = await shiftApi.close(payload);
      this.activeShift = null;
      return response?.data;
    }
  }
});
