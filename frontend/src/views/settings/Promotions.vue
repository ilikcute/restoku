<template>
  <AppPage title="Manajemen Promosi" :breadcrumb="['Pengaturan', 'Promosi']" no-card>
    <template #actions>
      <Button :label="`${$t('common.add')} ${$t('promotions')}`" icon="pi pi-plus"
        class="!rounded-2xl !px-6 !bg-emerald-600 !border-none shadow-lg shadow-emerald-200/50" @click="openNew" />
    </template>
    <div class="space-y-6">
      <!-- Main Content Card -->
      <Card class="!rounded-3xl border-none shadow-sm overflow-hidden bg-white">
        <template #content>
          <AppDataTable :value="items" :loading="loading" dataKey="id" class="app-table" responsiveLayout="scroll"
            :rows="10" paginator>
            <Column field="priority" header="Prio" sortable style="width: 80px">
              <template #body="slotProps">
                <div
                  class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-slate-600 text-xs">
                  {{ slotProps.data.priority }}
                </div>
              </template>
            </Column>
            <Column field="title" header="Promosi & Tipe" sortable>
              <template #body="slotProps">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-800">{{ slotProps.data.title }}</span>
                  <div class="flex items-center gap-2 mt-1">
                    <Tag :severity="getTypeSeverity(slotProps.data.type)" :value="getTypeLabel(slotProps.data.type)"
                      class="!text-[9px] !px-2 !py-0.5" />
                    <span v-if="slotProps.data.type !== 'announcement'"
                      class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                      {{ formatDiscount(slotProps.data) }}
                    </span>
                  </div>
                  <div class="flex items-center gap-1 mt-1.5">
                    <Tag v-if="slotProps.data.is_multiple" value="Kelipatan" severity="secondary"
                      class="!text-[8px] !bg-slate-100 !text-slate-500 !border-slate-200 !px-1.5 !py-0.5" />
                    <Tag v-if="slotProps.data.is_stackable" value="Stackable" severity="info"
                      class="!text-[8px] !bg-blue-50 !text-blue-500 !border-blue-100 !px-1.5 !py-0.5" />
                  </div>
                </div>
              </template>
            </Column>
            <Column field="applicable_type" header="Target" style="width: 150px">
              <template #body="slotProps">
                <span class="text-xs font-medium text-slate-600">
                  {{ getApplicableLabel(slotProps.data.applicable_type) }}
                </span>
              </template>
            </Column>
            <Column field="is_active" header="Status" style="width: 120px">
              <template #body="slotProps">
                <div :class="[
                  'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5',
                  slotProps.data.is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100'
                ]">
                  <span
                    :class="['w-1.5 h-1.5 rounded-full', slotProps.data.is_active ? 'bg-emerald-500' : 'bg-rose-500']"></span>
                  {{ slotProps.data.is_active ? 'Aktif' : 'Nonaktif' }}
                </div>
              </template>
            </Column>
            <Column header="Masa Berlaku" style="width: 200px">
              <template #body="slotProps">
                <div class="flex items-center gap-2 text-slate-500 text-xs font-medium">
                  <i class="pi pi-calendar text-[10px]"></i>
                  <span v-if="slotProps.data.start_date || slotProps.data.end_date">
                    {{ formatDate(slotProps.data.start_date) }} - {{ formatDate(slotProps.data.end_date) }}
                  </span>
                  <span v-else class="text-slate-300 italic">Selalu Aktif</span>
                </div>
              </template>
            </Column>
            <Column :exportable="false" style="width: 120px" header="Aksi">
              <template #body="slotProps">
                <div class="flex gap-2">
                  <Button icon="pi pi-pencil" text rounded severity="success" class="hover:bg-emerald-50"
                    @click="editItem(slotProps.data)" v-tooltip.top="'Edit'" />
                  <Button icon="pi pi-trash" text rounded severity="danger" class="hover:bg-rose-50"
                    @click="confirmDelete(slotProps.data)" v-tooltip.top="'Hapus'" />
                </div>
              </template>
            </Column>
          </AppDataTable>
        </template>
      </Card>

      <!-- Enhanced Dialog Structure -->
      <Dialog v-model:visible="itemDialog" :style="{ width: '600px' }"
        :header="item.id ? 'Edit Promosi' : 'Promosi Baru'" :modal="true"
        class="p-fluid !rounded-3xl overflow-hidden shadow-2xl" pt:root:class="!rounded-3xl"
        pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100" pt:content:class="!p-6">
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Side: Basic Info -->
            <div class="space-y-4">
              <div class="flex flex-col gap-2">
                <label for="title" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Judul / Pesan
                  Promosi</label>
                <InputText id="title" v-model.trim="item.title" required="true" autofocus
                  :class="{ 'p-invalid': submitted && !item.title }" placeholder="Contoh: Promo Gila Gajian!"
                  class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500 !p-3" />
                <small class="p-error v-if='submitted && !item.title'">Judul wajib diisi.</small>
              </div>

              <div class="flex flex-col gap-2">
                <label for="type" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Tipe
                  Promosi</label>
                <Select id="type" v-model="item.type" :options="promotionTypes" optionLabel="label" optionValue="value"
                  placeholder="Pilih Tipe" class="!rounded-xl !bg-slate-50 !border-slate-100" />
              </div>

              <div v-if="item.type && item.type !== 'announcement' && item.type !== 'buy_x_get_y'"
                class="flex flex-col gap-2">
                <label for="discount_value" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">
                  {{ item.type === 'discount_percentage' ? 'Persentase Diskon (%)' : 'Nilai Potongan (Rp)' }}
                </label>
                <InputNumber id="discount_value" v-model="item.discount_value" :min="0"
                  :suffix="item.type === 'discount_percentage' ? '%' : ''" class="!rounded-xl overflow-hidden"
                  pt:input:class="!bg-slate-50 !p-3" />
              </div>

              <div class="flex flex-col gap-2">
                <label for="min_purchase"
                  class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Minimal
                  Belanja (Opsional)</label>
                <InputNumber id="min_purchase" v-model="item.min_purchase" :min="0" mode="currency" currency="IDR"
                  locale="id-ID" class="!rounded-xl overflow-hidden" pt:input:class="!bg-slate-50 !p-3" />
              </div>
            </div>

            <!-- Right Side: Rules & Target -->
            <div class="space-y-4">
              <div class="flex flex-col gap-2">
                <label for="applicable_type"
                  class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Target
                  Promosi</label>
                <Select id="applicable_type" v-model="item.applicable_type" :options="applicableTypes"
                  optionLabel="label" optionValue="value" placeholder="Pilih Target"
                  class="!rounded-xl !bg-slate-50 !border-slate-100" />
              </div>

              <div v-if="item.applicable_type === 'products'" class="flex flex-col gap-2">
                <label for="product_ids" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Pilih
                  Produk</label>
                <MultiSelect id="product_ids" v-model="item.product_ids" :options="products" optionLabel="name"
                  optionValue="id" placeholder="Pilih Produk" :filter="true"
                  class="!rounded-xl !bg-slate-50 !border-slate-100" />
              </div>

              <div v-if="item.applicable_type === 'categories'" class="flex flex-col gap-2">
                <label for="category_ids" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Pilih
                  Kategori</label>
                <MultiSelect id="category_ids" v-model="item.category_ids" :options="categories" optionLabel="name"
                  optionValue="id" placeholder="Pilih Kategori" :filter="true"
                  class="!rounded-xl !bg-slate-50 !border-slate-100" />
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                  <label for="priority"
                    class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Prioritas</label>
                  <InputNumber id="priority" v-model="item.priority" :min="0" class="!rounded-xl overflow-hidden"
                    pt:input:class="!bg-slate-50 !p-3" />
                </div>
                <div class="flex flex-col gap-2 justify-center pt-5">
                  <div
                    class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100 transition-colors"
                    :class="{ 'bg-emerald-50 border-emerald-100': item.is_active }">
                    <Checkbox id="is_active" v-model="item.is_active" :binary="true" />
                    <label for="is_active" class="text-sm font-bold cursor-pointer"
                      :class="item.is_active ? 'text-emerald-700' : 'text-slate-500'">Aktif</label>
                  </div>
                </div>
              </div>

              <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-3">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Konfigurasi Lanjutan
                </p>
                <div class="flex items-center gap-3">
                  <Checkbox id="is_multiple" v-model="item.is_multiple" :binary="true" />
                  <label for="is_multiple" class="text-xs font-bold text-slate-600 cursor-pointer">Berlaku Kelipatan
                    (Qty)</label>
                </div>
                <div class="flex items-center gap-3">
                  <Checkbox id="is_stackable" v-model="item.is_stackable" :binary="true" />
                  <label for="is_stackable" class="text-xs font-bold text-slate-600 cursor-pointer">Dapat Ditumpuk
                    (Promo
                    Lain)</label>
                </div>
              </div>

              <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100 space-y-3">
                <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] flex items-center gap-2">
                  <i class="pi pi-calendar-times"></i> Penjadwalan
                </p>
                <div class="grid grid-cols-2 gap-2">
                  <DatePicker v-model="item.start_date" dateFormat="yy-mm-dd" placeholder="Mulai" class="!rounded-lg"
                    pt:input:class="!bg-white !border-blue-100 !p-2 !text-[10px]" />
                  <DatePicker v-model="item.end_date" dateFormat="yy-mm-dd" placeholder="Selesai" class="!rounded-lg"
                    pt:input:class="!bg-white !border-blue-100 !p-2 !text-[10px]" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex flex-col gap-2">
            <label for="content" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Keterangan /
              Konten
              Promosi</label>
            <Textarea id="content" v-model="item.content" rows="2"
              class="!rounded-xl !bg-slate-50 !border-slate-100 focus:!ring-emerald-500 !p-3"
              placeholder="Detail promosi yang akan muncul sebagai subtitle..." />
          </div>
        </div>

        <template #footer>
          <div class="flex gap-3 p-2">
            <Button label="Batal" icon="pi pi-times" text class="!rounded-xl !text-slate-400 hover:!bg-slate-100"
              @click="hideDialog" />
            <Button label="Simpan" icon="pi pi-check" :loading="saving"
              class="!rounded-xl !bg-emerald-600 !border-none !px-8 h-12 font-bold shadow-lg shadow-emerald-100"
              @click="saveItem" />
          </div>
        </template>
      </Dialog>
    </div>
  </AppPage>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppDataTable from '@/components/AppDataTable.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { promotionApi, productApi, categoryApi } from '@/api/master';
