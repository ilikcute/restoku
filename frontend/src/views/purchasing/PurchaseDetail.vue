<template>
  <AppPage :title="$t('sidebar.purchase_detail')" :breadcrumb="[$t('common.purchasing'), $t('sidebar.purchases'), selected?.purchase_number]">
    <template #actions>
      <Button 
        v-if="selected"
        icon="pi pi-download" 
        label="Unduh Faktur" 
        @click="downloadPdf" 
        severity="secondary"
        outlined
      />
    </template>

    <div v-if="selected" class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-4">
      <!-- Purchase Info -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
          <div class="flex justify-between items-start mb-6">
            <div>
              <p class="text-xs text-slate-500 uppercase font-bold tracking-widest mb-1">Purchase ID</p>
              <h2 class="text-2xl font-black text-slate-900">{{ selected.purchase_number }}</h2>
            </div>
            <div class="text-right">
              <Tag :value="selected.status" severity="success" class="uppercase" />
            </div>
          </div>

          <DataTable :value="selected.items || []" class="p-datatable-sm" stripedRows>
            <template #empty>Tidak ada item.</template>
            <Column :header="$t('sidebar.products')">
              <template #body="{ data }">
                <div class="font-bold text-slate-900">{{ data.product_name }}</div>
              </template>
            </Column>
            <Column field="cost_price" :header="$t('product.cost_price')" class="text-right">
              <template #body="{ data }">Rp {{ money(data.cost_price) }}</template>
            </Column>
            <Column :header="$t('inventory.qty')" class="text-center">
              <template #body="{ data }">
                <div class="flex flex-col items-center">
                  <span :class="{ 'line-through text-slate-400': data.return_quantity > 0 }">{{ data.quantity }}</span>
                  <span v-if="data.return_quantity > 0" class="text-xs font-bold text-rose-600">Retur: {{ data.return_quantity }}</span>
                  <span v-if="data.return_quantity > 0" class="text-xs font-black text-emerald-600 mt-0.5">
                    Akhir: {{ data.quantity - data.return_quantity }}
                  </span>
                </div>
              </template>
            </Column>
            <Column field="subtotal" header="Subtotal" class="text-right">
              <template #body="{ data }">
                <div class="flex flex-col items-end">
                  <span :class="{ 'line-through text-slate-400': data.return_amount > 0 }">Rp {{ money(data.subtotal) }}</span>
                  <span v-if="data.return_amount > 0" class="text-xs font-bold text-rose-600">-Rp {{ money(data.return_amount) }}</span>
                </div>
              </template>
            </Column>
          </DataTable>
          
          <div v-if="selected.notes" class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-100">
            <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Catatan</p>
            <p class="text-sm italic text-slate-700">{{ selected.notes }}</p>
          </div>
        </div>

      </div>

      <!-- Summary & Supplier Info -->
      <div class="space-y-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
          <h3 class="font-bold text-slate-900 mb-4 border-b pb-2 uppercase text-xs tracking-wider">Ringkasan Pembayaran</h3>
          
          <div class="space-y-3">
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Subtotal</span>
              <span class="font-bold">Rp {{ money(selected.subtotal) }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-slate-500">Pajak</span>
              <span class="font-bold">Rp {{ money(selected.tax_amount) }}</span>
            </div>

            <div v-if="selected.total_return > 0" class="flex justify-between text-sm text-rose-600 pt-2 border-t border-slate-50 mt-2">
              <span class="font-bold">Total Retur</span>
              <span class="font-black">-Rp {{ money(selected.total_return) }}</span>
            </div>
            
            <div class="pt-3 border-t border-dashed">
              <div class="flex justify-between text-xl font-black text-emerald-600">
                <span>TOTAL AKHIR</span>
                <span>Rp {{ money(selected.total_amount - (selected.total_return || 0)) }}</span>
              </div>
            </div>

            <div class="pt-4 space-y-2 text-sm border-t border-slate-50 mt-4">
              <div class="flex justify-between">
                <span class="text-slate-500">Status Bayar</span>
                <Tag value="PAID" severity="success" size="small" />
              </div>
            </div>
          </div>
        </div>

        <div class="bg-slate-900 rounded-2xl p-6 text-white shadow-lg">
          <h3 class="font-bold mb-4 border-b border-slate-800 pb-2 uppercase text-[10px] tracking-widest text-slate-500">Informasi Supplier</h3>
          <div class="space-y-4">
            <div>
              <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Nama Pemasok</p>
              <p class="font-bold">{{ selected.supplier?.name || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Kontak Person</p>
              <p class="font-bold text-sm">{{ selected.supplier?.contact_person || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Petugas Penerima</p>
              <p class="font-bold">{{ selected.user?.name || '-' }}</p>
            </div>
            <div>
              <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Waktu Transaksi</p>
              <p class="font-bold text-sm">{{ formatDate(selected.purchase_date) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const { t: $t } = useI18n();
const toast = useToast();
const route = useRoute();
const selected = ref(null);
const loading = ref(false);

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
}

async function load() {
  loading.value = true;
  try {
    const response = await axios.get(`/purchases/${route.params.id}`);
    selected.value = response.data.data;
  } catch (error) {
    console.error('Failed to load purchase', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat data pembelian.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

async function downloadPdf() {
  if (!selected.value) return;
  
  try {
    const response = await axios.get(`/purchases/${selected.value.id}/pdf`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Purchase_${selected.value.purchase_number}.pdf`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (error) {
    console.error('Download failed', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengunduh PDF.', life: 3000 });
  }
}

onMounted(() => {
  load();
});
</script>
