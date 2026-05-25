<template>
  <AppPage :title="pageTitle" :breadcrumb="breadcrumb">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-4">
      <!-- Search & Info Card -->
      <div class="lg:col-span-1 space-y-6">
        <Card class="border-none shadow-sm overflow-hidden">
          <template #header>
            <div class="bg-slate-900 p-4">
              <h3 class="text-white font-bold flex items-center gap-2">
                <i class="pi pi-search"></i>
                {{ $t('common.search') }} Transaksi
              </h3>
            </div>
          </template>
          <template #content>
            <div class="space-y-4">
              <div class="p-fluid">
                <div class="p-inputgroup">
                  <InputText 
                    v-model="searchNumber" 
                    :placeholder="type === 'order' ? 'ORD-XXXX' : 'PUR-XXXX'" 
                    @keyup.enter="handleSearch"
                  />
                  <Button icon="pi pi-search" @click="handleSearch" :loading="searching" />
                </div>
              </div>

              <div v-if="transaction" class="p-4 bg-slate-50 rounded-xl border border-slate-100 space-y-3">
                <div class="flex justify-between items-start">
                  <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Nomor Transaksi</p>
                    <p class="font-mono font-bold text-slate-900">{{ transaction.order_number || transaction.purchase_number }}</p>
                  </div>
                  <Tag :value="transaction.status" severity="success" />
                </div>
                <div>
                  <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Tanggal</p>
                  <p class="text-sm font-semibold">{{ formatDate(transaction.created_at) }}</p>
                </div>
                <div v-if="type === 'order'">
                  <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Pelanggan</p>
                  <p class="text-sm font-semibold">{{ transaction.customer_name || 'Pelanggan Umum' }}</p>
                </div>
                <div v-else>
                  <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Supplier</p>
                  <p class="text-sm font-semibold">{{ transaction.supplier?.name || '-' }}</p>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                  <p class="text-sm font-bold">Total Transaksi</p>
                  <p class="text-sm font-bold text-slate-900">Rp {{ money(transaction.total_amount) }}</p>
                </div>
              </div>
              
              <div v-else-if="!searching && searchNumber" class="text-center py-8">
                <i class="pi pi-info-circle text-slate-300 text-4xl mb-2"></i>
                <p class="text-slate-500 text-sm">Masukkan nomor transaksi untuk memulai retur.</p>
              </div>
            </div>
          </template>
        </Card>

        <Card v-if="transaction" class="border-none shadow-sm overflow-hidden">
          <template #header>
            <div class="bg-emerald-600 p-4">
              <h3 class="text-white font-bold flex items-center gap-2">
                <i class="pi pi-wallet"></i>
                Pengaturan Pengembalian
              </h3>
            </div>
          </template>
          <template #content>
            <div class="space-y-4">
              <div class="field">
                <label class="font-bold block mb-2">{{ $t('purchasing.account') }}</label>
                <Select 
                  v-model="form.account_id" 
                  :options="accounts" 
                  optionLabel="name" 
                  optionValue="id" 
                  class="w-full"
                  placeholder="Pilih Rekening"
                />
                <small class="text-slate-500 mt-1 block">Rekening ini akan {{ type === 'order' ? 'berkurang' : 'bertambah' }} saldonya.</small>
              </div>
              
              <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                <div class="flex justify-between items-center mb-1">
                  <p class="text-sm font-bold text-emerald-900">Total Nilai Retur</p>
                  <p class="text-lg font-black text-emerald-700">Rp {{ money(totalReturnAmount) }}</p>
                </div>
              </div>

              <Button 
                :label="'Simpan ' + pageTitle" 
                icon="pi pi-check-circle" 
                class="w-full h-12 text-lg" 
                :disabled="totalReturnAmount <= 0 || !form.account_id"
                :loading="saving"
                @click="confirmSave"
              />
            </div>
          </template>
        </Card>
      </div>

      <!-- Items Table Card -->
      <div class="lg:col-span-2">
        <Card class="border-none shadow-sm h-full">
          <template #content>
            <AppDataTable 
              :value="items" 
              class="app-table" 
              responsiveLayout="scroll"
              :placeholder="'Belum ada data transaksi'"
            >
              <template #empty>
                <div class="text-center py-12 text-slate-400">
                  <i class="pi pi-shopping-cart text-6xl mb-4 opacity-20"></i>
                  <p>Cari transaksi untuk melihat daftar barang.</p>
                </div>
              </template>
              <Column field="product_name" header="Nama Barang">
                <template #body="{ data }">
                  <div class="flex flex-col">
                    <span class="font-bold text-slate-700">{{ data.product_name }}</span>
                    <span class="text-[10px] text-slate-500">Harga: Rp {{ money(data.price || data.cost_price) }}</span>
                  </div>
                </template>
              </Column>
              <Column header="Qty Beli" class="text-center" style="width: 80px">
                <template #body="{ data }">{{ data.quantity }}</template>
              </Column>
              <Column header="Sudah Retur" class="text-center" style="width: 100px">
                <template #body="{ data }">
                   <Tag :value="data.return_quantity || 0" :severity="data.return_quantity > 0 ? 'warning' : 'secondary'" />
                </template>
              </Column>
              <Column header="Qty Retur Baru" style="width: 150px">
                <template #body="{ data }">
                  <div class="flex items-center gap-2">
                    <InputNumber 
                      v-model="data.new_return_qty" 
                      showButtons 
                      buttonLayout="horizontal" 
                      :min="0" 
                      :max="data.quantity - (data.return_quantity || 0)"
                      inputClass="w-16 text-center font-bold"
                      decrementButtonClass="p-button-secondary p-button-text"
                      incrementButtonClass="p-button-secondary p-button-text"
                      incrementButtonIcon="pi pi-plus"
                      decrementButtonIcon="pi pi-minus"
                      @input="() => {}"
                    />
                  </div>
                </template>
              </Column>
              <Column header="Subtotal Retur" class="text-right font-bold" style="width: 120px">
                <template #body="{ data }">
                  Rp {{ money(calculateItemReturn(data)) }}
                </template>
              </Column>
            </AppDataTable>
          </template>
        </Card>
      </div>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { useI18n } from 'vue-i18n';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { returnsApi } from '@/api/returns';
