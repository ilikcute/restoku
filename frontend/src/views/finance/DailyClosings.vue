<template>
  <AppPage :title="$t('finance.daily_closings')" :breadcrumb="[$t('common.finance'), $t('finance.daily_closings')]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
      
      <!-- Current Closing Status / Form -->
      <div class="lg:col-span-1 space-y-6">
        <Card class="border-none shadow-sm overflow-hidden">
          <template #header>
            <div :class="isSelectedDateClosed ? 'bg-indigo-600' : 'bg-slate-700'" class="p-4 flex justify-between items-center transition-colors duration-500">
              <h3 class="text-white font-bold flex items-center gap-2">
                <i class="pi pi-calendar"></i>
                {{ isSelectedDateClosed ? 'Harian Telah Ditutup' : 'Tutup Harian' }}
              </h3>
              <Tag v-if="isSelectedDateClosed" value="CLOSED" severity="info" class="bg-white/20 border-none text-white px-3" />
            </div>
          </template>
          <template #content>
            <div v-if="isSelectedDateClosed" class="text-center py-6 space-y-4">
              <div class="w-20 h-20 bg-indigo-50 rounded-full flex items-center justify-center mx-auto text-indigo-500 ring-8 ring-indigo-50/50">
                <i class="pi pi-check-circle text-4xl"></i>
              </div>
              <div class="px-4">
                <p class="font-bold text-slate-800 text-lg">Harian Selesai</p>
                <p class="text-xs text-slate-500 leading-relaxed">Laporan harian untuk tanggal {{ formattedSelectedDate }} telah diproses.</p>
              </div>
              <div class="px-4 pb-2">
                <Button label="LIHAT DETAIL LAPORAN" icon="pi pi-file-pdf" class="w-full font-bold bg-indigo-50 text-indigo-700 border-none h-12" @click="showDetailForDate" />
              </div>
              <div class="px-4">
                <Button label="Ganti Tanggal" text severity="secondary" size="small" @click="dateValue = new Date()" />
              </div>
            </div>

            <div v-else class="space-y-6">
              <div class="p-4 bg-amber-50 rounded-2xl border border-amber-100 flex gap-4">
                <i class="pi pi-exclamation-triangle text-amber-500 text-xl"></i>
                <div class="flex flex-col">
                  <p class="text-xs font-bold text-amber-800 uppercase tracking-wider">Perhatian Penting</p>
                  <p class="text-[10px] text-amber-700 leading-tight">Sistem akan menarik akumulasi dari seluruh Shift yang telah ditutup pada tanggal yang dipilih. Pastikan tidak ada Shift yang masih berstatus OPEN.</p>
                </div>
              </div>

              <div class="space-y-4 px-2">
                <div class="field">
                  <label class="text-[10px] font-black text-slate-400 block mb-2 uppercase tracking-widest">Pilih Tanggal Closing</label>
                  <DatePicker v-model="dateValue" dateFormat="yy-mm-dd" showIcon class="w-full" />
                </div>

                <div class="field">
                  <label class="text-[10px] font-black text-slate-400 block mb-2 uppercase tracking-widest">Catatan (Opsional)</label>
                  <Textarea v-model="notes" rows="3" class="w-full bg-slate-50 border-slate-200" placeholder="Ringkasan atau catatan penting operasional hari ini..." />
                </div>
              </div>

              <div class="px-2 pb-2">
                <Button 
                  label="PROSES TUTUP HARIAN" 
                  icon="pi pi-check-square" 
                  class="w-full h-14 text-lg font-black tracking-widest shadow-xl shadow-indigo-100 rounded-2xl bg-indigo-600 border-none" 
                  :loading="saving" 
                  :disabled="isSelectedDateClosed"
                  @click="save" 
                />
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- History & Stats -->
      <div class="lg:col-span-2 space-y-6">
        <Card class="border-none shadow-sm h-full">
          <template #header>
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
              <h3 class="font-bold text-slate-800 flex items-center gap-2 text-sm uppercase tracking-wider">
                <i class="pi pi-history text-indigo-500"></i>
                Riwayat Tutup Harian
              </h3>
              <Button icon="pi pi-refresh" text rounded @click="load" :loading="loading" />
            </div>
          </template>
          <template #content>
            <DataTable :value="rows" :loading="loading" paginator :rows="10" class="p-datatable-sm" stripedRows responsiveLayout="scroll">
              <Column field="closing_date" header="Tgl. Closing">
                <template #body="{ data }">
                  <span class="text-xs font-bold text-slate-700">{{ formatDate(data.closing_date) }}</span>
                </template>
              </Column>
              <Column field="total_transactions" header="Trans" class="text-center w-20" />
              <Column header="Total Revenue" class="text-right">
                <template #body="{ data }">
                  <span class="text-xs font-black text-slate-800">Rp {{ money(data.total_revenue) }}</span>
                </template>
              </Column>
              <Column header="Bersih (Net)" class="text-right">
                <template #body="{ data }">
                  <span class="text-xs font-black text-emerald-600">Rp {{ money(data.net_revenue) }}</span>
                </template>
              </Column>
              <Column header="Status" class="text-center w-24">
                <template #body>
                  <Tag value="COMPLETED" severity="secondary" class="text-[10px]" />
                </template>
              </Column>
              <Column header="Aksi" class="text-center w-24">
                <template #body="{ data }">
                  <div class="flex justify-center gap-1">
                    <Button icon="pi pi-eye" severity="info" text rounded size="small" @click="showDetail(data)" />
                    <Button icon="pi pi-print" text rounded severity="secondary" size="small" @click="printDailyReport(data.id)" />
                  </div>
                </template>
              </Column>
            </DataTable>
          </template>
        </Card>
      </div>
    </div>

    <!-- Detail Modal -->
    <Dialog v-model:visible="detailVisible" modal header="Detail Laporan Tutup Harian" :style="{ width: '32rem' }">
      <div v-if="selectedClosing" class="space-y-6 py-4">
        <div class="bg-indigo-900 rounded-2xl p-6 text-white relative overflow-hidden shadow-xl">
          <div class="relative z-10">
            <div class="flex justify-between items-start mb-6">
              <div>
                <p class="text-[10px] opacity-60 font-bold uppercase tracking-widest mb-1">Tanggal Operasional</p>
                <p class="font-bold text-sm">{{ formatDate(selectedClosing.closing_date) }}</p>
              </div>
              <div class="text-right">
                <p class="text-[10px] opacity-60 font-bold uppercase tracking-widest mb-1">Total Pendapatan Bersih</p>
                <p class="text-2xl font-black text-emerald-400">Rp {{ money(selectedClosing.net_revenue) }}</p>
              </div>
            </div>
          </div>
          <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
        </div>

        <div class="space-y-2">
          <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-2 mb-2">Akumulasi Shift Hari Ini</h4>
          <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
            <div class="p-3 flex justify-between items-center border-b border-slate-50">
              <span class="text-sm text-slate-600">Total Penjualan (Bruto)</span>
              <span class="font-bold text-slate-800">Rp {{ money(selectedClosing.total_revenue) }}</span>
            </div>
            <div class="p-3 flex justify-between items-center border-b border-slate-50 bg-emerald-50/20">
              <span class="text-sm text-emerald-700">Total Pendapatan Lain (+)</span>
              <span class="font-bold text-emerald-700">+ Rp {{ money(selectedClosing.total_income) }}</span>
            </div>
            <div class="p-3 flex justify-between items-center border-b border-slate-50 bg-rose-50/20">
              <span class="text-sm text-rose-600">Total Pengeluaran (-)</span>
              <span class="font-bold text-rose-600">- Rp {{ money(selectedClosing.total_expense) }}</span>
            </div>
            <div class="p-3 flex justify-between items-center border-b border-slate-50">
              <span class="text-sm text-slate-600">Total Transaksi</span>
              <span class="font-bold text-slate-800">{{ selectedClosing.total_transactions }} Transaksi</span>
            </div>
            <div class="p-4 flex justify-between items-center bg-indigo-50/50">
              <span class="font-bold text-indigo-900">Total Pendapatan Bersih</span>
              <span class="text-xl font-black text-indigo-700">Rp {{ money(selectedClosing.net_revenue) }}</span>
            </div>
          </div>
        </div>

        <div v-if="selectedClosing.notes" class="p-4 bg-slate-50 rounded-xl border border-slate-100">
          <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Catatan Penutupan</p>
          <p class="text-xs text-slate-700 italic">"{{ selectedClosing.notes }}"</p>
        </div>
      </div>
    </Dialog>
  </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { closingApi } from '@/api/finance';
