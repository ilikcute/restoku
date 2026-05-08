<template>
  <Dialog :visible="visible" @update:visible="$emit('update:visible', $event)" modal
    :header="$t('product.detail_product')" :style="{ width: '32rem' }">
    <div v-if="product" class="space-y-4">
      <div class="flex justify-center" v-if="product.image">
        <img :src="getImageUrl(product.image)" class="w-48 h-48 object-cover rounded-lg shadow" />
      </div>
      <div class="flex justify-center" v-else>
        <div class="w-48 h-48 bg-gray-200 rounded-lg shadow flex items-center justify-center text-gray-400">
          <i class="pi pi-image text-4xl"></i>
        </div>
      </div>

      <table class="w-full text-left border-collapse">
        <tbody>
          <tr class="border-b">
            <th class="py-2 w-1/3">{{ $t('common.name') }}</th>
            <td>{{ product.name }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.short_name') }}</th>
            <td>{{ product.short_name || '-' }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.code') }}</th>
            <td class="font-bold text-blue-600">{{ product.code || '-' }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('common.category') }}</th>
            <td>{{ product.category?.name || '-' }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('common.brand') }}</th>
            <td>{{ product.brand_name || '-' }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('common.supplier') }}</th>
            <td>{{ product.supplier?.name || '-' }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('common.unit') }}</th>
            <td>{{ product.unit?.name || '-' }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.cost_price') }}</th>
            <td>Rp {{ money(product.cost_price) }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.selling_price') }}</th>
            <td>Rp {{ money(product.price) }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.stock_type') }}</th>
            <td>{{ $t(`product.${product.stock_type}`) }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.minimum_stock') }}</th>
            <td>{{ product.minimum_stock || 0 }}</td>
          </tr>
          <tr class="border-b">
            <th class="py-2">{{ $t('product.maximum_stock') }}</th>
            <td>{{ product.maximum_stock || 0 }}</td>
          </tr>
          <tr>
            <th class="py-2">{{ $t('common.stock') }}</th>
            <td>{{ product.stock?.current_stock || 0 }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <template #footer>
      <Button :label="$t('common.cancel')" text @click="$emit('update:visible', false)" />
    </template>
  </Dialog>
</template>

<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useI18n } from 'vue-i18n';
const { t: $t } = useI18n();

defineProps({
  visible: { type: Boolean, default: false },
  product: { type: Object, default: null }
});

defineEmits(['update:visible']);

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
