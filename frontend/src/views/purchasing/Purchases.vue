<template>
  <AppPage 
    title="Pembelian Stok" 
    subtitle="Kelola pengadaan stok barang dari supplier dan pantau pengeluaran inventaris."
    accent="orange"
    :breadcrumb="['Manajemen', 'Pembelian']"
  >
    <template #actions>
      <Button label="Tambah Pembelian" icon="pi pi-plus" class="!rounded-2xl !px-6 !bg-orange-600 !border-none shadow-lg shadow-orange-200/50" @click="openDialog" />
    </template>

    <DataTable :value="rows" :loading="loading" paginator :rows="20" class="p-datatable-modern" responsiveLayout="scroll">
      <Column field="purchase_number" :header="$t('purchasing.number')" style="width: 180px">
        <template #body="{ data }">
          <span class="font-mono font-bold text-slate-700 tracking-tighter">{{ data.purchase_number }}</span>
        </template>
      </Column>
      <Column :header="$t('common.date')" style="width: 150px">
        <template #body="{ data }">
          <div class="flex items-center gap-2 text-slate-500 text-sm font-medium">
            <i class="pi pi-calendar text-[10px]"></i>
            {{ formatDate(data.purchase_date) }}
          </div>
        </template>
      </Column>
      <Column :header="$t('common.supplier')">
        <template #body="{ data }">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center">
              <i class="pi pi-truck text-xs"></i>
            </div>
            <span class="font-bold text-slate-700">{{ data.supplier?.name }}</span>
          </div>
        </template>
      </Column>
      <Column field="total_amount" :header="$t('common.total')" style="width: 180px">
        <template #body="{ data }">
          <span class="text-lg font-black text-slate-800 tracking-tight">
            <span class="text-xs font-bold mr-1 text-slate-400">Rp</span>
            {{ money(data.total_amount) }}
          </span>
        </template>
      </Column>
      <Column field="payment_status" :header="$t('common.payment_status')" style="width: 120px">
        <template #body="{ data }">
          <div :class="[
            'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 border',
            data.payment_status === 'paid' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'
          ]">
            <span :class="['w-1.5 h-1.5 rounded-full', data.payment_status === 'paid' ? 'bg-emerald-500' : 'bg-rose-500']"></span>
            {{ data.payment_status === 'paid' ? 'Lunas' : 'Hutang' }}
          </div>
        </template>
      </Column>
      <Column field="status" :header="$t('common.status')" style="width: 120px">
        <template #body="{ data }">
          <div class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5 bg-slate-50 text-slate-600 border border-slate-100">
            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
            {{ data.status || 'Selesai' }}
          </div>
        </template>
      </Column>
      <Column header="" class="text-right w-32">
        <template #body="{ data }">
          <div class="flex justify-end gap-2">
            <Button icon="pi pi-eye" text rounded severity="success" class="hover:bg-emerald-50" @click="viewDetail(data)" v-tooltip.top="'Detail'" />
            <Button icon="pi pi-print" text rounded severity="secondary" class="hover:bg-slate-100" @click="downloadPdf(data)" v-tooltip.top="'Print'" />
          </div>
        </template>
      </Column>
    </DataTable>

    <!-- Create Purchase Dialog -->
    <Dialog v-model:visible="dialogOpen" :header="$t('purchasing.create_purchase')" modal :style="{ width: '56rem' }" class="p-fluid !rounded-3xl overflow-hidden shadow-2xl" pt:root:class="!rounded-3xl" pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100" pt:content:class="!p-6">
      <!-- ... rest of dialog ... -->
        <div class="space-y-6">
          <!-- Form Header Info -->
          <div class="flex items-start gap-4 p-4 bg-orange-50 rounded-2xl border border-orange-100">
            <div class="w-10 h-10 rounded-xl bg-orange-500 text-white flex items-center justify-center shrink-0">
              <i class="pi pi-shopping-cart text-xl"></i>
            </div>
            <div>
              <p class="text-sm font-bold text-orange-900">Pembelian Stok Baru</p>
              <p class="text-[11px] text-orange-700/70 leading-relaxed font-medium">Input data pembelian barang untuk memperbarui stok dan mencatat pengeluaran secara otomatis.</p>
            </div>
          </div>

          <div class="grid gap-6 md:grid-cols-4">
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Supplier / Pemasok</label>
              <Select v-model="form.supplier_id" :options="suppliers" optionLabel="name" optionValue="id" :placeholder="$t('common.supplier')" class="!rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3 !text-sm" />
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Metode Bayar</label>
              <Select v-model="form.payment_method" :options="[
                { label: 'Tunai (Cash)', value: 'cash' },
                { label: 'Kredit (Hutang)', value: 'credit' }
              ]" optionLabel="label" optionValue="value" class="!rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3 !text-sm" />
            </div>
            <div v-if="form.payment_method === 'cash'" class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Akun Pembayaran</label>
              <Select v-model="form.account_id" :options="accounts" optionLabel="name" optionValue="id" :placeholder="$t('purchasing.account')" class="!rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3 !text-sm" />
            </div>
            <div v-else class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1 opacity-40">Status Pembayaran</label>
              <div class="p-3 bg-rose-50 border border-rose-100 rounded-xl text-rose-600 font-bold text-xs flex items-center gap-2">
                <i class="pi pi-info-circle text-[10px]"></i> Dicatat sebagai Hutang
              </div>
            </div>
            <div class="flex flex-col gap-2">
              <label class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Tanggal Beli</label>
              <DatePicker v-model="purchaseDate" dateFormat="yy-mm-dd" showIcon iconDisplay="input" class="!rounded-xl overflow-hidden" pt:input:class="!bg-slate-50 !border-slate-100 !p-3 !text-sm" />
            </div>
          </div>

          <!-- Add Item Section -->
          <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 space-y-4">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] flex items-center gap-2 mb-2">
              <i class="pi pi-plus-circle text-orange-500"></i> Tambah Item Barang
            </p>
            <div class="grid gap-4 md:grid-cols-4">
              <div class="flex flex-col gap-2 md:col-span-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Pilih Produk</label>
                <Select v-model="item.product_id" :options="products" optionLabel="name" optionValue="id" filter :placeholder="$t('sidebar.products')" class="!rounded-xl overflow-hidden" pt:input:class="!bg-white !border-slate-200 !p-3 !text-sm" />
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Jumlah (Qty)</label>
                <InputNumber v-model="item.quantity" mode="decimal" :min="1" placeholder="0" class="!rounded-xl overflow-hidden" pt:input:class="!bg-white !border-slate-200 !p-3 !text-sm" />
              </div>
              <div class="flex flex-col gap-2">
                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Harga Beli / Unit</label>
                <InputNumber v-model="item.cost_price" mode="decimal" :min="0" placeholder="0" class="!rounded-xl overflow-hidden" pt:input:class="!bg-white !border-slate-200 !p-3 !text-sm" />
              </div>
            </div>
            <div class="flex justify-end pt-2">
              <Button label="Tambahkan ke Daftar" icon="pi pi-plus" size="small" class="!rounded-xl !px-8 !bg-orange-600 !border-none font-bold shadow-md shadow-orange-100" @click="addItem" />
            </div>
          </div>

          <!-- Items Table -->
          <div class="border border-slate-100 rounded-2xl overflow-hidden bg-white shadow-sm">
            <DataTable :value="form.items" class="p-datatable-sm" responsiveLayout="scroll">
              <Column :header="$t('sidebar.products')">
                <template #body="{ data }">
                    <div class="flex flex-col">
                      <span class="font-bold text-slate-700">{{ productName(data.product_id) }}</span>
                      <span class="text-[10px] text-slate-400 font-medium">SKU: {{ productCode(data.product_id) }}</span>
                    </div>
                </template>
              </Column>
              <Column field="quantity" :header="$t('inventory.qty')" style="width: 100px">
                <template #body="{ data }">
                  <span class="font-mono font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded text-xs">{{ data.quantity }}</span>
                </template>
              </Column>
              <Column field="cost_price" :header="$t('product.cost_price')">
                <template #body="{ data }">
                  <span class="text-slate-600">Rp {{ money(data.cost_price) }}</span>
                </template>
              </Column>
              <Column header="Subtotal" style="width: 160px">
                <template #body="{ data }">
                  <span class="font-black text-slate-800">Rp {{ money(data.quantity * data.cost_price) }}</span>
                </template>
              </Column>
              <Column style="width: 50px">
                <template #body="slotProps">
                    <Button icon="pi pi-trash" text rounded severity="danger" size="small" @click="removeItem(slotProps.index)" />
                </template>
              </Column>
            </DataTable>
            <div v-if="form.items.length === 0" class="p-12 text-center bg-slate-50/30">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                  <i class="pi pi-shopping-bag text-2xl text-slate-300"></i>
                </div>
                <p class="text-sm text-slate-400 italic">Belum ada item barang yang ditambahkan ke daftar pembelian.</p>
            </div>
          </div>
        </div>

        <template #footer>
          <div class="flex justify-between items-center w-full px-2">
            <div class="flex flex-col items-start">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Belanja</span>
                <span class="text-2xl font-black text-orange-600 italic">Rp {{ money(totalAmount) }}</span>
            </div>
            <div class="flex gap-3">
                <Button label="Batal" icon="pi pi-times" text class="!rounded-xl !text-slate-400 hover:!bg-slate-100" @click="dialogOpen = false" />
                <Button :label="`${$t('common.save')}`" icon="pi pi-check" :loading="saving" class="!rounded-xl !bg-orange-600 !border-none !px-8 h-12 font-bold shadow-lg shadow-orange-100" @click="save" />
            </div>
          </div>
        </template>
      </Dialog>
    </AppPage>
