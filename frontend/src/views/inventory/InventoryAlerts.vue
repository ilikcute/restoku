<template>
  <AppPage :title="$t('inventory.alerts')" :breadcrumb="[$t('common.inventory'), $t('inventory.alerts')]">
    <div class="space-y-6">
      <!-- Warning Header -->
      <div class="bg-orange-500 rounded-2xl p-8 text-white shadow-lg overflow-hidden relative">
        <div class="relative z-10">
          <h2 class="text-3xl font-bold mb-2">Inventory Overstock Alert</h2>
          <p class="text-orange-100 max-w-2xl">
            Produk-produk berikut telah melebihi ambang batas maksimal stok. Segera tinjau strategi penjualan atau tunda pembelian item ini untuk menjaga cash flow.
          </p>
        </div>
        <div class="absolute right-0 top-0 opacity-10 transform translate-x-10 -translate-y-10 scale-150">
          <i class="pi pi-exclamation-triangle" style="font-size: 15rem;"></i>
        </div>
      </div>

      <!-- Alerts Table -->
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
              <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="pi pi-thumbs-up text-4xl text-green-500"></i>
              </div>
              <p class="text-lg font-medium text-gray-600">Semua Terkendali!</p>
              <p>Tidak ada produk yang stoknya berlebih saat ini.</p>
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

          <Column :header="$t('inventory.maximum')" field="maximum_stock" class="text-center">
            <template #body="{ data }">
              <span class="font-medium text-gray-500">{{ data.maximum_stock }}</span>
            </template>
          </Column>

          <Column :header="$t('inventory.current')" field="current_stock" class="text-center">
            <template #body="{ data }">
              <span class="text-lg font-black text-orange-600">
                {{ data.current_stock }}
              </span>
            </template>
          </Column>

          <Column :header="$t('inventory.excess')" field="excess_quantity" class="text-center">
            <template #body="{ data }">
              <div class="flex flex-col items-center">
                <span class="text-red-600 font-bold">+{{ data.excess_quantity }}</span>
                <span class="text-[10px] text-red-400 font-bold">{{ data.excess_percentage }}% OVER</span>
              </div>
            </template>
          </Column>

          <Column :header="$t('common.status')" field="severity" class="text-center">
            <template #body="{ data }">
              <Tag 
                :value="data.severity === 'critical' ? 'CRITICAL OVER' : 'WARNING'" 
                :severity="data.severity === 'critical' ? 'danger' : 'warn'" 
                rounded
              />
            </template>
          </Column>

          <Column header="" class="w-24">
            <template #body="{ data }">
              <Button icon="pi pi-megaphone" label="Promo" size="small" rounded severity="help" v-tooltip="'Sarankan promo untuk item ini'" />
            </template>
          </Column>
        </DataTable>
      </div>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { inventoryApi } from '@/api/inventory';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';

const rows = ref([]);
const loading = ref(false);

async function load() {
  loading.value = true;
  try {
    const response = await inventoryApi.getAlerts();
    rows.value = response?.data?.data || [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  load();
});
</script>
