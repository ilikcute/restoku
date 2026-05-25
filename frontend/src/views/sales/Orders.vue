<template>
  <AppPage :title="$t('sales.order_history')" :breadcrumb="[$t('common.sales'), $t('sidebar.orders')]">
    <template #actions>
      <Button icon="pi pi-refresh" severity="secondary" text class="!rounded-2xl !px-4"
        @click="load" :loading="loading" />
    </template>

    <div class="space-y-6">
      <!-- Filters -->
      <div class="grid gap-4 md:grid-cols-3 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
        <div class="md:col-span-2">
          <InputText v-model="filters.q"
            :placeholder="`${$t('common.search_placeholder')} order...`"
            class="w-full !rounded-xl" />
        </div>
        <Select v-model="filters.status" :options="statusOptions" optionLabel="label" optionValue="value"
          placeholder="Filter Status" showClear class="!rounded-xl" />
      </div>

      <!-- Data Table -->
      <DataTable :value="rows" lazy paginator :rows="rowsPerPage" v-model:first="first" :totalRecords="totalRecords"
        :loading="loading" @page="onPage" class="custom-table" :pt="{ wrapper: { class: 'border-none' } }"
        selectionMode="single" @rowSelect="openOrder">
        <Column header="No" class="w-16 text-center">
          <template #body="slotProps">
            <span class="text-slate-400 font-mono text-xs">{{ slotProps.index + first + 1 }}</span>
          </template>
        </Column>
        <Column field="order_number" :header="$t('dashboard.order_id')">
          <template #body="{ data }">
            <span class="font-bold text-slate-700">{{ data.order_number }}</span>
          </template>
        </Column>
        <Column :header="$t('common.date')">
          <template #body="{ data }">
            <span class="text-slate-600">{{ formatDate(data.created_at) }}</span>
          </template>
        </Column>
        <Column field="customer_name" :header="$t('checkout.customer')">
          <template #body="{ data }">
            <span class="text-slate-600">{{ data.customer_name || '-' }}</span>
          </template>
        </Column>
        <Column field="payment_method" :header="$t('sales.payment_method')">
          <template #body="{ data }">
            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase tracking-wider">
              {{ data.payment_method }}
            </span>
          </template>
        </Column>
        <Column field="total_amount" :header="$t('common.total')">
          <template #body="{ data }">
            <span class="font-bold text-slate-800">Rp {{ money(data.total_amount) }}</span>
          </template>
        </Column>
        <Column :header="$t('common.status')" class="w-32">
          <template #body="{ data }">
            <span :class="[
              'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
              data.status === 'completed'
                ? 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                : data.status === 'cancelled'
                  ? 'bg-rose-50 text-rose-600 border border-rose-100'
                  : 'bg-amber-50 text-amber-600 border border-amber-100'
            ]">
              <span :class="[
                'w-1.5 h-1.5 rounded-full',
                data.status === 'completed' ? 'bg-emerald-500' : data.status === 'cancelled' ? 'bg-rose-500' : 'bg-amber-500'
              ]"></span>
              {{ data.status || 'pending' }}
            </span>
          </template>
        </Column>
      </DataTable>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, watch, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { orderApi } from '@/api/sales';
import { unwrapCollection } from '@/utils/api';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

const { t: $t } = useI18n();
const router = useRouter();
const rows = ref([]);
const loading = ref(false);
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const first = ref(0);

const filters = reactive({ q: '', status: null });

const statusOptions = [
  { label: 'Completed', value: 'completed' },
  { label: 'Pending', value: 'pending' },
  { label: 'Cancelled', value: 'cancelled' }
];

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function onPage(event) {
  first.value = event.first;
  rowsPerPage.value = event.rows;
  load(event.page + 1);
}

watch(filters, () => {
  first.value = 0;
  load(1);
}, { deep: true });

async function load(page = 1) {
  loading.value = true;
  try {
    const params = { page, per_page: rowsPerPage.value };
    if (filters.q) params.q = filters.q;
    if (filters.status) params.status = filters.status;
    const response = await orderApi.getAll(params);
    const result = response?.data?.data || {};
    rows.value = result.data || [];
    totalRecords.value = result.meta?.total || 0;
  } finally {
    loading.value = false;
  }
}

async function openOrder(event) {
  router.push({ name: 'order-detail', params: { id: event.data.id } });
}

load();
</script>

<style scoped>
:deep(.custom-table .p-datatable-thead > tr > th) {
  background-color: transparent !important;
  color: #64748b !important;
  font-size: 0.75rem !important;
  text-transform: uppercase !important;
  font-weight: 600 !important;
  letter-spacing: 0.05em;
  border-bottom: 1px solid #f1f5f9 !important;
  border-top: none !important;
  padding: 1rem 0.5rem !important;
}

:deep(.custom-table .p-datatable-tbody > tr > td) {
  border-bottom: 1px solid #f1f5f9 !important;
  padding: 1rem 0.5rem !important;
  color: #334155 !important;
  font-size: 0.875rem !important;
  font-weight: 500;
}

:deep(.custom-table .p-datatable-tbody > tr:hover) {
  background-color: #f8fafc !important;
}

:deep(.custom-table .p-paginator) {
  background-color: transparent !important;
  border: none !important;
  padding-top: 1.5rem !important;
  justify-content: center !important;
}

:deep(.custom-table .p-paginator .p-paginator-pages .p-paginator-page.p-highlight) {
  background-color: #3b82f6 !important;
  color: white !important;
  border-radius: 0.5rem !important;
  border: none !important;
}

:deep(.custom-table .p-paginator .p-paginator-pages .p-paginator-page) {
  border-radius: 0.5rem !important;
  border: 1px solid #e2e8f0 !important;
  margin: 0 0.25rem !important;
  color: #64748b !important;
  min-width: 2.5rem !important;
  height: 2.5rem !important;
}

:deep(.custom-table .p-paginator .p-paginator-prev),
:deep(.custom-table .p-paginator .p-paginator-next),
:deep(.custom-table .p-paginator .p-paginator-first),
:deep(.custom-table .p-paginator .p-paginator-last) {
  border-radius: 0.5rem !important;
  border: 1px solid #e2e8f0 !important;
  margin: 0 0.25rem !important;
  color: #64748b !important;
  min-width: 2.5rem !important;
  height: 2.5rem !important;
}
</style>
