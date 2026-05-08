<template>
  <AppPage :title="$t('sales.order_history')" :breadcrumb="[$t('common.sales'), $t('sidebar.orders')]">
      <DataTable :value="rows" :loading="loading" paginator :rows="20" selectionMode="single" @rowSelect="openOrder">
        <Column field="order_number" :header="$t('dashboard.order_id')" />
        <Column :header="$t('common.date')">
          <template #body="{ data }">{{ formatDate(data.created_at) }}</template>
        </Column>
        <Column field="customer_name" :header="$t('checkout.customer')" />
        <Column field="payment_method" :header="$t('sales.payment_method')" />
        <Column field="total_amount" :header="$t('common.total')">
          <template #body="{ data }">Rp {{ money(data.total_amount) }}</template>
        </Column>
      </DataTable>
  </AppPage>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { orderApi } from '@/api/sales';
import { unwrapCollection } from '@/utils/api';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

const { t: $t } = useI18n();
const router = useRouter();
const rows = ref([]);
const loading = ref(false);

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

async function load() {
  loading.value = true;
  try {
    const response = await orderApi.getAll();
    rows.value = unwrapCollection(response).items;
  } finally {
    loading.value = false;
  }
}

async function openOrder(event) {
  router.push({ name: 'order-detail', params: { id: event.data.id } });
}

load();
</script>
