<template>
  <AppPage title="Laporan Pajak (DPKAD)" :breadcrumb="['Laporan', 'Laporan Pajak']">
    <div class="space-y-6 mt-4">
      
      <!-- Filters -->
      <Card class="border-none shadow-sm">
        <template #content>
          <div class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-500 uppercase">Periode Laporan</label>
              <DatePicker v-model="dates" selectionMode="range" :manualInput="false" showIcon iconDisplay="input" class="w-72" />
            </div>
            <Button label="Tampilkan Laporan" icon="pi pi-search" @click="loadReport" :loading="loading" />
          </div>
        </template>
      </Card>

      <!-- Tax Report Table -->
      <Card class="border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex justify-between items-center px-2">
            <div class="flex flex-col">
              <span class="text-slate-800 font-black tracking-tight text-xl">Rekapitulasi Penjualan Terlapor (TAX)</span>
              <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Hanya menampilkan data yang sudah disinkronkan ke DPKAD</span>
            </div>
            <div class="flex gap-2">
              <Button label="Cetak PDF" icon="pi pi-file-pdf" severity="danger" outlined size="small" @click="exportPdf" />
              <Button label="Export Excel" icon="pi pi-file-excel" severity="success" outlined size="small" @click="exportExcel" />
            </div>
          </div>
        </template>
        <template #content>
          <DataTable :value="reportData" class="p-datatable-sm" stripedRows responsiveLayout="scroll" :loading="loading">
            <template #empty>
              <div class="text-center py-12">
                <i class="pi pi-info-circle text-4xl text-slate-200 mb-3 block"></i>
                <div class="text-slate-400">Tidak ada data transaksi yang tersinkron untuk periode ini.</div>
              </div>
            </template>
            
            <Column field="date" header="Tanggal" class="font-bold text-slate-700">
              <template #body="{ data }">
                <div class="flex flex-col">
                  <span>{{ formatDateId(data.date) }}</span>
                  <span class="text-[10px] text-slate-400 uppercase font-bold">{{ data.day }}</span>
                </div>
              </template>
            </Column>

            <!-- Dynamic Category Columns -->
            <Column v-for="cat in allCategories" :key="cat" :header="cat" class="text-right">
              <template #body="{ data }">
                <span class="text-slate-600">Rp {{ money(data.categories[cat] || 0) }}</span>
              </template>
            </Column>

            <Column field="subtotal" header="Subtotal" class="text-right font-bold bg-slate-50/50">
              <template #body="{ data }">
                Rp {{ money(data.subtotal) }}
              </template>
            </Column>

            <Column field="service" header="Service" class="text-right text-slate-500">
              <template #body="{ data }">
                Rp {{ money(data.service) }}
              </template>
            </Column>

            <Column field="tax" header="Pajak (TAX)" class="text-right text-primary-600 font-bold">
              <template #body="{ data }">
                Rp {{ money(data.tax) }}
              </template>
            </Column>

            <Column field="grand_total" header="Grand Total" class="text-right font-black text-slate-900 bg-primary-50/30">
              <template #body="{ data }">
                Rp {{ money(data.grand_total) }}
              </template>
            </Column>

            <template #footer>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-6 p-4 bg-slate-900 rounded-xl text-white">
                <div class="flex flex-col">
                  <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Total DPP (Net)</span>
                  <span class="text-xl font-black">Rp {{ money(totals.subtotal) }}</span>
                </div>
                <div class="flex flex-col border-l border-slate-700 pl-6">
                  <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Total Service</span>
                  <span class="text-xl font-black">Rp {{ money(totals.service) }}</span>
                </div>
                <div class="flex flex-col border-l border-slate-700 pl-6">
                  <span class="text-[10px] text-primary-400 uppercase font-bold tracking-widest">Total Pajak (PB1)</span>
                  <span class="text-xl font-black text-primary-400">Rp {{ money(totals.tax) }}</span>
                </div>
                <div class="flex flex-col border-l border-slate-700 pl-6">
                  <span class="text-[10px] text-slate-400 uppercase font-bold tracking-widest">Total Terlapor</span>
                  <span class="text-xl font-black text-emerald-400">Rp {{ money(totals.grand) }}</span>
                </div>
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
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const loading = ref(false);
const dates = ref([new Date(new Date().getFullYear(), new Date().getMonth(), 1), new Date()]);
const reportData = ref([]);

const allCategories = computed(() => {
  const cats = new Set();
  reportData.value.forEach(day => {
    Object.keys(day.categories).forEach(cat => cats.add(cat));
  });
  return Array.from(cats).sort();
});

const totals = computed(() => {
  return reportData.value.reduce((acc, day) => {
    acc.subtotal += day.subtotal;
    acc.tax += day.tax;
    acc.service += day.service;
    acc.grand += day.grand_total;
    return acc;
  }, { subtotal: 0, tax: 0, service: 0, grand: 0 });
});

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDateId(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
}

const formatDatePayload = (date) => {
  if (!date) return null;
  const d = new Date(date);
  let month = '' + (d.getMonth() + 1);
  let day = '' + d.getDate();
  let year = d.getFullYear();
  if (month.length < 2) month = '0' + month;
  if (day.length < 2) day = '0' + day;
  return [year, month, day].join('-');
};

async function loadReport() {
  if (!dates.value || !dates.value[0] || !dates.value[1]) {
    toast.add({ severity: 'warn', summary: 'Peringatan', detail: 'Pilih rentang tanggal.', life: 3000 });
    return;
  }

  loading.value = true;
  try {
    const params = {
      start_date: formatDatePayload(dates.value[0]),
      end_date: formatDatePayload(dates.value[1])
    };
    const response = await axios.get('/reports/tax', { params });
    reportData.value = response.data.data || [];
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat laporan pajak.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

async function exportExcel() {
  await downloadFile('/reports/export/tax-excel', `Laporan_Pajak`);
}

async function exportPdf() {
  await downloadFile('/reports/export/tax-pdf', `Laporan_Pajak`, 'pdf');
}

async function downloadFile(url, prefix, type = 'xlsx') {
  if (!dates.value || !dates.value[0] || !dates.value[1]) return;
  
  loading.value = true;
  try {
    const start = formatDatePayload(dates.value[0]);
    const end = formatDatePayload(dates.value[1]);
    const response = await axios.get(url, {
      params: { start_date: start, end_date: end },
      responseType: 'blob'
    });
    
    const downloadUrl = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.setAttribute('download', `${prefix}_${start}_${end}.${type}`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'File berhasil diunduh.', life: 3000 });
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengunduh file.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadReport();
});
</script>
