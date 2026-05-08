<template>
  <AppPage :title="$t('sidebar.products')" :subtitle="$t('product.manage_desc', 'Kelola daftar produk, harga, dan stok inventaris Anda di sini.')" :breadcrumb="[$t('common.master_data'), $t('sidebar.products')]">
    <template #actions>
      <Button :label="`${$t('common.add')} ${$t('sidebar.products')}`" icon="pi pi-plus" class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50" @click="openCreate" />
    </template>

    <div class="space-y-6">
      <div class="grid gap-4 md:grid-cols-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
        <Select v-model="filters.category_id" :options="categories" optionLabel="name" optionValue="id"
          :placeholder="$t('common.category')" showClear class="!rounded-xl" />
        <Select v-model="filters.unit_id" :options="units" optionLabel="name" optionValue="id"
          :placeholder="$t('common.unit')" showClear class="!rounded-xl" />
        <div class="md:col-span-2">
          <InputText v-model="filters.q" :placeholder="`${$t('common.search_placeholder')} ${$t('sidebar.products')}...`" class="w-full !rounded-xl" />
        </div>
      </div>

      <DataTable :value="products" lazy paginator :rows="rowsPerPage" v-model:first="first" :totalRecords="totalRecords"
        :loading="loading" @page="onPage" class="p-datatable-modern">
      <Column header="No" class="w-16 text-center">
        <template #body="slotProps">
          {{ slotProps.index + first + 1 }}
        </template>
      </Column>
      <Column :header="$t('common.image')">
        <template #body="{ data }">
          <img v-if="data.image" :src="getImageUrl(data.image)" class="w-10 h-10 object-cover rounded shadow-sm" />
          <div v-else class="w-10 h-10 bg-gray-100 rounded shadow-sm flex items-center justify-center text-gray-400">
            <i class="pi pi-image text-lg"></i>
          </div>
        </template>
      </Column>
      <Column field="name" :header="$t('common.name')" />
      <Column field="code" :header="$t('product.code')" />
      <Column field="category.name" :header="$t('common.category')">
        <template #body="{ data }">{{ data.category?.name }}</template>
      </Column>
      <Column field="unit.short_name" :header="$t('common.unit')">
        <template #body="{ data }">{{ data.unit?.short_name }}</template>
      </Column>
      <Column field="price" :header="$t('common.price')">
        <template #body="{ data }">Rp {{ money(data.price) }}</template>
      </Column>
      <Column :header="$t('common.stock')">
        <template #body="{ data }">
          <Tag :value="data.stock?.current_stock ?? 0" :severity="stockSeverity(data)" />
        </template>
      </Column>
      <Column :header="$t('common.actions')">
        <template #body="{ data }">
          <Button icon="pi pi-eye" text rounded severity="info" @click="openShow(data)" />
          <Button icon="pi pi-pencil" text rounded @click="openEdit(data)" />
          <Button icon="pi pi-trash" text rounded severity="danger" @click="remove(data)" />
        </template>
      </Column>
    </DataTable>

    <ProductFormModal v-model:visible="dialogOpen" :title="dialogTitle" :product="selectedProductForEdit"
      :categories="categories" :suppliers="suppliers" :units="units" @saved="loadProducts" />

    <ProductDetailModal v-model:visible="showDialogOpen" :product="selectedProduct" />
    </div>
  </AppPage>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { productApi, masterApi } from '@/api/master';

import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';

import ProductFormModal from '@/components/master/ProductFormModal.vue';
import ProductDetailModal from '@/components/master/ProductDetailModal.vue';
import AppPage from '@/components/layout/AppPage.vue';

const { t: $t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

// State
const loading = ref(false);
const dialogOpen = ref(false);
const showDialogOpen = ref(false);
const dialogTitle = ref('');
const selectedProduct = ref(null);
const selectedProductForEdit = ref(null);

// Data
const products = ref([]);
const categories = ref([]);
const suppliers = ref([]);
const units = ref([]);

// Pagination
const totalRecords = ref(0);
const rowsPerPage = ref(10);
const first = ref(0);

// Filters
const filters = reactive({ q: '', category_id: null, unit_id: null });

// --- Pagination Handler ---
function onPage(event) {
  first.value = event.first;
  rowsPerPage.value = event.rows;
  loadProducts(event.page + 1);
}

// Watch filters — reset ke halaman 1 saat filter berubah
watch(filters, () => {
  first.value = 0;
  loadProducts(1);
}, { deep: true });

// --- Helpers ---
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

function stockSeverity(product) {
  if (!product.stock) return 'secondary';
  return Number(product.stock.current_stock) <= Number(product.stock.minimum_stock) ? 'danger' : 'success';
}

// --- Dialog handlers ---
function openCreate() {
  selectedProductForEdit.value = null;
  dialogTitle.value = `${$t('common.add')} ${$t('sidebar.products')}`;
  dialogOpen.value = true;
}

function openEdit(item) {
  selectedProductForEdit.value = item;
  dialogTitle.value = `${$t('common.edit')} ${$t('sidebar.products')}`;
  dialogOpen.value = true;
}

function openShow(item) {
  selectedProduct.value = item;
  showDialogOpen.value = true;
}

// --- API Calls ---
async function initPage() {
  loading.value = true;
  try {
    const response = await productApi.getInitData();
    const data = response?.data?.data || {};

    // Set products dari init endpoint
    const productData = data.products || {};
    products.value = productData.data || [];
    totalRecords.value = productData.meta?.total || 0;

    // Set options untuk dropdown filter & form
    const options = data.options || {};
    categories.value = options.categories || [];
    units.value = options.units || [];
    suppliers.value = options.suppliers || [];
  } catch (error) {
    console.error('Failed to initialize page', error);
  } finally {
    loading.value = false;
  }
}

async function loadProducts(page = 1) {
  loading.value = true;
  try {
    const params = { page, per_page: rowsPerPage.value };
    if (filters.q) params.q = filters.q;
    if (filters.category_id) params.category_id = filters.category_id;

    const response = await productApi.getAll(params);
    // response.data.data = { data: [...items], meta: { total, ... } }
    const result = response?.data?.data || {};
    products.value = result.data || [];
    totalRecords.value = result.meta?.total || 0;
  } finally {
    loading.value = false;
  }
}

function remove(item) {
  confirm.require({
    header: $t('common.confirm'),
    message: $t('common.delete_confirm', { name: item.name }),
    acceptClass: 'p-button-danger',
    accept: async () => {
      await productApi.delete(item.id);
      toast.add({ severity: 'success', summary: $t('common.delete'), life: 2000 });
      loadProducts();
    }
  });
}

// Inisialisasi halaman
initPage();
</script>
