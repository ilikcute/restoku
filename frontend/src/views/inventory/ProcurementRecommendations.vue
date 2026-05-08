<template>
  <AppPage :title="$t('inventory.recommendations')" :breadcrumb="[$t('common.purchasing'), $t('sidebar.procurement')]">
    <div class="space-y-6">
      <!-- Info Header -->
      <div class="bg-primary-600 rounded-2xl p-8 text-white shadow-lg overflow-hidden relative">
        <div class="relative z-10">
          <h2 class="text-3xl font-bold mb-2">Smart Procurement</h2>
          <p class="text-primary-100 max-w-2xl">
            Sistem menganalisis rata-rata penjualan harian, waktu tunggu supplier (Lead Time), dan stok pengaman (Safety Stock) untuk memberikan rekomendasi pesanan yang akurat.
          </p>
          <div class="mt-6 flex flex-wrap gap-4">
            <div class="bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl border border-white/30">
              <span class="text-xs font-medium uppercase tracking-wider block opacity-70">Sales Period</span>
              <span class="text-lg font-bold">{{ days }} Hari Terakhir</span>
            </div>
            <div class="bg-white/20 backdrop-blur-md px-4 py-2 rounded-xl border border-white/30">
              <span class="text-xs font-medium uppercase tracking-wider block opacity-70">Item Rekomendasi</span>
              <span class="text-lg font-bold">{{ rows.length }} Produk</span>
            </div>
          </div>
        </div>
        <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-10 translate-y-10 scale-150">
          <i class="pi pi-shopping-cart" style="font-size: 15rem;"></i>
        </div>
      </div>

      <!-- Controls -->
      <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="text-sm font-medium text-gray-500">{{ $t('inventory.days_of_sales') }}:</span>
          <SelectButton v-model="days" :options="dayOptions" optionLabel="label" optionValue="value" class="custom-select-button" />
        </div>
        <Button 
          icon="pi pi-refresh" 
          severity="secondary" 
          outlined 
          @click="load" 
          :loading="loading" 
          v-tooltip="'Recalculate'"
        />
      </div>

      <!-- Recommendations Table -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <DataTable 
          :value="rows" 
          :loading="loading" 
          paginator 
          :rows="10"
          responsiveLayout="stack" 
          breakpoint="960px"
          stripedRows
        >
          <template #empty>
            <div class="text-center py-12 text-gray-400">
              <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-check-circle text-4xl text-green-500"></i>
              </div>
              <p class="text-lg font-medium text-gray-600">Stok Anda dalam kondisi prima!</p>
              <p>Tidak ada item yang perlu dipesan kembali saat ini.</p>
            </div>
          </template>

          <Column :header="$t('common.name')" field="name" sortable class="font-bold text-gray-800" />
          
          <Column :header="$t('common.category')" field="category" sortable>
            <template #body="{ data }">
              <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs font-bold uppercase">
                {{ data.category }}
              </span>
            </template>
          </Column>

          <Column header="Daily Avg" field="daily_avg_sales" class="text-center">
            <template #body="{ data }">
              <span class="font-medium">{{ data.daily_avg_sales }}</span>
              <span class="text-xs text-gray-400 ml-1">{{ data.unit }}</span>
            </template>
          </Column>

          <Column :header="$t('inventory.current')" field="current_stock" class="text-center">
            <template #body="{ data }">
              <span :class="data.current_stock <= data.safety_stock ? 'text-red-600 font-bold' : 'text-orange-600 font-bold'">
                {{ data.current_stock }}
              </span>
            </template>
          </Column>

          <Column :header="$t('inventory.rop')" field="calculated_rop" class="text-center">
             <template #body="{ data }">
              <div class="flex flex-col items-center">
                <span class="font-bold text-gray-700">{{ data.calculated_rop }}</span>
                <span class="text-[10px] text-gray-400 uppercase tracking-tighter">Point to Reorder</span>
              </div>
            </template>
          </Column>

          <Column :header="$t('inventory.suggested_order')" field="reorder_quantity" class="text-center">
            <template #body="{ data }">
              <div class="px-3 py-1 bg-primary-50 text-primary-700 rounded-lg font-black inline-block border border-primary-100">
                +{{ data.reorder_quantity }}
              </div>
            </template>
          </Column>

          <Column :header="$t('inventory.priority')" field="priority" class="text-center">
            <template #body="{ data }">
              <Tag 
                :value="data.priority.toUpperCase()" 
                :severity="data.priority === 'high' ? 'danger' : 'warn'" 
                rounded
              />
            </template>
          </Column>

          <Column header="" class="w-24">
            <template #body="{ data }">
              <Button icon="pi pi-shopping-cart" size="small" rounded severity="primary" v-tooltip="'Buat Purchase Order'" />
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { inventoryApi } from '@/api/inventory';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import SelectButton from 'primevue/selectbutton';

const rows = ref([]);
const loading = ref(false);
const days = ref(30);

const dayOptions = [
  { label: '7 Hari', value: 7 },
  { label: '15 Hari', value: 15 },
  { label: '30 Hari', value: 30 }
];

async function load() {
  loading.value = true;
  try {
    const response = await inventoryApi.getRecommendations({ days: days.value });
    rows.value = response?.data?.data || [];
  } catch (error) {
    console.error('Failed to load recommendations', error);
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal memuat rekomendasi pengadaan.', life: 3000 });
  } finally {
    loading.value = false;
  }
}

watch(days, () => {
  load();
});

onMounted(() => {
  load();
});
</script>

<style scoped>
.custom-select-button :deep(.p-button) {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}
</style>
