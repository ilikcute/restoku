<template>
  <AppPage :title="$t('reports.title')" :breadcrumb="[$t('common.reports'), $t('sidebar.recap')]">
    <div class="space-y-6 mt-4">
      
      <!-- Filters -->
      <Card class="border-none shadow-sm">
        <template #content>
          <div class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-500 uppercase">{{ $t('reports.period') }}</label>
              <DatePicker v-model="dates" selectionMode="range" :manualInput="false" showIcon iconDisplay="input" class="w-72" />
            </div>
            <Button :label="$t('reports.show')" icon="pi pi-search" @click="loadAllData" :loading="loading" />
            <div class="flex gap-2">
              <Button label="Excel" icon="pi pi-file-excel" severity="success" @click="exportExcel" />
              <Button label="PDF" icon="pi pi-file-pdf" severity="danger" @click="exportPdf" />
            </div>
          </div>
        </template>
      </Card>

      <!-- KPI Board -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card class="border-none shadow-sm bg-gradient-to-br from-blue-600 to-blue-800 text-white">
          <template #content>
            <div class="flex justify-between items-start">
              <div>
                <p class="text-xs opacity-70 font-bold uppercase mb-1">{{ $t('reports.total_net') }}</p>
                <h3 class="text-2xl font-black">Rp {{ money(summary.sales?.net) }}</h3>
                <p class="text-[10px] opacity-80 mt-1">{{ summary.sales?.count }} {{ $t('reports.transactions') }}</p>
              </div>
              <div class="bg-white/20 p-2 rounded-lg"><i class="pi pi-shopping-cart text-xl"></i></div>
            </div>
          </template>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-emerald-600 to-emerald-800 text-white">
          <template #content>
            <div class="flex justify-between items-start">
              <div>
                <p class="text-xs opacity-70 font-bold uppercase mb-1">{{ $t('reports.gross_profit') }}</p>
                <h3 class="text-2xl font-black">Rp {{ money(summary.financials?.gross_profit) }}</h3>
                <p class="text-[10px] opacity-80 mt-1">Margin: {{ calculateMargin(summary.financials?.gross_profit, summary.sales?.net) }}%</p>
              </div>
              <div class="bg-white/20 p-2 rounded-lg"><i class="pi pi-chart-line text-xl"></i></div>
            </div>
          </template>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-rose-600 to-rose-800 text-white">
          <template #content>
            <div class="flex justify-between items-start">
              <div>
                <p class="text-xs opacity-70 font-bold uppercase mb-1">{{ $t('reports.total_expenses') }}</p>
                <h3 class="text-2xl font-black">Rp {{ money(summary.financials?.expenses) }}</h3>
              </div>
              <div class="bg-white/20 p-2 rounded-lg"><i class="pi pi-external-link text-xl"></i></div>
            </div>
          </template>
        </Card>

        <Card class="border-none shadow-sm bg-gradient-to-br from-indigo-600 to-indigo-800 text-white">
          <template #content>
            <div class="flex justify-between items-start">
              <div>
                <p class="text-xs opacity-70 font-bold uppercase mb-1">{{ $t('reports.net_profit') }}</p>
                <h3 class="text-2xl font-black">Rp {{ money(summary.financials?.net_profit) }}</h3>
              </div>
              <div class="bg-white/20 p-2 rounded-lg"><i class="pi pi-wallet text-xl"></i></div>
            </div>
          </template>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sales Trend Chart -->
        <Card class="lg:col-span-2 border-none shadow-sm">
          <template #title><span class="text-slate-800">{{ $t('reports.daily_trend') }}</span></template>
          <template #content>
            <Chart type="line" :data="chartData" :options="chartOptions" class="h-[350px]" />
          </template>
        </Card>

        <!-- Top Products -->
        <Card class="lg:col-span-1 border-none shadow-sm">
          <template #title><span class="text-slate-800">{{ $t('reports.top_selling') }}</span></template>
          <template #content>
            <DataTable :value="topProducts" class="p-datatable-sm" :rows="10">
              <Column field="product_name" :header="$t('reports.product')">
                <template #body="{ data }">
                  <span class="text-xs font-bold text-slate-700">{{ data.product_name }}</span>
                </template>
              </Column>
              <Column field="total_qty" :header="$t('reports.qty')" class="text-center">
                <template #body="{ data }">
                  <Tag :value="data.total_qty" severity="info" rounded class="text-[10px]" />
                </template>
              </Column>
              <Column field="total_sales" :header="$t('reports.total')" class="text-right">
                <template #body="{ data }">
                  <span class="text-xs font-semibold text-emerald-600">Rp {{ money(data.total_sales) }}</span>
                </template>
              </Column>
            </DataTable>
          </template>
        </Card>
      </div>

      <!-- Financial Details Table -->
      <Card class="border-none shadow-sm">
        <template #title><span class="text-slate-800 font-bold">{{ $t('reports.detail_recap') }}</span></template>
        <template #content>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
              <h4 class="font-bold border-b pb-2 text-blue-600">{{ $t('reports.revenue_sales') }}</h4>
              <div class="flex justify-between text-sm"><span>{{ $t('reports.gross_sales') }}</span><span class="font-bold">Rp {{ money(summary.sales?.gross) }}</span></div>
              <div class="flex justify-between text-sm text-slate-500 italic"><span>- {{ $t('reports.tax_collected') }}</span><span>(Rp {{ money(summary.sales?.tax) }})</span></div>
              <div class="flex justify-between text-sm text-slate-500 italic"><span>- {{ $t('reports.service_charge') }}</span><span>(Rp {{ money(summary.sales?.service) }})</span></div>
              <div class="flex justify-between text-sm text-slate-500 italic"><span>- {{ $t('reports.discount_given') }}</span><span>(Rp {{ money(summary.sales?.discount) }})</span></div>
              <div class="flex justify-between text-sm font-black border-t pt-2"><span>{{ $t('reports.total_net') }}</span><span class="text-blue-700">Rp {{ money(summary.sales?.net) }}</span></div>
            </div>
            <div class="space-y-4">
              <h4 class="font-bold border-b pb-2 text-rose-600">{{ $t('reports.expenses_profit') }}</h4>
              <div class="flex justify-between text-sm"><span>{{ $t('reports.cogs') }}</span><span class="font-bold text-rose-600">Rp {{ money(summary.financials?.cogs) }}</span></div>
              <div class="flex justify-between text-sm font-bold bg-slate-50 p-2 rounded"><span>{{ $t('reports.gross_profit') }}</span><span class="text-emerald-700">Rp {{ money(summary.financials?.gross_profit) }}</span></div>
              <div class="flex justify-between text-sm"><span>{{ $t('reports.op_expenses') }}</span><span class="font-bold text-rose-600">Rp {{ money(summary.financials?.expenses) }}</span></div>
              <div class="flex justify-between text-sm"><span>{{ $t('reports.other_income') }}</span><span class="font-bold text-emerald-600">Rp {{ money(summary.financials?.other_income) }}</span></div>
              <div class="flex justify-between text-lg font-black border-t-2 border-slate-900 pt-2 bg-indigo-50 p-2 rounded">
                <span>{{ $t('reports.est_net_profit') }}</span>
                <span class="text-indigo-700">Rp {{ money(summary.financials?.net_profit) }}</span>
              </div>
            </div>
          </div>
        </template>
      </Card>



    </div>
  </AppPage>