</template>

<script setup>
import axios from '@/api/axios';
import { reactive, ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { financeApi } from '@/api/finance';
import { productApi, supplierApi } from '@/api/master';
import { purchaseApi } from '@/api/sales';
import { unwrapCollection } from '@/utils/api';
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import AppPage from '@/components/layout/AppPage.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';

const { t: $t } = useI18n();
const toast = useToast();
const router = useRouter();
const loading = ref(false);
const saving = ref(false);
const rows = ref([]);
const products = ref([]);
const suppliers = ref([]);
const accounts = ref([]);
const dialogOpen = ref(false);
const purchaseDate = ref(new Date());
const item = reactive({ product_id: null, quantity: 1, cost_price: 0 });
const form = reactive({ supplier_id: null, payment_method: 'cash', account_id: null, purchase_date: '', items: [] });

const totalAmount = computed(() => {
    return form.items.reduce((total, i) => total + (i.quantity * i.cost_price), 0);
});

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function formatDate(date) {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric'
  });
}

function productName(id) {
  return products.value.find((product) => product.id === id)?.name || '-';
}

function productCode(id) {
  return products.value.find((product) => product.id === id)?.code || '-';
}

function openDialog() {
    form.supplier_id = null;
    form.payment_method = 'cash';
    form.account_id = accounts.value[0]?.id || null;
    form.items = [];
    purchaseDate.value = new Date();
    dialogOpen.value = true;
}

