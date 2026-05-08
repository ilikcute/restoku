<template>
  <AppPage title="Audit Trail (Log Aktivitas)" :breadcrumb="['Laporan', 'Audit Trail']">
    <div class="space-y-6 mt-4">
      
      <!-- Table -->
      <Card class="border-none shadow-sm overflow-hidden">
        <template #title>
          <div class="flex justify-between items-center px-2">
            <div class="flex flex-col">
              <span class="text-slate-800 font-black tracking-tight text-xl">Log Aktivitas Sistem</span>
              <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Memantau setiap perubahan data penting</span>
            </div>
            <Button icon="pi pi-refresh" outlined rounded @click="loadActivities" :loading="loading" />
          </div>
        </template>
        <template #content>
          <DataTable 
            :value="activities" 
            class="p-datatable-sm" 
            stripedRows 
            responsiveLayout="scroll" 
            :loading="loading"
            lazy
            :paginator="true"
            :rows="rows"
            :totalRecords="totalRecords"
            @page="onPage($event)"
          >
            <template #empty>
              <div class="text-center py-12">
                <i class="pi pi-history text-4xl text-slate-200 mb-3 block"></i>
                <div class="text-slate-400">Belum ada rekaman aktivitas.</div>
              </div>
            </template>
            
            <Column field="created_at" header="Waktu" class="w-48">
              <template #body="{ data }">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-700">{{ formatDateTime(data.created_at) }}</span>
                  <span class="text-[10px] text-slate-400 font-bold uppercase">{{ timeAgo(data.created_at) }}</span>
                </div>
              </template>
            </Column>

            <Column field="causer" header="Pelaku" class="w-48">
              <template #body="{ data }">
                <div class="flex items-center gap-2" v-if="data.causer">
                  <Avatar :image="data.causer.avatar_url" shape="circle" size="small" />
                  <div class="flex flex-col">
                    <span class="text-sm font-bold text-slate-700">{{ data.causer.name }}</span>
                    <span class="text-[10px] text-slate-400 uppercase font-bold">{{ data.causer.role }}</span>
                  </div>
                </div>
                <span v-else class="text-slate-400 italic">System</span>
              </template>
            </Column>

            <Column field="description" header="Aktivitas">
              <template #body="{ data }">
                <div class="flex flex-col">
                  <div class="flex items-center gap-2">
                    <Tag :value="translateEvent(data.description)" :severity="getEventSeverity(data.description)" />
                    <span class="font-medium text-slate-600">
                      {{ formatSubjectType(data.subject_type) }} 
                      <span class="text-slate-400 font-normal">#{{ data.subject_id }}</span>
                    </span>
                  </div>
                </div>
              </template>
            </Column>

            <Column header="Detail" class="w-20 text-center">
              <template #body="{ data }">
                <Button icon="pi pi-eye" text rounded @click="showDetail(data)" />
              </template>
            </Column>
          </DataTable>
        </template>
      </Card>

      <!-- Detail Dialog -->
      <Dialog v-model:visible="displayDetail" header="Detail Aktivitas" :modal="true" :style="{ width: '50vw' }" class="p-fluid">
        <div v-if="selectedActivity" class="space-y-4">
          <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl">
            <div class="flex flex-col">
              <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Waktu</span>
              <span class="font-bold">{{ formatDateTime(selectedActivity.created_at) }}</span>
            </div>
            <div class="flex flex-col">
              <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Aktivitas</span>
              <div>
                 <Tag :value="translateEvent(selectedActivity.description)" :severity="getEventSeverity(selectedActivity.description)" />
              </div>
            </div>
            <div class="flex flex-col">
              <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Pelaku</span>
              <span class="font-bold">{{ selectedActivity.causer?.name || 'System' }}</span>
            </div>
            <div class="flex flex-col">
              <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Objek</span>
              <span class="font-bold">{{ formatSubjectType(selectedActivity.subject_type) }} (#{{ selectedActivity.subject_id }})</span>
            </div>
          </div>

          <div v-if="hasChanges" class="space-y-2">
            <span class="text-xs font-bold text-slate-500 uppercase">Perubahan Data</span>
            <div class="border rounded-xl overflow-hidden">
               <table class="w-full text-sm">
                 <thead class="bg-slate-100 text-slate-600 font-bold">
                   <tr>
                     <th class="px-4 py-2 text-left">Field</th>
                     <th class="px-4 py-2 text-left">Sebelum</th>
                     <th class="px-4 py-2 text-left">Sesudah</th>
                   </tr>
                 </thead>
                 <tbody class="divide-y">
                   <tr v-for="(val, field) in changes.attributes" :key="field">
                     <td class="px-4 py-2 font-mono text-xs text-slate-500">{{ field }}</td>
                     <td class="px-4 py-2 text-red-500 line-through">{{ changes.old ? changes.old[field] : '-' }}</td>
                     <td class="px-4 py-2 text-emerald-600 font-bold">{{ val }}</td>
                   </tr>
                 </tbody>
               </table>
            </div>
          </div>
          <div v-else class="text-center py-4 text-slate-400 italic text-sm">
            Tidak ada perubahan data yang dicatat (Mungkin hapus atau baru dibuat tanpa log field).
          </div>
        </div>
      </Dialog>

    </div>
  </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import { ref, onMounted, computed } from 'vue';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import Avatar from 'primevue/avatar';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const loading = ref(false);
const activities = ref([]);
const totalRecords = ref(0);
const rows = ref(20);
const page = ref(1);

const displayDetail = ref(false);
const selectedActivity = ref(null);

const changes = computed(() => {
  return selectedActivity.value?.properties || {};
});

const hasChanges = computed(() => {
  return changes.value.attributes && Object.keys(changes.value.attributes).length > 0;
});

function formatDateTime(str) {
  return new Date(str).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
}

function timeAgo(str) {
  const date = new Date(str);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000);
  
  if (diff < 60) return 'Baru saja';
  if (diff < 3600) return `${Math.floor(diff / 60)}m lalu`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}j lalu`;
  return `${Math.floor(diff / 86400)}h lalu`;
}

function translateEvent(event) {
  const map = {
    'created': 'TAMBAH',
    'updated': 'UBAH',
    'deleted': 'HAPUS',
    'restored': 'PULIHKAN'
  };
  return map[event] || event.toUpperCase();
}

function getEventSeverity(event) {
  const map = {
    'created': 'success',
    'updated': 'info',
    'deleted': 'danger',
    'restored': 'warn'
  };
  return map[event] || 'secondary';
}

function formatSubjectType(type) {
  if (!type) return '-';
  const parts = type.split('\\');
  return parts[parts.length - 1];
}

async function loadActivities() {
  loading.value = true;
  try {
    const response = await axios.get('/reports/audit-logs', {
      params: { page: page.value, per_page: rows.value }
    });
    activities.value = response.data.data.data;
    totalRecords.value = response.data.data.total;
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat log aktivitas.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

function onPage(event) {
  page.value = event.page + 1;
  rows.value = event.rows;
  loadActivities();
}

function showDetail(activity) {
  selectedActivity.value = activity;
  displayDetail.value = true;
}

onMounted(() => {
  loadActivities();
});
</script>
