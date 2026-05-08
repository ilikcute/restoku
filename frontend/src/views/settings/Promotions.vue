<template>
  <AppPage title="Manajemen Promosi" :breadcrumb="['Pengaturan', 'Promosi']" no-card>
    <div class="space-y-6">
      <!-- Header Section with Glassmorphism Effect -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/60 backdrop-blur-lg p-6 rounded-3xl border border-slate-200/50 shadow-sm">
        <div>
          <h2 class="text-2xl font-black text-slate-800 tracking-tight italic uppercase">Daftar <span class="text-emerald-600">Promosi</span></h2>
          <p class="text-slate-500 text-sm font-medium mt-1">Kelola teks berjalan dan promosi untuk layar konsumen.</p>
        </div>
        <Button label="Tambah Promosi Baru" icon="pi pi-plus" class="!rounded-2xl !px-6 !py-3 !font-bold shadow-lg shadow-emerald-200/50 hover:scale-105 transition-transform" @click="openNew" />
      </div>

      <!-- Main Content Card -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden bg-white">
        <template #content>
          <DataTable :value="items" :loading="loading" dataKey="id" class="p-datatable-modern" responsiveLayout="scroll" :rows="10" paginator>
            <Column field="priority" header="Prio" sortable style="width: 80px">
              <template #body="slotProps">
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-slate-600 text-xs">
                  {{ slotProps.data.priority }}
                </div>
              </template>
            </Column>
            <Column field="title" header="Pesan Promosi" sortable>
              <template #body="slotProps">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800">{{ slotProps.data.title }}</span>
                  <span v-if="slotProps.data.content" class="text-xs text-slate-400 mt-0.5 line-clamp-1 italic">{{ slotProps.data.content }}</span>
                </div>
              </template>
            </Column>
            <Column field="is_active" header="Status" style="width: 120px">
              <template #body="slotProps">
                <div :class="[
                  'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
                  slotProps.data.is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'
                ]">
                  <span :class="['w-1.5 h-1.5 rounded-full', slotProps.data.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                  {{ slotProps.data.is_active ? 'Aktif' : 'Nonaktif' }}
                </div>
              </template>
            </Column>
            <Column header="Masa Berlaku" style="width: 250px">
              <template #body="slotProps">
                <div class="flex items-center gap-2 text-slate-500 text-xs font-medium">
                  <i class="pi pi-calendar text-[10px]"></i>
                  <span v-if="slotProps.data.start_date || slotProps.data.end_date">
                    {{ formatDate(slotProps.data.start_date) }} - {{ formatDate(slotProps.data.end_date) }}
                  </span>
                  <span v-else class="text-slate-300 italic">Selalu Tampil</span>
                </div>
              </template>
            </Column>
            <Column :exportable="false" style="width: 120px" header="Aksi">
              <template #body="slotProps">
                <div class="flex gap-2">
                  <Button icon="pi pi-pencil" text rounded severity="success" class="hover:bg-emerald-50" @click="editItem(slotProps.data)" v-tooltip.top="'Edit'" />
                  <Button icon="pi pi-trash" text rounded severity="danger" class="hover:bg-rose-50" @click="confirmDelete(slotProps.data)" v-tooltip.top="'Hapus'" />
                </div>
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>

      <!-- Enhanced Dialog Structure -->
      <Dialog v-model:visible="itemDialog" :style="{width: '500px'}" :header="item.id ? 'Edit Promosi' : 'Promosi Baru'" :modal="true" class="p-fluid !rounded-3xl overflow-hidden shadow-2xl" pt:root:class="!rounded-3xl" pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100" pt:content:class="!p-6">
        <div class="space-y-6">
          <!-- Form Header Info -->
          <div class="flex items-start gap-4 p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
              <i class="pi pi-megaphone text-xl"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-emerald-900">Informasi Promosi</p>
              <p class="text-[11px] text-emerald-700/70 leading-relaxed font-medium">Pastikan pesan singkat, padat, dan menarik perhatian pelanggan di layar konsumen.</p>
            </div>
          </div>

          <div class="space-y-4">
            <div class="flex flex-col gap-2">
              <label for="title" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Isi Pesan (Running Text)</label>
              <InputText id="title" v-model.trim="item.title" required="true" autofocus :class="{'p-invalid': submitted && !item.title}" placeholder="Contoh: Diskon 10% setiap hari Jumat!" class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500 !p-3" />
              <small class="p-error flex items-center gap-1 ml-1" v-if="submitted && !item.title">
                <i class="pi pi-exclamation-circle text-[10px]"></i> Isi pesan wajib diisi.
              </small>
            </div>

            <div class="flex flex-col gap-2">
              <label for="content" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Keterangan Tambahan (Opsional)</label>
              <Textarea id="content" v-model="item.content" rows="3" class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500 !p-3" placeholder="Detail promo atau catatan internal..." />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="flex flex-col gap-2">
                <label for="priority" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Prioritas Tampil</label>
                <InputNumber id="priority" v-model="item.priority" showButtons :min="0" class="!rounded-xl overflow-hidden !border-slate-100" pt:input:class="!bg-slate-50 !p-3" />
              </div>
              <div class="flex flex-col gap-2 justify-center pt-5">
                <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 transition-colors" :class="{'bg-emerald-50 border-emerald-100': item.is_active}">
                  <Checkbox id="is_active" v-model="item.is_active" :binary="true" />
                  <label for="is_active" class="text-sm font-bold cursor-pointer" :class="item.is_active ? 'text-emerald-700' : 'text-slate-500'">Status Aktif</label>
                </div>
              </div>
            </div>

            <div class="p-4 bg-blue-50 rounded-2xl border border-blue-100 space-y-4">
              <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] flex items-center gap-2">
                <i class="pi pi-calendar-times"></i> Penjadwalan Otomatis
              </p>
              <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                  <label for="start_date" class="text-[10px] font-bold text-blue-400 uppercase ml-1">Mulai Tampil</label>
                  <DatePicker id="start_date" v-model="item.start_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input" class="!rounded-xl overflow-hidden" pt:input:class="!bg-white !border-blue-100 !p-2 !text-xs" />
                </div>
                <div class="flex flex-col gap-2">
                  <label for="end_date" class="text-[10px] font-bold text-blue-400 uppercase ml-1">Selesai Tampil</label>
                  <DatePicker id="end_date" v-model="item.end_date" dateFormat="yy-mm-dd" showIcon iconDisplay="input" class="!rounded-xl overflow-hidden" pt:input:class="!bg-white !border-blue-100 !p-2 !text-xs" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <template #footer>
          <div class="flex gap-3 p-2">
            <Button label="Batal" icon="pi pi-times" text class="!rounded-xl !text-slate-400 hover:!bg-slate-100" @click="hideDialog" />
            <Button label="Simpan Promosi" icon="pi pi-check" :loading="saving" class="!rounded-xl !bg-emerald-600 !border-none !px-8 h-12 font-bold shadow-lg shadow-emerald-100" @click="saveItem" />
          </div>
        </template>
      </Dialog>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { promotionApi } from '@/api/master';