import { useToast } from 'primevue/usetoast';
import AppPage from '@/components/layout/AppPage.vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Checkbox from 'primevue/checkbox';
import Textarea from 'primevue/textarea';
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import Tag from 'primevue/tag';
import DatePicker from 'primevue/datepicker';
import Card from 'primevue/card';

const toast = useToast();
const items = ref([]);
const products = ref([]);
const categories = ref([]);
const loading = ref(false);
const itemDialog = ref(false);
const item = ref({});
const submitted = ref(false);
const saving = ref(false);

const promotionTypes = [
  { label: 'Pengumuman (Teks)', value: 'announcement' },
  { label: 'Diskon Persen (%)', value: 'discount_percentage' },
  { label: 'Diskon Tetap (Rp)', value: 'discount_fixed' },
  { label: 'Beli X Gratis Y', value: 'buy_x_get_y' }
];

const applicableTypes = [
  { label: 'Semua Produk', value: 'all' },
  { label: 'Produk Tertentu', value: 'products' },
  { label: 'Kategori Tertentu', value: 'categories' }
];

async function load() {
  loading.value = true;
  try {
    const [promoRes, prodRes, catRes] = await Promise.all([
      promotionApi.getAll({ active_only: 0 }),
      productApi.getAll({ per_page: 1000 }),
      categoryApi.getAll()
    ]);

    items.value = promoRes?.data?.data || [];

    // Handle Laravel pagination structure vs simple collection
    const prodData = prodRes?.data?.data;
    products.value = Array.isArray(prodData) ? prodData : (prodData?.data || []);

    const catData = catRes?.data?.data;
    categories.value = Array.isArray(catData) ? catData : (catData?.data || []);

  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal mengambil data', life: 3000 });
    console.error('Promotion load error:', error);
  } finally {
    loading.value = false;
  }
}