function addItem() {
  if (!item.product_id) return;
  form.items.push({ product_id: item.product_id, quantity: item.quantity || 1, cost_price: item.cost_price || 0 });
  item.product_id = null;
  item.quantity = 1;
  item.cost_price = 0;
}

function removeItem(index) {
    form.items.splice(index, 1);
}

async function loadRows() {
  loading.value = true;
  try {
    const response = await purchaseApi.getAll();
    rows.value = unwrapCollection(response).items;
  } finally {
    loading.value = false;
  }
}

async function bootstrap() {
  const [productRes, supplierRes, accountRes] = await Promise.all([
      productApi.getAll(), 
      supplierApi.getAll(), 
      financeApi.getAccounts()
  ]);
  
  // Unwrap properly to get the array
  products.value = unwrapCollection(productRes).items;
  suppliers.value = unwrapCollection(supplierRes).items;
  accounts.value = unwrapCollection(accountRes).items;
}

function viewDetail(purchase) {
  router.push({ name: 'purchase-detail', params: { id: purchase.id } });
}

async function downloadPdf(purchase) {
  try {
    const response = await axios.get(`/purchases/${purchase.id}/pdf`, {
      responseType: 'blob'
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Purchase_${purchase.purchase_number}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Gagal', detail: 'Gagal mengunduh PDF.', life: 3000 });
  }
}

async function save() {
  if (form.items.length === 0) {
      toast.add({ severity: 'error', summary: 'Gagal', detail: 'Minimal harus ada 1 item barang.', life: 3000 });
      return;
  }

  saving.value = true;
  try {
    const d = purchaseDate.value;
    const offset = d.getTimezoneOffset();
    form.purchase_date = new Date(d.getTime() - (offset*60*1000)).toISOString().split('T')[0];
    
    await purchaseApi.create(form);
    dialogOpen.value = false;
    form.items = [];
    toast.add({ severity: 'success', summary: $t('common.save'), life: 2000 });
    loadRows();
  } catch (error) {
    toast.add({ severity: 'error', summary: $t('common.save'), detail: error?.response?.data?.message || 'Validation failed', life: 3000 });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
    bootstrap();
    loadRows();
});
</script>

<style scoped>
@reference "@/style.css";

.p-datatable-modern :deep(.p-datatable-thead > tr > th) {
  @apply bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-[0.2em] border-none py-4;
}

.p-datatable-modern :deep(.p-datatable-tbody > tr) {
  @apply transition-colors duration-200 border-b border-slate-50;
}

.p-datatable-modern :deep(.p-datatable-tbody > tr:hover) {
  @apply bg-slate-50/50;
}

.p-datatable-modern :deep(.p-datatable-tbody > tr > td) {
  @apply py-4 border-none;
}
</style>