import { useToast } from 'primevue/usetoast';
import AppPage from '@/components/layout/AppPage.vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';

const toast = useToast();
const items = ref([]);
const loading = ref(false);
const itemDialog = ref(false);
const item = ref({});
const submitted = ref(false);
const saving = ref(false);

async function load() {
  loading.value = true;
  try {
    const response = await promotionApi.getAll({ active_only: 0 });
    items.value = response?.data?.data || [];
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal mengambil data promosi', life: 3000 });
  } finally {
    loading.value = false;
  }
}

function openNew() {
  item.value = { is_active: true, priority: 0 };
  submitted.value = false;
  itemDialog.value = true;
}

function hideDialog() {
  itemDialog.value = false;
  submitted.value = false;
}

function editItem(data) {
  item.value = { ...data };
  // Convert date strings to Date objects for DatePicker
  if (item.value.start_date) item.value.start_date = new Date(item.value.start_date);
  if (item.value.end_date) item.value.end_date = new Date(item.value.end_date);
  itemDialog.value = true;
}

async function saveItem() {
  submitted.value = true;
  if (!item.value.title) return;

  saving.value = true;
  try {
    const payload = { ...item.value };
    if (payload.start_date instanceof Date) {
        const offset = payload.start_date.getTimezoneOffset();
        payload.start_date = new Date(payload.start_date.getTime() - (offset*60*1000)).toISOString().split('T')[0];
    }
    if (payload.end_date instanceof Date) {
        const offset = payload.end_date.getTimezoneOffset();
        payload.end_date = new Date(payload.end_date.getTime() - (offset*60*1000)).toISOString().split('T')[0];
    }

    if (item.value.id) {
      await promotionApi.update(item.value.id, payload);
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Promosi diperbarui', life: 3000 });
    } else {
      await promotionApi.create(payload);
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Promosi ditambahkan', life: 3000 });
    }
    itemDialog.value = false;
    load();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal menyimpan data', life: 3000 });
  } finally {
    saving.value = false;
  }
}

async function confirmDelete(data) {
  if (confirm('Apakah Anda yakin ingin menghapus promosi ini?')) {
    try {
      await promotionApi.delete(data.id);
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Promosi dihapus', life: 3000 });
      load();
    } catch (error) {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal menghapus data', life: 3000 });
    }
  }
}

function formatDate(date) {
  if (!date) return '...';
  const d = new Date(date);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

onMounted(() => {
  load();
});
</script>

<style scoped>
@reference "@/style.css";

.p-datatable-modern :deep(.p-datatable-thead > tr > th) {
  @apply bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] border-none py-4;
}

.p-datatable-modern :deep(.p-datatable-tbody > tr) {
  @apply transition-colors duration-200 border-b border-slate-50;
}

.p-datatable-modern :deep(.p-datatable-tbody > tr:hover) {
  @apply bg-slate-50/50;
}

.p-datatable-modern :deep(.p-datatable-tbody > tr > td) {
  @apply py-4 border-none;
}
</style>
