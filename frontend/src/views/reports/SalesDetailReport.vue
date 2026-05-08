<template>
  <AppPage :title="$t('sidebar.sales_report')" :breadcrumb="[$t('common.reports'), $t('sidebar.sales_report')]">
    <div class="space-y-6 mt-4">
      
      <!-- Filters -->
      <Card class="border-none shadow-sm">
        <template #content>
          <div class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-500 uppercase">{{ $t('reports.period') }}</label>
              <DatePicker v-model="dates" selectionMode="range" :manualInput="false" showIcon iconDisplay="input" class="w-72" />
            </div>
            <div class="flex flex-col gap-2 flex-1 min-w-[200px]">
              <label class="text-xs font-bold text-slate-500 uppercase">Cari Pesanan</label>
              <InputText v-model="search" placeholder="Cari No. Pesanan atau Pelanggan..." class="w-full" />
            </div>
            <Button :label="$t('reports.show')" icon="pi pi-search" @click="loadTransactions" :loading="loading" />
          </div>
        </template>
      </Card>

      <!-- Detailed Sales Report Table -->
      <Card class="border-none shadow-sm">
        <template #title>
          <div class="flex justify-between items-center px-2">
            <span class="text-slate-800 font-black tracking-tight text-xl">Data Riwayat Transaksi Penjualan</span>
            <div class="flex gap-2">
              <Button label="Excel Penjualan" icon="pi pi-file-excel" severity="success" outlined size="small" @click="exportExcelSales" />
              <Button label="Excel Detail" icon="pi pi-file-excel" severity="success" outlined size="small" @click="exportExcelDetail" />
              <Button label="Excel Per Shift" icon="pi pi-file-excel" severity="success" outlined size="small" @click="exportExcelShift" />
            </div>
          </div>
        </template>
        <template #content>
          <DataTable :value="filteredTransactions" paginator :rows="10" class="p-datatable-sm" stripedRows responsiveLayout="scroll">
            <template #empty>
              <div class="text-center py-8 text-slate-400">Tidak ada data transaksi untuk periode ini.</div>
            </template>
            <Column field="DT_RowIndex" header="No." class="text-center w-12">
              <template #body="{ index }">{{ index + 1 }}</template>
            </Column>
            <Column field="order_number" header="No. Trans" />
            <Column header="Tanggal">
              <template #body="{ data }">{{ formatDateTime(data.created_at) }}</template>
            </Column>
            <Column header="Karyawan">
              <template #body="{ data }">{{ data.user?.name || '-' }}</template>
            </Column>
            <Column field="table_number" header="No. Meja" class="text-center" />
            <Column field="total_amount" header="Total Transaksi" class="text-right">
              <template #body="{ data }">
                <span class="font-bold text-slate-800">Rp {{ money(data.total_amount) }}</span>
              </template>
            </Column>
            <Column header="DPKAD" class="text-center w-32">
              <template #body="{ data }">
                <div class="flex items-center justify-center gap-2">
                  <Checkbox 
                    :modelValue="!!data.is_synced_to_dpkad" 
                    :binary="true" 
                    :disabled="!!data.is_synced_to_dpkad"
                    @update:modelValue="syncToDpkad(data)"
                  />
                  <span v-if="data.is_synced_to_dpkad" class="text-[10px] text-emerald-600 font-bold uppercase">Synced</span>
                </div>
              </template>
            </Column>
            <Column header="Aksi" class="text-center w-12">
              <template #body="{ data }">
                <Button icon="pi pi-eye" text rounded @click="viewDetail(data)" />
              </template>
            </Column>
            <template #footer>
              <div class="flex justify-end pr-12 gap-4">
                <span class="font-bold text-slate-600 uppercase tracking-wider">Total Omzet:</span>
                <span class="font-black text-xl text-primary-600">Rp {{ money(transactionsTotal) }}</span>
              </div>
            </template>
          </DataTable>
        </template>
      </Card>

    </div>
  </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const router = useRouter();
const loading = ref(false);
// Default to current month
const dates = ref([new Date(new Date().getFullYear(), new Date().getMonth(), 1), new Date()]);
const transactions = ref([]);
const search = ref('');

const filteredTransactions = computed(() => {
  if (!search.value) return transactions.value;
  const s = search.value.toLowerCase();
  return transactions.value.filter(item => 
    item.order_number?.toLowerCase().includes(s) || 
    item.customer_name?.toLowerCase().includes(s)
  );
});

const transactionsTotal = computed(() => {
  return filteredTransactions.value.reduce((sum, item) => sum + Number(item.total_amount), 0);
});

function money(value) {
  if (value === undefined || value === null) return '0';
  return Number(value).toLocaleString('id-ID');
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

async function syncToDpkad(order) {
  if (order.is_synced_to_dpkad) return;

  loading.value = true;
  try {
    const response = await axios.post('/reports/sync-dpkad', {
      order_ids: [order.id]
    });

    if (response.data.success) {
      toast.add({ severity: 'success', summary: 'Berhasil', detail: response.data.message, life: 3000 });
      // Update local data
      order.is_synced_to_dpkad = true;
    }
  } catch (error) {
    console.error('Failed to sync to DPKAD', error);
    toast.add({ 
      severity: 'error', 
      summary: 'Gagal', 
      detail: error.response?.data?.message || 'Gagal menyinkronkan data ke DPKAD.', 
      life: 3000 
    });
  } finally {
    loading.value = false;
  }
}

async function loadTransactions() {
  if (!dates.value || !dates.value[0] || !dates.value[1]) {
    toast.add({ severity: 'warn', summary: 'Peringatan', detail: 'Harap pilih rentang tanggal lengkap.', life: 3000 });
    return;
  }

  loading.value = true;
  const params = {
    start_date: formatDate(dates.value[0]),
    end_date: formatDate(dates.value[1])
  };

  try {
    const response = await axios.get('/reports/transactions', { params });
    transactions.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load transaction data', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengambil data transaksi.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

async function exportExcelSales() {
  await downloadFile('/reports/export/sales', `Laporan_Penjualan`);
}

async function exportExcelDetail() {
  await downloadFile('/reports/export/sales-detail', `Laporan_Penjualan_Detail`);
}

async function exportExcelShift() {
  await downloadFile('/reports/export/sales-shift', `Laporan_Per_Shift`);
}

async function downloadFile(url, prefix) {
  if (!dates.value || !dates.value[0] || !dates.value[1]) return;
  
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

function viewDetail(order) {
  router.push({ name: 'order-detail', params: { id: order.id } });
}

onMounted(() => {
  loadTransactions();
});
</script>
