<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
      <Card v-for="card in cards" :key="card.label"
        class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl overflow-hidden hover:shadow-md transition-shadow duration-300">
        <template #content>
          <div class="flex flex-col p-2 space-y-4">
            <p class="text-sm font-semibold text-slate-500">{{ $t(card.label) }}</p>
            <div class="flex items-end justify-between">
              <p class="text-3xl font-extrabold text-slate-800 tracking-tight">{{ card.value }}</p>
              <div :class="['flex items-center justify-center w-12 h-12 rounded-2xl bg-opacity-20', card.bg]">
                <i :class="[card.icon, card.color, 'text-2xl']" />
              </div>
            </div>
            <div class="flex items-center gap-1 mt-2">
              <i
                :class="['pi text-xs', card.trend === 'up' ? 'pi-arrow-up text-emerald-500' : 'pi-arrow-down text-red-500']"></i>
              <span :class="['text-xs font-bold', card.trend === 'up' ? 'text-emerald-500' : 'text-red-500']">{{
                card.percent }}%</span>
              <span class="text-xs text-slate-400">{{ $t('dashboard.vs_last_day') }}</span>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
      <!-- Main Content Left (Chart & Table) -->
      <div class="xl:col-span-2 space-y-6">
        <Card class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl">
          <template #title>
            <div class="flex items-center justify-between text-slate-800 px-2 pt-2">
              <div class="font-bold text-lg">{{ $t('dashboard.sales_revenue') }}</div>
              <Button icon="pi pi-ellipsis-v" text rounded class="!text-slate-400" />
            </div>
          </template>
          <template #content>
            <div class="h-80">
              <Chart type="bar" :data="chartData" :options="chartOptions" />
            </div>
          </template>
        </Card>

        <Card class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl">
          <template #title>
            <div class="flex items-center justify-between text-slate-800 px-2 pt-2">
              <div class="font-bold text-lg flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ $t('dashboard.live_order') }}
              </div>
              <Button label="Today" icon="pi pi-angle-down" iconPos="right" text
                class="!text-slate-500 border border-slate-200 !rounded-xl !px-4 !py-2" />
            </div>
          </template>
          <template #content>
            <div class="pt-2">
              <!-- Using lowStock data as a placeholder for Live Order table layout -->
              <DataTable :value="dashboardStore.recentOrders" :loading="dashboardStore.loading"
                class="p-datatable-sm !border-t !border-slate-100">
                <Column field="order_number" :header="$t('dashboard.order_id')">
                  <template #body="{ data }"><span class="font-bold text-slate-700">{{ data.order_number
                      }}</span></template>
                </Column>
                <Column :header="$t('common.status')">
                  <template #body="{ data }">
                    <span
                      :class="['px-3 py-1 text-xs font-bold rounded-lg', data.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700']">
                      {{ $t(`common.${data.status}`) }}
                    </span>
                  </template>
                </Column>
                <Column :header="$t('checkout.customer')">
                  <template #body="{ data }">
                    <span class="text-xs font-medium text-slate-600">{{ data.customer_name || $t('dashboard.walking_customer')
                    }}</span>
                  </template>
                </Column>
                <Column field="total_amount" :header="$t('dashboard.amount')">
                  <template #body="{ data }"><span class="font-semibold text-slate-700">Rp {{ money(data.total_amount)
                      }}</span></template>
                </Column>
                <Column :header="$t('dashboard.details')">
                  <template #body="{ data }">
                    <Button :label="$t('dashboard.details')" text class="!text-blue-600 !p-0"
                      @click="router.push('/sales/orders/' + data.id)" />
                  </template>
                </Column>
              </DataTable>
            </div>
          </template>
        </Card>
      </div>

      <!-- Right Sidebar (Trending Menu) -->
      <div class="space-y-6">
        <Card class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl">
          <template #title>
            <div class="flex items-center justify-between text-slate-800 px-2 pt-2">
              <div>
                <div class="font-bold text-lg">{{ $t('dashboard.trending_menu') }}</div>
                <div class="text-xs text-slate-400 font-normal">{{ $t('dashboard.most_selling') }}</div>
              </div>
              <Button label="Today" icon="pi pi-angle-down" iconPos="right" text
                class="!text-slate-500 border border-slate-200 !rounded-xl !px-4 !py-2" />
            </div>
          </template>
          <template #content>
            <div class="pt-2 overflow-y-auto max-h-[600px] pr-2 custom-scrollbar space-y-4">
              <!-- Favorite Menu Items -->
              <div v-if="!dashboardStore.trendingProducts.length" class="text-center py-8 text-slate-400">
                <i class="pi pi-inbox text-4xl mb-2"></i>
                <p>{{ $t('pos.empty_cart') }}</p>
              </div>
              <div v-for="(menu, i) in dashboardStore.trendingProducts" :key="i"
                class="bg-slate-50 rounded-2xl p-3 flex flex-col gap-3">
                <img :src="menu.image" alt="Menu" class="w-full h-40 object-cover rounded-xl shadow-sm" />
                <div class="px-1">
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="font-bold text-slate-800">{{ menu.name }}</h4>
                      <p class="text-xs text-slate-500">{{ menu.category }}</p>
                    </div>
                    <i class="pi pi-chart-bar text-slate-300"></i>
                  </div>
                  <div class="flex items-center justify-between mt-4">
                    <div class="flex gap-4 text-xs font-semibold text-slate-600">
                      <span><i class="pi pi-star-fill text-amber-400 mr-1"></i> {{ menu.rating }}</span>
                      <span><i class="pi pi-shopping-bag text-slate-400 mr-1"></i> {{ menu.orders }} {{ $t('dashboard.sold') }}</span>
                    </div>
                    <span class="font-bold text-red-500">Rp {{ money(menu.price) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useDashboardStore } from '@/stores/dashboard';
import { inventoryApi } from '@/api/inventory';
import { useI18n } from 'vue-i18n';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';

const router = useRouter();
const dashboardStore = useDashboardStore();
const { t: $t } = useI18n();

const lowStocks = ref([]);
const loadingStock = ref(false);

const cards = computed(() => {
  const calcTrend = (today, yesterday) => {
    if (!yesterday || yesterday === 0) return { trend: 'up', percent: today > 0 ? 100 : 0 };
    const diff = ((today - yesterday) / yesterday) * 100;
    return { trend: diff >= 0 ? 'up' : 'down', percent: Math.abs(diff).toFixed(0) };
  };

  const salesTrend = calcTrend(dashboardStore.salesToday, dashboardStore.salesYesterday);
  const ordersTrend = calcTrend(dashboardStore.salesCountToday, dashboardStore.salesCountYesterday);
  const customersTrend = calcTrend(dashboardStore.customersToday, dashboardStore.customersYesterday);

  return [
    {
      label: 'dashboard.total_menu',
      value: dashboardStore.totalProducts,
      icon: 'pi pi-box',
      color: 'text-indigo-600',
      bg: 'bg-indigo-100',
      trend: 'up',
      percent: '0'
    },
    {
      label: 'dashboard.orders_today',
      value: dashboardStore.salesCountToday,
      icon: 'pi pi-shopping-bag',
      color: 'text-blue-600',
      bg: 'bg-blue-100',
      trend: ordersTrend.trend,
      percent: ordersTrend.percent
    },
    {
      label: 'dashboard.unique_customers',
      value: dashboardStore.totalCustomers,
      icon: 'pi pi-users',
      color: 'text-emerald-600',
      bg: 'bg-emerald-100',
      trend: customersTrend.trend,
      percent: customersTrend.percent
    },
    {
      label: 'dashboard.revenue_today',
      value: `Rp ${money(dashboardStore.salesToday)}`,
      icon: 'pi pi-wallet',
      color: 'text-red-600',
      bg: 'bg-red-100',
      trend: salesTrend.trend,
      percent: salesTrend.percent
    }
  ];
});

const chartData = computed(() => ({
  labels: dashboardStore.chartData?.map((item) => {
    const label = item.label || '';
    return $t(`days.${label}`) !== `days.${label}` ? $t(`days.${label}`) : label;
  }) || [],
  datasets: [{
    label: $t('dashboard.sales_revenue'),
    data: dashboardStore.chartData?.length ? dashboardStore.chartData.map((item) => item.total) : [0, 0, 0, 0, 0, 0, 0],
    backgroundColor: '#3b82f6',
    borderRadius: 8,
    barPercentage: 0.5
  }]
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, border: { dash: [4, 4] }, grid: { color: '#f1f5f9' } },
    x: { grid: { display: false } }
  }
};

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

async function loadLowStock() {
  loadingStock.value = true;
  try {
    const response = await inventoryApi.getStocks();
    // Ensure we are working with an array even if the API returns something else
    const stocks = Array.isArray(response?.data?.data) ? response.data.data : [];
    lowStocks.value = stocks.filter((stock) => stock.is_low_stock);
  } catch (e) {
    console.error('Failed to load stocks', e);
  } finally {
    loadingStock.value = false;
  }
}

onMounted(async () => {
  try {
    await dashboardStore.fetchStats();
    await loadLowStock();
  } catch (e) {
    console.error('Dashboard mount failed', e);
  }
});
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #e2e8f0;
  border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #cbd5e1;
}

.custom-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: #e2e8f0 transparent;
}
</style>
