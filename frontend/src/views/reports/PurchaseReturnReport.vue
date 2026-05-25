<template>
  <AppPage :title="$t('sidebar.purchase_return_report')" :breadcrumb="[$t('common.reports'), $t('sidebar.purchase_return_report')]">
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
              <label class="text-xs font-bold text-slate-500 uppercase">Cari Transaksi</label>
              <InputText v-model="search" placeholder="Cari No. Pembelian atau Pemasok..." class="w-full" />
            </div>
            <Button :label="$t('reports.show')" icon="pi pi-search" @click="loadData" :loading="loading" />
          </div>
        </template>
      </Card>

      <!-- Purchase Return Table -->
      <Card class="border-none shadow-sm">
        <template #title>
          <div class="flex justify-between items-center px-2">
            <span class="text-slate-800 font-black tracking-tight text-xl">Daftar Riwayat Retur Pembelian</span>
          </div>
        </template>
        <template #content>
          <AppDataTable :value="filteredReturns" paginator :rows="10" class="app-table" stripedRows responsiveLayout="scroll">
            <template #empty>
              <div class="text-center py-8 text-slate-400">Tidak ada data retur pembelian untuk periode ini.</div>
            </template>
            <Column field="DT_RowIndex" header="No." class="text-center w-12">
              <template #body="{ index }">{{ index + 1 }}</template>
            </Column>
            <Column field="purchase_number" header="No. Pembelian" />
            <Column header="Tgl Retur">
              <template #body="{ data }">{{ formatDateTime(data.return_date) }}</template>
            </Column>
            <Column header="Pemasok">
              <template #body="{ data }">{{ data.supplier?.name || '-' }}</template>
            </Column>
            <Column header="Karyawan">
              <template #body="{ data }">{{ data.user?.name || '-' }}</template>
            </Column>
            <Column header="Penerima Retur">
              <template #body="{ data }">{{ data.return_user?.name || '-' }}</template>
            </Column>
            <Column field="total_return" header="Total Retur" class="text-right">
              <template #body="{ data }">
                <span class="font-bold text-rose-600">Rp {{ money(data.total_return) }}</span>
              </template>
            </Column>
            <Column header="Aksi" class="text-center w-12">
              <template #body="{ data }">
                <Button icon="pi pi-eye" text rounded @click="viewDetail(data)" />
              </template>
            </Column>
            <template #footer>
              <div class="flex justify-end pr-12 gap-4">
                <span class="font-bold text-slate-600 uppercase tracking-wider">Total Nilai Retur:</span>
                <span class="font-black text-xl text-rose-600">Rp {{ money(totalAmount) }}</span>
              </div>
            </template>
          </AppDataTable>
        </template>
      </Card>

    </div>
  </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Column from 'primevue/column';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const router = useRouter();
const loading = ref(false);
const dates = ref([new Date(new Date().getFullYear(), new Date().getMonth(), 1), new Date()]);
const returns = ref([]);
const search = ref('');

const filteredReturns = computed(() => {
  if (!search.value) return returns.value;
  const s = search.value.toLowerCase();
  return returns.value.filter(item => 
    item.purchase_number?.toLowerCase().includes(s) || 
    item.supplier?.name?.toLowerCase().includes(s)
  );
});

const totalAmount = computed(() => {
  return filteredReturns.value.reduce((sum, item) => sum + Number(item.total_return), 0);
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

async function loadData() {
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
    const response = await axios.get('/reports/purchase-returns', { params });
    returns.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load return data', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengambil data retur pembelian.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

function viewDetail(item) {
  const purchaseId = item.purchase_id || item.id;
  router.push({ name: 'purchase-detail', params: { id: purchaseId } });
}

onMounted(() => {
  loadData();
});
</script>