function getTypeLabel(type) {
  const t = promotionTypes.find(x => x.value === type);
  return t ? t.label : type;
}

function getTypeSeverity(type) {
  switch (type) {
    case 'announcement': return 'info';
    case 'discount_percentage': return 'success';
    case 'discount_fixed': return 'warning';
    case 'buy_x_get_y': return 'danger';
    default: return 'secondary';
  }
}

function getApplicableLabel(type) {
  const t = applicableTypes.find(x => x.value === type);
  return t ? t.label : type;
}

function formatDiscount(data) {
  if (data.type === 'discount_percentage') return `${data.discount_value}% OFF`;
  if (data.type === 'discount_fixed') return `Rp ${data.discount_value.toLocaleString()} OFF`;
  return '';
}

function openNew() {
  item.value = {
    is_active: true,
    priority: 0,
    type: 'announcement',
    applicable_type: 'all',
    discount_value: 0,
    min_purchase: 0,
    product_ids: [],
    category_ids: [],
    is_multiple: true,
    is_stackable: false
  };
  submitted.value = false;
  itemDialog.value = true;
}

function hideDialog() {
  itemDialog.value = false;
  submitted.value = false;
}

function editItem(data) {
  item.value = {
    ...data,
    product_ids: data.product_ids || [],
    category_ids: data.category_ids || []
  };
  if (item.value.start_date) item.value.start_date = new Date(item.value.start_date);
  if (item.value.end_date) item.value.end_date = new Date(item.value.end_date);
  itemDialog.value = true;
}

async function saveItem() {
  submitted.value = true;
  if (!item.value.title || !item.value.type) return;

  saving.value = true;
  try {
    const payload = { ...item.value };
    if (payload.start_date instanceof Date) {
      payload.start_date = payload.start_date.toISOString().split('T')[0];
    }
    if (payload.end_date instanceof Date) {
      payload.end_date = payload.end_date.toISOString().split('T')[0];
    }

    if (item.value.id) {
      await promotionApi.update(item.value.id, payload);
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Promosi diperbarui', life: 3000 });
    } else {
      await promotionApi.create(payload);
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Promosi ditambahkan', life: 3000 });
    }
    itemDialog.value = false;
    load();
  } catch (error) {
    toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal menyimpan data', life: 3000 });
  } finally {
    saving.value = false;
  }
}

async function confirmDelete(data) {
  if (confirm('Apakah Anda yakin ingin menghapus promosi ini?')) {
    try {
      await promotionApi.delete(data.id);
      toast.add({ severity: 'success', summary: 'Berhasil', detail: 'Promosi dihapus', life: 3000 });
      load();
    } catch (error) {
      toast.add({ severity: 'error', summary: 'Error', detail: 'Gagal menghapus data', life: 3000 });
    }
  }
}

function formatDate(date) {
  if (!date) return '...';
  const d = new Date(date);
  return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

onMounted(() => {
  load();
});
</script>
