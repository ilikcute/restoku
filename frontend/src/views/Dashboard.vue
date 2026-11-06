<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
      <MetricCard
        v-for="card in cards"
        :key="card.label"
        :title="$t(card.label)"
        :value="card.value"
        :icon="card.icon"
        :color="card.colorKey"
        :trend="card.trend"
        :percent="card.percent"
        :trendText="$t('dashboard.vs_last_day')"
      />
    </div>

    <!-- Middle Section: Chart & Trending Menu side by side -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3 mt-6">
      <div class="xl:col-span-2">
        <Card class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl h-full">
          <template #title>
            <div class="flex items-center justify-between text-slate-800 px-2 pt-2">
              <div class="font-bold text-lg">{{ $t('dashboard.sales_revenue') }}</div>
              <Button icon="pi pi-ellipsis-v" text rounded class="!text-slate-400" />
            </div>
          </template>
          <template #content>
            <div class="h-64">
              <Chart type="bar" :data="chartData" :options="chartOptions" />
            </div>
          </template>
        </Card>
      </div>

      <div class="xl:col-span-1">
        <Card class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl h-full flex flex-col">
          <template #title>
            <div class="flex items-center justify-between text-slate-800 px-2 pt-2">
              <div>
                <div class="font-bold text-lg">{{ $t('dashboard.trending_menu') }}</div>
                <div class="text-xs text-slate-400 font-normal">{{ $t('dashboard.most_selling') }}</div>
              </div>
            </div>
          </template>
          <template #content>
            <div class="pt-2 overflow-y-auto h-64 pr-2 custom-scrollbar">
              <div v-if="!dashboardStore.trendingProducts.length" class="text-center py-8 text-slate-400">
                <i class="pi pi-inbox text-4xl mb-2"></i>
                <p>{{ $t('common.no_data') }}</p>
              </div>
              <div v-for="(menu, i) in dashboardStore.trendingProducts" :key="i"
                class="flex items-center gap-4 py-3 border-b border-slate-100 last:border-0">
                <img :src="menu.image" alt="Menu" class="w-16 h-16 object-cover rounded-xl shadow-sm" />
                <div class="flex-1">
                  <div class="flex justify-between items-start">
                    <h4 class="font-bold text-slate-800 text-sm">{{ menu.name }}</h4>
                    <span class="font-bold text-red-500 text-sm whitespace-nowrap ml-2">Rp {{ money(menu.price) }}</span>
                  </div>
                  <p class="text-xs text-slate-500 mt-0.5">{{ menu.category }}</p>
                  <div class="flex items-center gap-4 mt-2 text-xs font-semibold text-slate-600">
                    <span><i class="pi pi-star-fill text-amber-400 text-[10px] mr-1"></i> {{ menu.rating }}</span>
                    <span><i class="pi pi-shopping-bag text-slate-400 text-[10px] mr-1"></i> {{ menu.orders }} {{ $t('dashboard.sold') }}</span>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </Card>
      </div>
    </div>

    <!-- Bottom Section: Live Order Table full width -->
    <div class="mt-6">
      <Card class="!bg-white border !border-slate-100 shadow-sm !rounded-3xl">
        <template #title>
          <div class="flex items-center justify-between text-slate-800 px-2 pt-2">
            <div class="font-bold text-lg flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ $t('dashboard.live_order') }}
            </div>
          </div>
        </template>
        <template #content>
          <div class="pt-2">
            <AppDataTable :value="dashboardStore.recentOrders" :loading="dashboardStore.loading"
              class="p-datatable-sm !border-t !border-slate-100">
              <Column field="order_number" :header="$t('dashboard.order_id')">
                <template #body="{ data }"><span class="font-bold text-slate-700">{{ data.order_number }}</span></template>
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
                  <span class="text-xs font-medium text-slate-600">{{ data.customer_name || $t('dashboard.walking_customer') }}</span>
                </template>
              </Column>
              <Column field="total_amount" :header="$t('dashboard.amount')">
                <template #body="{ data }"><span class="font-semibold text-slate-700">Rp {{ money(data.total_amount) }}</span></template>
              </Column>
              <Column :header="$t('dashboard.details')">
                <template #body="{ data }">
                  <Button :label="$t('dashboard.details')" text class="!text-blue-600 !p-0"
                    @click="router.push('/sales/orders/' + data.id)" />
                </template>
              </Column>
            </AppDataTable>
          </div>
        </template>
      </Card>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import AppDataTable from '@/components/AppDataTable.vue';
import MetricCard from '@/components/ui/MetricCard.vue';
import { money } from '@/utils/formatters';
import { useRouter } from 'vue-router';
import { useDashboardStore } from '@/stores/dashboard';
import { useI18n } from 'vue-i18n';
import Card from 'primevue/card';
import Chart from 'primevue/chart';
import Button from 'primevue/button';
import Column from 'primevue/column';

const router = useRouter();
const dashboardStore = useDashboardStore();
const { t: $t } = useI18n();

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
      colorKey: 'indigo',
      trend: 'up',
      percent: '0'
    },
    {
      label: 'dashboard.orders_today',
      value: dashboardStore.salesCountToday,
      icon: 'pi pi-shopping-bag',
      colorKey: 'blue',
      trend: ordersTrend.trend,
      percent: ordersTrend.percent
    },
    {
      label: 'dashboard.unique_customers',
      value: dashboardStore.totalCustomers,
      icon: 'pi pi-users',
      colorKey: 'emerald',
      trend: customersTrend.trend,
      percent: customersTrend.percent
    },
    {
      label: 'dashboard.revenue_today',
      value: `Rp ${money(dashboardStore.salesToday)}`,
      icon: 'pi pi-wallet',
      colorKey: 'rose',
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

onMounted(async () => {
  try {
    await dashboardStore.fetchStats();
  } catch (e) {
    console.error('Dashboard mount failed', e);
  }
});
</script>
