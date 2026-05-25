<template>
  <AppPage :title="$t('sidebar.products')" :breadcrumb="[$t('common.master_data'), $t('sidebar.products')]">
    <template #actions>
      <div class="flex gap-2">
        <Button label="Template" icon="pi pi-download" severity="info" text class="!rounded-2xl !px-4"
          @click="downloadTemplate" />
        <Button label="Export" icon="pi pi-file-excel" severity="success" text class="!rounded-2xl !px-4"
          @click="exportProducts" />
        <Button label="Import" icon="pi pi-upload" severity="secondary"
          class="!rounded-2xl !px-6 !bg-white !border-slate-200 !text-slate-600 shadow-sm"
          @click="$refs.fileInput.click()" :loading="importing" />
        <input type="file" ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv" @change="handleImport" />

        <Button :label="`${$t('common.add')} ${$t('sidebar.products')}`" icon="pi pi-plus"
          class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50" @click="openCreate" />
      </div>
    </template>

    <div class="space-y-6">
      <div class="grid gap-4 md:grid-cols-4 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
        <Select v-model="filters.category_id" :options="categories" optionLabel="name" optionValue="id"
          :placeholder="$t('common.category')" showClear class="!rounded-xl" />
        <Select v-model="filters.unit_id" :options="units" optionLabel="name" optionValue="id"
          :placeholder="$t('common.unit')" showClear class="!rounded-xl" />
        <div class="md:col-span-2">
          <InputText v-model="filters.q"
            :placeholder="`${$t('common.search_placeholder')} ${$t('sidebar.products')}...`"
            class="w-full !rounded-xl" />
        </div>
      </div>

      <AppDataTable framed
        :value="products"
        lazy
        paginator
        :rows="rowsPerPage"
        :first="first"
        :totalRecords="totalRecords"
        :loading="loading"
        @page="onPage"
      >
        <Column header="No" class="w-16 text-center">
          <template #body="slotProps">
            <span class="text-slate-400 font-mono text-xs">{{ slotProps.index + first + 1 }}</span>
          </template>
        </Column>
        <Column :header="$t('common.image')">
          <template #body="{ data }">
            <img v-if="data.image" :src="getImageUrl(data.image)" class="w-10 h-10 object-cover rounded-full shadow-sm" />
            <div v-else class="w-10 h-10 bg-slate-50 border border-slate-100 rounded-full shadow-sm flex items-center justify-center text-slate-400">
              <i class="pi pi-image text-lg"></i>
            </div>
          </template>
        </Column>
        <Column field="name" :header="$t('common.name')">
          <template #body="{ data }">
            <span class="font-semibold text-slate-800">{{ data.name }}</span>
          </template>
        </Column>
        <Column field="code" :header="$t('product.code')">
          <template #body="{ data }">
            <span class="font-mono text-xs text-slate-500">{{ data.code }}</span>
          </template>
        </Column>
        <Column field="category.name" :header="$t('common.category')">
          <template #body="{ data }">
            <span class="px-2 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold uppercase tracking-wider">{{ data.category?.name }}</span>
          </template>
        </Column>
        <Column field="unit.short_name" :header="$t('common.unit')">
          <template #body="{ data }">
            <span class="text-slate-500 text-xs">{{ data.unit?.short_name }}</span>
          </template>
        </Column>
        <Column field="price" :header="$t('common.price')">
          <template #body="{ data }">
            <span class="font-semibold text-slate-700">Rp {{ money(data.price) }}</span>
          </template>
        </Column>
        <Column :header="$t('common.stock')">
          <template #body="{ data }">
            <StatusBadge :status="stockSeverity(data) === 'danger' ? 'failed' : 'active'" :label="String(data.stock?.current_stock ?? 0)" />
          </template>
        </Column>
        <Column :header="$t('common.actions')" class="w-28">
          <template #body="{ data }">
            <div class="flex items-center gap-1">
              <Button icon="pi pi-eye" outlined class="!w-8 !h-8 !p-0 !text-slate-500 !border-slate-200 hover:!bg-slate-50" @click="openShow(data)" />
              <Button icon="pi pi-pencil" outlined class="!w-8 !h-8 !p-0 !text-slate-500 !border-slate-200 hover:!bg-slate-50" @click="openEdit(data)" />
              <Button icon="pi pi-trash" outlined class="!w-8 !h-8 !p-0 !text-red-500 !border-slate-200 hover:!bg-red-50" @click="remove(data)" />
            </div>
          </template>
        </Column>
      </AppDataTable>

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
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';

import ProductFormModal from '@/components/master/ProductFormModal.vue';
import ProductDetailModal from '@/components/master/ProductDetailModal.vue';
import AppPage from '@/components/layout/AppPage.vue';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';

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
const importing = ref(false);
const fileInput = ref(null);

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
async function handleImport(event) {
  const file = event.target.files[0];
  if (!file) return;

  importing.value = true;
  const formData = new FormData();
  formData.append('file', file);

  try {
    const response = await productApi.import(formData);
    const result = response.data.data;

    toast.add({
      severity: 'success',
      summary: 'Import Berhasil',
      detail: `${result.success_count} produk berhasil di-import.`,
      life: 5000
    });

    if (result.errors && result.errors.length > 0) {
      toast.add({
        severity: 'warn',
        summary: 'Beberapa baris bermasalah',
        detail: `Cek konsol untuk detail error.`,
        life: 5000
      });
      console.warn('Import Errors:', result.errors);
    }

    loadProducts();
  } catch (error) {
    console.error('Import Error:', error);
    toast.add({
      severity: 'error',
      summary: 'Import Gagal',
      detail: error.response?.data?.message || 'Terjadi kesalahan saat mengunggah file.',
      life: 5000
    });
  } finally {
    importing.value = false;
    if (fileInput.value) fileInput.value.value = '';
  }
}

async function downloadTemplate() {
  try {
    const response = await productApi.downloadTemplate();
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'template_import_produk.xlsx');
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    console.error('Download Error:', error);
    toast.add({ severity: 'error', summary: 'Gagal Download Template', life: 3000 });
  }
}

async function exportProducts() {
  try {
    const response = await productApi.export();
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `export_produk_${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    toast.add({ severity: 'success', summary: 'Export Berhasil', life: 3000 });
  } catch (error) {
    console.error('Export Error:', error);
    toast.add({ severity: 'error', summary: 'Gagal Export Produk', life: 3000 });
  }
}

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