import { unwrapCollection } from '@/utils/api';
import Button from 'primevue/button';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import Card from 'primevue/card';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();
const toast = useToast();
const loading = ref(false);
const saving = ref(false);
const rows = ref([]);
const dateValue = ref(new Date());
const notes = ref('');

const detailVisible = ref(false);
const selectedClosing = ref(null);

const formattedSelectedDate = computed(() => {
  if (!dateValue.value) return '';
  const d = dateValue.value;
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
});

const isSelectedDateClosed = computed(() => {
  return rows.value.some(row => {
    const rowDate = row.closing_date.split('T')[0];
    return rowDate === formattedSelectedDate.value;
  });
});

function showDetailForDate() {
  const closing = rows.value.find(row => {
    const rowDate = row.closing_date.split('T')[0];
    return rowDate === formattedSelectedDate.value;
  });
  if (closing) {
    showDetail(closing);
  }
}

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

function showDetail(closing) {
  selectedClosing.value = closing;
  detailVisible.value = true;
}

async function printDailyReport(id) {
  loading.value = true;
  try {
    const response = await axios.get(`/daily-closings/${id}/report`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Laporan_Closing_Harian_${id}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Laporan harian siap dicetak.', life: 3000 });
  } catch (error) {
    console.error('Error fetching daily report:', error);
    const message = error.response?.data?.message || 'Gagal mengambil laporan harian.';
    toast.add({ severity: 'error', summary: 'Gagal', detail: message, life: 5000 });
  } finally {
    loading.value = false;
  }
}

async function load() {
  loading.value = true;
  try {
    const response = await closingApi.getAll();
    const collection = unwrapCollection(response);
    rows.value = collection.items || [];
  } catch (error) {
    console.error('Failed to load history:', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat riwayat tutup harian.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  try {
    // Convert date to local YYYY-MM-DD
    const d = dateValue.value;
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const formattedDate = `${year}-${month}-${day}`;

    await closingApi.create({
      closing_date: formattedDate,
      notes: notes.value || null
    });
    
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Tutup harian telah diselesaikan.', life: 3000 });
    notes.value = '';
    load();
  } catch (error) {
    const msg = error.response?.data?.message || 'Gagal melakukan tutup harian.';
    toast.add({ severity: 'error', summary: 'Gagal', detail: msg, life: 5000 });
  } finally {
    saving.value = false;
  }
}

load();
</script>

<style scoped>
:deep(.p-card-body) {
  padding: 0;
}
:deep(.p-card-content) {
  padding: 1.5rem;
}
</style>