import { financeApi } from '@/api/finance';
import AppPage from '@/components/layout/AppPage.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import Column from 'primevue/column';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import Select from 'primevue/select';

const props = defineProps({
  type: { type: String, required: true } // 'order' or 'purchase'
});

const { t: $t } = useI18n();
const toast = useToast();
const confirm = useConfirm();

const searchNumber = ref('');
const searching = ref(false);
const saving = ref(false);
const transaction = ref(null);
const items = ref([]);
const accounts = ref([]);

const form = reactive({
  account_id: null
});

const pageTitle = computed(() => props.type === 'order' ? 'Retur Penjualan' : 'Retur Pembelian');
const breadcrumb = computed(() => [
  props.type === 'order' ? $t('common.sales') : $t('common.purchasing'),
  pageTitle.value
]);

const totalReturnAmount = computed(() => {
  return items.value.reduce((total, item) => total + calculateItemReturn(item), 0);
});

function calculateItemReturn(item) {
  const qty = item.new_return_qty || 0;
  const price = item.price || item.cost_price || 0;
  const discountPerQty = (item.discount_amount || 0) / item.quantity;
  return (price - discountPerQty) * qty;
}

async function handleSearch() {
  if (!searchNumber.value) return;
  
  searching.value = true;
  transaction.value = null;
  items.value = [];
  
  try {
    const response = await returnsApi.search({ 
      number: searchNumber.value, 
      type: props.type 
    });
    transaction.value = response.data.data;
    items.value = (transaction.value.items || []).map(item => ({
      ...item,
      new_return_qty: 0
    }));
  } catch (error) {
    toast.add({ 
      severity: 'error', 
      summary: 'Tidak Ditemukan', 
      detail: error.response?.data?.message || 'Transaksi tidak ditemukan.', 
      life: 3000 
    });
  } finally {
    searching.value = false;
  }
}

async function loadAccounts() {
  try {
    const response = await financeApi.getAccounts();
    accounts.value = response.data.data || [];
    if (accounts.value.length > 0) {
      form.account_id = accounts.value[0].id;
    }
  } catch (error) {
    console.error('Failed to load accounts', error);
  }
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

function money(value) {
  return Number(value || 0).toLocaleString('id-ID');
}

function confirmSave() {
  confirm.require({
    message: `Apakah Anda yakin ingin memproses retur senilai Rp ${money(totalReturnAmount.value)}?`,
    header: 'Konfirmasi Retur',
    icon: 'pi pi-exclamation-triangle',
    acceptClass: 'p-button-danger',
    accept: () => saveReturn()
  });
}

async function saveReturn() {
  saving.value = true;
  try {
    const payload = {
      account_id: form.account_id,
      items: items.value
        .filter(item => item.new_return_qty > 0)
        .map(item => ({
          [props.type === 'order' ? 'order_item_id' : 'purchase_item_id']: item.id,
          quantity: item.new_return_qty
        }))
    };

    if (props.type === 'order') {
      payload.order_id = transaction.value.id;
      await returnsApi.storeOrderReturn(payload);
    } else {
      payload.purchase_id = transaction.value.id;
      await returnsApi.storePurchaseReturn(payload);
    }

    toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Transaksi retur berhasil diproses.', life: 3000 });
    
    // Reset
    transaction.value = null;
    items.value = [];
    searchNumber.value = '';
  } catch (error) {
    toast.add({ 
      severity: 'error', 
      summary: 'Gagal', 
      detail: error.response?.data?.message || 'Terjadi kesalahan sistem.', 
      life: 3000 
    });
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  loadAccounts();
});
</script>


