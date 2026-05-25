<template>
  <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal
    :header="$t('product.detail_product')" :style="{ width: '32rem' }">
    <div v-if="product" class="space-y-4">
      <div class="flex justify-center" v-if="product.image">
        <img :src="getImageUrl(product.image)" class="w-48 h-48 object-cover rounded-lg shadow" />
      </div>
      <div class="flex justify-center" v-else>
        <div class="w-48 h-48 bg-slate-200 rounded-lg shadow flex items-center justify-center text-slate-400">
          <i class="pi pi-image text-4xl"></i>
        </div>
      </div>

      <AppDataTable framed :value="detailRows" :paginator="false" dataKey="field" compact>
        <Column field="label" header="Field" class="w-1/3">
          <template #body="{ data }">
            <span class="font-semibold">{{ data.label }}</span>
          </template>
        </Column>
        <Column field="value" header="Nilai">
          <template #body="{ data }">
            <span :class="data.valueClass">{{ data.value }}</span>
          </template>
        </Column>
      </AppDataTable>
    </div>
    <template #footer>
      <Button :label="$t('common.cancel')" text @click="$emit('update:visible', false)" />
    </template>
  </Dialog>
</template>

<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import Column from 'primevue/column';
import AppDataTable from '@/components/AppDataTable.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
const { t: $t } = useI18n();
const props = defineProps({
  visible: { type: Boolean, default: false },
  product: { type: Object, default: null }
});

defineEmits(['update:visible']);

const detailRows = computed(() => {
  const product = props.product;
  if (!product) return [];

  return [
    { field: 'name', label: $t('common.name'), value: product.name || '-' },
    { field: 'short_name', label: $t('product.short_name'), value: product.short_name || '-' },
    { field: 'code', label: $t('product.code'), value: product.code || '-', valueClass: 'font-bold text-blue-600' },
    { field: 'category', label: $t('common.category'), value: product.category?.name || '-' },
    { field: 'brand', label: $t('common.brand'), value: product.brand_name || '-' },
    { field: 'supplier', label: $t('common.supplier'), value: product.supplier?.name || '-' },
    { field: 'unit', label: $t('common.unit'), value: product.unit?.name || '-' },
    { field: 'cost_price', label: $t('product.cost_price'), value: `Rp ${money(product.cost_price)}` },
    { field: 'selling_price', label: $t('product.selling_price'), value: `Rp ${money(product.price)}` },
    { field: 'stock_type', label: $t('product.stock_type'), value: $t(`product.${product.stock_type}`) },
    { field: 'minimum_stock', label: $t('product.minimum_stock'), value: product.minimum_stock || 0 },
    { field: 'maximum_stock', label: $t('product.maximum_stock'), value: product.maximum_stock || 0 },
    { field: 'stock', label: $t('common.stock'), value: product.stock?.current_stock || 0 }
  ];
});

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function getImageUrl(path) {
  if (!path) return '';
  const baseUrl = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
    ? 'http://restoku.test'
    : window.location.origin;
  return `${baseUrl}/storage/${path}`;
}
</script>
