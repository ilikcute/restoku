<template>
  <AppPage :title="$t('sales.shift_manager')" :breadcrumb="[$t('common.sales'), $t('sales.shift_manager')]">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
      
      <!-- Current Shift Status -->
      <div class="lg:col-span-1 space-y-6">
        <Card class="shift-manager-card border-none shadow-sm overflow-hidden">
          <template #header>
            <div :class="shiftStore.activeShift ? 'bg-emerald-600' : 'bg-slate-700'" class="p-4 flex justify-between items-center transition-colors duration-500">
              <h3 class="text-white font-bold flex items-center gap-2">
                <i class="pi pi-clock animate-pulse"></i>
                {{ shiftStore.activeShift ? 'Shift Aktif' : 'Shift Belum Dibuka' }}
              </h3>
              <Tag v-if="shiftStore.activeShift" value="OPEN" severity="success" class="bg-white/20 border-none text-white px-3" />
            </div>
          </template>
          <template #content>
            <div v-if="shiftStore.activeShift" class="space-y-6">
              <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <Avatar icon="pi pi-user" shape="circle" size="large" class="bg-primary-100 text-primary-600" />
                <div class="flex flex-col">
                  <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Kasir Bertugas</p>
                  <p class="font-bold text-slate-900">{{ shiftStore.activeShift.user?.name || 'User' }}</p>
                  <p class="text-[10px] text-slate-400">{{ formatDate(shiftStore.activeShift.start_time) }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-2 gap-4">
                <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                  <p class="text-[10px] text-indigo-600 uppercase font-bold">Modal Awal</p>
                  <p class="text-sm font-black text-indigo-900">Rp {{ money(shiftStore.activeShift.starting_cash) }}</p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                  <p class="text-[10px] text-emerald-600 uppercase font-bold">Total Sales</p>
                  <p class="text-sm font-black text-emerald-700">Rp {{ money(shiftStore.activeShift.total_sales) }}</p>
                </div>
              </div>

              <div class="pt-4 border-t border-dashed border-slate-200">
                <h4 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                  <i class="pi pi-lock-open text-rose-500"></i>
                  Tutup Shift Sekarang
                </h4>
                <div class="space-y-4">
                  <div class="field">
                    <label class="text-xs font-bold text-slate-500 block mb-2 uppercase tracking-wide">Total Kas Fisik (Tunai di Laci)</label>
                    <InputNumber 
                      v-model="closeForm.ending_cash" 
                      mode="currency" 
                      currency="IDR" 
                      locale="id-ID" 
                      class="shift-manager-input w-full"
                      inputClass="text-2xl font-black text-slate-900 h-14 text-center bg-slate-50 border-slate-200 focus:border-primary-500"
                      placeholder="Rp 0"
                    />
                  </div>
                  <div class="field">
                    <label class="text-xs font-bold text-slate-500 block mb-2 uppercase tracking-wide">Catatan Penutupan</label>
                    <Textarea v-model="closeForm.notes" rows="2" class="w-full bg-slate-50" placeholder="Opsional: Alasan jika ada selisih kas..." />
                  </div>
                  <Button 
                    label="PROSES TUTUP SHIFT" 
                    icon="pi pi-check-circle" 
                    severity="danger" 
                    class="w-full h-14 text-lg font-black tracking-widest shadow-lg shadow-rose-100 rounded-xl" 
                    :loading="loading" 
                    @click="confirmClose" 
                  />
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 space-y-6">
              <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 ring-8 ring-slate-50">
                <i class="pi pi-key text-5xl"></i>
              </div>
              <div class="px-4">
                <h3 class="font-black text-slate-800 text-xl mb-2">Buka Shift Baru</h3>
                <p class="text-slate-500 text-sm">Silakan masukkan jumlah modal awal tunai yang ada di laci kasir untuk memulai transaksi.</p>
              </div>
              <div class="field text-left px-4">
                <label class="text-xs font-bold text-slate-500 block mb-2 uppercase">Modal Awal Kasir (IDR)</label>
                <InputNumber 
                  v-model="startingCash" 
                  mode="currency" 
                  currency="IDR" 
                  locale="id-ID" 
                  class="shift-manager-input w-full"
                  inputClass="text-3xl font-black text-center h-16 bg-slate-50 rounded-xl"
                  autofocus
                />
              </div>
              <div class="px-4 pb-4">
                <Button 
                  label="BUKA SHIFT SEKARANG" 
                  icon="pi pi-play" 
                  class="w-full h-16 text-xl font-black tracking-wider shadow-xl shadow-emerald-100 rounded-2xl" 
                  :loading="loading" 
                  @click="openShift" 
                />
              </div>
            </div>
          </template>
        </Card>
      </div>

      <!-- Real-time Stats & History -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Active Stats Cards -->
        <div v-if="shiftStore.activeShift" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
          <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600">
              <i class="pi pi-wallet text-xl"></i>
            </div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">Tunai</p>
              <p class="font-black text-slate-800">Rp {{ money(shiftStore.activeShift.cash_sales) }}</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center text-amber-600">
              <i class="pi pi-credit-card text-xl"></i>
            </div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">Non-Tunai</p>
              <p class="font-black text-slate-800">Rp {{ money(shiftStore.activeShift.non_cash_sales) }}</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
              <i class="pi pi-plus-circle text-xl"></i>
            </div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">Lainnya (+)</p>
              <p class="font-black text-emerald-600">Rp {{ money(shiftStore.activeShift.total_income) }}</p>
            </div>
          </div>
          <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-600">
              <i class="pi pi-minus-circle text-xl"></i>
            </div>
            <div>
              <p class="text-[10px] text-slate-400 font-bold uppercase">Biaya (-)</p>
              <p class="font-black text-rose-600">Rp {{ money(shiftStore.activeShift.total_expense) }}</p>
            </div>
          </div>
        </div>

        <!-- History Table -->
        <Card class="shift-manager-card border-none shadow-sm h-full">
          <template #header>
            <div class="p-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
              <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <i class="pi pi-history text-primary-500"></i>
                Riwayat Shift Kasir
              </h3>
              <Button icon="pi pi-refresh" text rounded @click="loadHistory" :loading="loadingHistory" />
            </div>
          </template>
          <template #content>
            <AppDataTable 
              :value="history" 
              :loading="loadingHistory" 
              paginator 
              :rows="10" 
              class="app-table"
              responsiveLayout="scroll"
              stripedRows
            >
              <Column field="start_time" header="Waktu Shift">
                <template #body="{ data }">
                  <div class="flex flex-col">
                    <span class="text-xs font-bold text-slate-700">{{ formatDate(data.start_time) }}</span>
                    <span v-if="data.end_time" class="text-[10px] text-slate-400">Durasi: {{ calculateDuration(data.start_time, data.end_time) }}</span>
                    <Tag v-else value="ACTIVE" severity="success" class="w-fit text-[9px] py-0 px-1 mt-1" />
                  </div>
                </template>
              </Column>
              <Column field="user.name" header="Kasir">
                <template #body="{ data }">
                  <div class="flex items-center gap-2">
                    <Avatar icon="pi pi-user" shape="circle" class="bg-slate-100 text-slate-500" size="small" />
                    <span class="text-xs font-semibold">{{ data.user?.name || '-' }}</span>
                  </div>
                </template>
              </Column>
              <Column header="Total Sales" class="text-right">
                <template #body="{ data }">
                  <div class="flex flex-col text-right">
                    <span class="text-xs font-black text-slate-800">Rp {{ money(data.total_sales) }}</span>
                    <span class="text-[10px] text-slate-400">T: {{ money(data.cash_sales) }} | NT: {{ money(data.non_cash_sales) }}</span>
                  </div>
                </template>
              </Column>
              <Column header="Selisih" class="text-right w-24">
                <template #body="{ data }">
                  <span v-if="data.status === 'closed'" :class="data.difference < 0 ? 'text-rose-600' : (data.difference > 0 ? 'text-amber-600' : 'text-emerald-600')" class="text-xs font-black">
                    {{ data.difference > 0 ? '+' : '' }}Rp {{ money(data.difference) }}
                  </span>
                  <span v-else class="text-slate-300">-</span>
                </template>
              </Column>
              <Column field="status" header="Status" class="text-center w-24">
                <template #body="{ data }">
                  <Tag :value="data.status.toUpperCase()" :severity="data.status === 'open' ? 'success' : 'secondary'" class="text-[10px]" />
                </template>
              </Column>
              <Column header="Aksi" class="text-center w-24">
                <template #body="{ data }">
                  <div class="flex justify-center gap-1">
                    <Button icon="pi pi-info-circle" severity="info" text rounded size="small" @click="showDetail(data)" />
                    <Button icon="pi pi-print" text rounded severity="secondary" size="small" @click="printShiftReport(data.id)" />
                  </div>
                </template>
              </Column>
            </AppDataTable>
          </template>
        </Card>
      </div>
    </div>

    <!-- Detail Modal -->
    <Dialog v-model:visible="detailVisible" modal header="Detail Laporan Shift" :style="{ width: '32rem' }">
      <div v-if="selectedShift" class="space-y-6 py-4">
        <div class="bg-slate-800 rounded-2xl p-6 text-white relative overflow-hidden shadow-xl">
          <div class="relative z-10">
            <div class="flex justify-between items-start mb-6">
              <div>
                <p class="text-[10px] opacity-60 font-bold uppercase tracking-widest mb-1">Status Shift</p>
                <Tag :value="selectedShift.status.toUpperCase()" :severity="selectedShift.status === 'open' ? 'success' : 'secondary'" />
              </div>
              <div class="text-right">
                <p class="text-[10px] opacity-60 font-bold uppercase tracking-widest mb-1">Total Sales</p>
                <p class="text-2xl font-black">Rp {{ money(selectedShift.total_sales) }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 border-t border-white/10 pt-4">
              <div>
                <p class="text-[10px] opacity-60 uppercase font-bold mb-1">Mulai</p>
                <p class="text-xs">{{ formatDate(selectedShift.start_time) }}</p>
              </div>
              <div class="text-right">
                <p class="text-[10px] opacity-60 uppercase font-bold mb-1">Selesai</p>
                <p class="text-xs">{{ selectedShift.end_time ? formatDate(selectedShift.end_time) : '-' }}</p>
              </div>
            </div>
          </div>
          <!-- Decorative element -->
          <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-2xl"></div>
        </div>

        <div class="space-y-2">
          <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest px-2 mb-2">Rekonsiliasi Kas</h4>
          <div class="bg-white rounded-2xl border border-slate-100 overflow-hidden shadow-sm">
            <div class="p-3 flex justify-between items-center border-b border-slate-50">
              <span class="text-sm text-slate-600">Modal Awal (Starting Cash)</span>
              <span class="font-bold text-slate-800">Rp {{ money(selectedShift.starting_cash) }}</span>
            </div>
            <div class="p-3 flex justify-between items-center border-b border-slate-50 bg-emerald-50/30">
              <span class="text-sm text-emerald-700">Penjualan Tunai (+)</span>
              <span class="font-bold text-emerald-700">+ Rp {{ money(selectedShift.cash_sales) }}</span>
            </div>
            <div class="p-3 flex justify-between items-center border-b border-slate-50 bg-emerald-50/10">
              <span class="text-sm text-emerald-600 font-medium">Pemasukan Lain (+)</span>
              <span class="font-bold text-emerald-600">+ Rp {{ money(selectedShift.total_income) }}</span>
            </div>
            <div class="p-3 flex justify-between items-center border-b border-slate-50 bg-rose-50/10">
              <span class="text-sm text-rose-600 font-medium">Pengeluaran/Biaya (-)</span>
              <span class="font-bold text-rose-600">- Rp {{ money(selectedShift.total_expense) }}</span>
            </div>
            <div class="p-4 flex justify-between items-center bg-slate-50">
              <span class="font-bold text-slate-700">Total Kas Seharusnya</span>
              <span class="text-lg font-black text-slate-900">Rp {{ money(selectedShift.total_expected) }}</span>
            </div>
            <div class="p-4 flex justify-between items-center bg-white border-t border-slate-100">
              <span class="font-bold text-slate-700">Kas Fisik dilaporkan</span>
              <span class="text-lg font-black text-indigo-600">Rp {{ money(selectedShift.ending_cash) }}</span>
            </div>
          </div>
        </div>

        <div v-if="selectedShift.status === 'closed'" class="p-4 rounded-2xl border-2 border-dashed flex justify-between items-center" :class="selectedShift.difference < 0 ? 'bg-rose-50 border-rose-200' : (selectedShift.difference > 0 ? 'bg-amber-50 border-amber-200' : 'bg-emerald-50 border-emerald-200')">
          <div>
            <p class="text-xs font-bold" :class="selectedShift.difference < 0 ? 'text-rose-700' : (selectedShift.difference > 0 ? 'text-amber-700' : 'text-emerald-700')">SELISIH KAS</p>
            <p class="text-sm opacity-70">{{ selectedShift.difference < 0 ? 'Terdapat kekurangan kas fisik' : (selectedShift.difference > 0 ? 'Terdapat kelebihan kas fisik' : 'Kas fisik sesuai (Balance)') }}</p>
          </div>
          <span class="text-xl font-black" :class="selectedShift.difference < 0 ? 'text-rose-700' : (selectedShift.difference > 0 ? 'text-amber-700' : 'text-emerald-700')">
            {{ selectedShift.difference > 0 ? '+' : '' }}Rp {{ money(selectedShift.difference) }}
          </span>
        </div>

        <div v-if="selectedShift.notes" class="p-4 bg-slate-50 rounded-xl border border-slate-100">
          <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Catatan Kasir</p>
          <p class="text-xs text-slate-700 italic">"{{ selectedShift.notes }}"</p>
        </div>
      </div>
    </Dialog>
  </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { reactive, ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { useShiftStore } from '@/stores/shift';
import AppPage from '@/components/layout/AppPage.vue';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import Dialog from 'primevue/dialog';

const { t: $t } = useI18n();
const toast = useToast();
const confirm = useConfirm();
const shiftStore = useShiftStore();

const loading = ref(false);
const loadingHistory = ref(false);
const history = ref([]);
const startingCash = ref(0);
const closeForm = reactive({ ending_cash: 0, notes: '' });

const detailVisible = ref(false);
const selectedShift = ref(null);

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function calculateDuration(start, end) {
  if (!start || !end) return '-';
  const diff = new Date(end) - new Date(start);
  const hours = Math.floor(diff / 3600000);
  const minutes = Math.floor((diff % 3600000) / 60000);
  return `${hours}j ${minutes}m`;
}

async function loadHistory() {
  loadingHistory.value = true;
  try {
    const collection = await shiftStore.fetchShifts();
    history.value = collection?.data || [];
  } catch (error) {
    console.error('Failed to load shift history:', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat riwayat shift.', life: 3000 });
  } finally {
    loadingHistory.value = false;
  }
}

function showDetail(shift) {
  selectedShift.value = shift;
  detailVisible.value = true;
}

async function openShift() {
  if (startingCash.value < 0) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Modal awal tidak boleh negatif', life: 3000 });
    return;
  }
  loading.value = true;
  try {
    await shiftStore.openShift(startingCash.value || 0);
    toast.add({ severity: 'success', summary: 'Shift Dibuka', detail: 'Selamat bertugas!', life: 3000 });
    startingCash.value = 0;
    loadHistory();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error?.response?.data?.message, life: 3000 });
  } finally {
    loading.value = false;
  }
}

function confirmClose() {
  if (closeForm.ending_cash < 0) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Total kas fisik tidak boleh negatif', life: 3000 });
    return;
  }
  confirm.require({
    message: 'Apakah Anda yakin ingin menutup shift? Pastikan kas fisik sudah dihitung dengan benar.',
    header: 'Konfirmasi Tutup Shift',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger rounded-xl',
    accept: () => closeShift()
  });
}