</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from '@/api/axios';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Chart from 'primevue/chart';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const router = useRouter();
const loading = ref(false);
const dates = ref([new Date(new Date().getFullYear(), new Date().getMonth(), 1), new Date()]);
const summary = ref({
  sales: { net: 0, gross: 0, tax: 0, service: 0, discount: 0, count: 0 },
  financials: { gross_profit: 0, net_profit: 0, cogs: 0, expenses: 0, other_income: 0 }
});
const dailyData = ref([]);
const topProducts = ref([]);

function money(value) {
  if (value === undefined || value === null) return '0';
  return Number(value).toLocaleString('id-ID');
}

function calculateMargin(profit, revenue) {
  if (!revenue || revenue === 0) return 0;
  return ((profit / revenue) * 100).toFixed(1);
}

const formatDate = (date) => {
  if (!date) return null;
  const d = new Date(date);
  let month = '' + (d.getMonth() + 1);
  let day = '' + d.getDate();
  let year = d.getFullYear();

  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;

  return [year, month, day].join('-');
};

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

async function loadAllData() {
  if (!dates.value || !dates.value[0] || !dates.value[1]) {
    toast.add({ severity: 'warn', summary: 'Peringatan', detail: 'Harap pilih rentang tanggal lengkap.', life: 3000 });
    return;
  }

  loading.value = true;
  const params = {
    start_date: formatDate(dates.value[0]),
    end_date: formatDate(dates.value[1])
  };

  console.log('Fetching report data with params:', params);

  try {
    const [resSummary, resChart, resTop] = await Promise.all([
      axios.get('/reports/summary', { params }),
      axios.get('/reports/daily-chart', { params }),
      axios.get('/reports/top-products', { params })
    ]);
    
    summary.value = resSummary.data.data;
    dailyData.value = resChart.data.data;
    topProducts.value = resTop.data.data;
  } catch (error) {
    console.error('Failed to load report data', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengambil data laporan.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

const chartData = computed(() => {
  const labels = (dailyData.value || []).map(d => d.date);
  const data = (dailyData.value || []).map(d => d.total);
  
  return {
    labels: labels,
    datasets: [
      {
        label: 'Omzet Penjualan',
        data: data,
        fill: true,
        borderColor: '#4f46e5',
        backgroundColor: 'rgba(79, 70, 229, 0.1)',
        tension: 0.4
      }
    ]
  };
});

const chartOptions = {
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: (value) => 'Rp ' + Number(value).toLocaleString('id-ID')
      }
    }
  }
};

async function exportExcel() {
  await downloadFile('/reports/export/excel', `Rekap_Keuangan`);
}

async function downloadFile(url, prefix) {
  loading.value = true;
  try {
    const start = formatDate(dates.value[0]);
    const end = formatDate(dates.value[1]);
    const response = await axios.get(url, {
      params: { start_date: start, end_date: end },
      responseType: 'blob'
    });
    
    const downloadUrl = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', `${prefix}_${start}_${end}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengunduh file Excel.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

async function exportPdf() {
  loading.value = true;
  try {
    const start = formatDate(dates.value[0]);
    const end = formatDate(dates.value[1]);
    const response = await axios.get('/reports/export/pdf', {
      params: { start_date: start, end_date: end },
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Rekap_Keuangan_${start}_${end}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengunduh PDF.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadAllData();
});
</script>
