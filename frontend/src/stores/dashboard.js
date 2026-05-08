import { defineStore } from 'pinia';
import { dashboardApi } from '@/api/dashboard';

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    salesToday: 0,
    salesYesterday: 0,
    salesCountToday: 0,
    salesCountYesterday: 0,
    lowStockCount: 0,
    totalProducts: 0,
    totalCustomers: 0,
    customersToday: 0,
    customersYesterday: 0,
    expensesToday: 0,
    chartData: [],
    trendingProducts: [],
    recentOrders: [],
    loading: false
  }),
  actions: {
    async fetchStats() {
      this.loading = true;
      try {
        const response = await dashboardApi.getStats();
        const data = response?.data?.data || {};
        this.salesToday = Number(data.sales_today || 0);
        this.salesYesterday = Number(data.sales_yesterday || 0);
        this.salesCountToday = Number(data.sales_count_today || 0);
        this.salesCountYesterday = Number(data.sales_count_yesterday || 0);
        this.lowStockCount = Number(data.low_stock_count || 0);
        this.totalProducts = Number(data.total_products || 0);
        this.totalCustomers = Number(data.total_customers || 0);
        this.customersToday = Number(data.customers_today || 0);
        this.customersYesterday = Number(data.customers_yesterday || 0);
        this.expensesToday = Number(data.expenses_today || 0);
        this.chartData = data.chart_data || [];
        this.trendingProducts = data.trending_products || [];
        this.recentOrders = data.recent_orders || [];
      } finally {
        this.loading = false;
      }
    }
  }
});