async function closeShift() {
  loading.value = true;
  try {
    await shiftStore.closeShift(closeForm);
    toast.add({ severity: 'success', summary: 'Shift Ditutup', detail: 'Laporan shift telah disimpan.', life: 3000 });
    closeForm.ending_cash = 0;
    closeForm.notes = '';
    loadHistory();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: error?.response?.data?.message, life: 3000 });
  } finally {
    loading.value = false;
  }
}

async function printShiftReport(id) {
  loading.value = true;
  try {
    const response = await axios.get(`/shifts/${id}/report`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Laporan_Shift_${id}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    
    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Laporan shift siap dicetak.', life: 3000 });
  } catch (error) {
    console.error('Error fetching shift report:', error);
    const message = error.response?.data?.message || 'Gagal mengambil laporan shift.';
    toast.add({ severity: 'error', summary: 'Gagal', detail: message, life: 5000 });
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await shiftStore.fetchCurrentShift();
  loadHistory();
});
</script>

<style>
/* Global styles for ShiftManager to avoid build issues with :deep() */
.shift-manager-card .p-card-body {
  padding: 0 !important;
}
.shift-manager-card .p-card-content {
  padding: 1.5rem !important;
}
.shift-manager-input .p-inputnumber-input {
  text-align: center !important;
}
</style>
