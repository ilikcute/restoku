<template>
  <Dialog :visible="visible" @update:visible="emit('update:visible', $event)" modal :header="title"
    :style="{ width: '50rem' }" pt:root:class="!rounded-3xl shadow-2xl"
    pt:header:class="!bg-slate-50 !p-6 !border-b !border-slate-100" pt:content:class="!p-0">

    <div class="p-6 space-y-8 max-h-[75vh] overflow-y-auto no-scrollbar">
      <!-- Section: Basic Info -->
      <section class="space-y-4">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-1.5 h-4 bg-emerald-500 rounded-full"></div>
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Informasi Produk</h3>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('common.name')
              }}</label>
            <InputText v-model="form.name"
              class="w-full !rounded-xl !bg-slate-50 !p-3 !border-slate-100 focus:!ring-emerald-500"
              placeholder="Contoh: Nasi Goreng Spesial" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{
              $t('product.short_name') }}</label>
            <InputText v-model="form.short_name"
              class="w-full !rounded-xl !bg-slate-50 !p-3 !border-slate-100 focus:!ring-emerald-500"
              placeholder="Nama singkat di struk" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.code') }}
              (SKU)</label>
            <div class="relative">
              <InputText v-model="form.code"
                class="w-full !rounded-xl !bg-slate-50 !p-3 !border-slate-100 focus:!ring-emerald-500 font-mono font-bold text-emerald-600"
                :placeholder="loadingCode ? 'Menghitung...' : 'Otomatis atau manual'" />
              <i v-if="loadingCode"
                class="pi pi-spin pi-spinner absolute right-3 top-1/2 -translate-y-1/2 text-slate-300"></i>
            </div>
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('common.category')
              }}</label>
            <Select v-model="form.category_id" :options="categories" optionLabel="name" optionValue="id"
              class="w-full !rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('common.brand')
              }}</label>
            <InputText v-model="form.brand_name" class="w-full !rounded-xl !bg-slate-50 !p-3 !border-slate-100"
              :placeholder="$t('common.brand')" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('common.supplier')
              }}</label>
            <Select v-model="form.supplier_id" :options="suppliers" optionLabel="name" optionValue="id"
              class="w-full !rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3" showClear />
          </div>
        </div>
      </section>

      <!-- Section: Pricing -->
      <section class="space-y-4">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-1.5 h-4 bg-orange-500 rounded-full"></div>
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Harga & Pajak Utama</h3>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.cost_price') }}</label>
            <InputNumber v-model="form.cost_price" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-100" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 text-orange-600">{{ $t('product.selling_price') }}</label>
            <InputNumber v-model="form.price" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:input:class="!rounded-xl !bg-orange-50 !p-3 !border-orange-100 !font-bold !text-orange-700" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('common.unit') }}</label>
            <Select v-model="form.unit_id" :options="units" optionLabel="name" optionValue="id" class="w-full !rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.tax_rate') }} (%)</label>
            <InputNumber v-model="form.tax_rate" suffix=" %" class="w-full" pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-100" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.service_charge_rate') }} (%)</label>
            <InputNumber v-model="form.service_charge_rate" suffix=" %" class="w-full" pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-100" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.discount_amount') }}</label>
            <InputNumber v-model="form.discount_amount" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:input:class="!rounded-xl !bg-slate-50 !p-3 !border-slate-100" />
          </div>
        </div>
      </section>

      <!-- Section: Channel Pricing (Ojol & Wholesale) -->
      <section class="space-y-4">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-1.5 h-4 bg-rose-500 rounded-full"></div>
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Harga Multi-Channel</h3>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
          <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-4 shadow-sm">
            <p class="text-[10px] font-black text-rose-600 uppercase tracking-widest italic border-b border-rose-100 pb-2">Harga Online (Ojol)</p>
            <div class="space-y-4">
              <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $t('product.ojol_price') }}</label>
                <InputNumber v-model="form.ojol_price" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:root:class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !text-sm !border-slate-200" />
              </div>
              <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $t('product.ojol_discount') }}</label>
                <InputNumber v-model="form.ojol_discount" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:root:class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !text-sm !border-slate-200" />
              </div>
            </div>
          </div>
          <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-4 shadow-sm">
            <p class="text-[10px] font-black text-blue-600 uppercase tracking-widest italic border-b border-blue-100 pb-2">Harga Grosir (Wholesale)</p>
            <div class="space-y-4">
              <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $t('product.wholesale_price') }}</label>
                <InputNumber v-model="form.wholesale_price" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:root:class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !text-sm !border-slate-200" />
              </div>
              <div class="space-y-1.5">
                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $t('product.wholesale_discount') }}</label>
                <InputNumber v-model="form.wholesale_discount" mode="currency" currency="IDR" locale="id-ID" class="w-full" pt:root:class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !text-sm !border-slate-200" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Section: Inventory -->
      <section class="space-y-4">
        <div class="flex items-center gap-2 mb-2">
          <div class="w-1.5 h-4 bg-blue-500 rounded-full"></div>
          <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Manajemen Stok & Barcode</h3>
        </div>
        <div class="grid gap-6 md:grid-cols-2">
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.barcode') }}</label>
            <InputText v-model="form.barcode" class="w-full !rounded-xl !bg-slate-50 !p-3 !border-slate-100 focus:!ring-blue-500" placeholder="Scan atau input barcode" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('product.stock_type') }}</label>
            <Select v-model="form.stock_type" :options="stockTypes" optionLabel="label" optionValue="value" class="w-full !rounded-xl !bg-slate-50 !border-slate-100" pt:input:class="!p-3" />
          </div>
        </div>
        
        <div class="grid gap-4 md:grid-cols-3 bg-slate-50 p-5 rounded-2xl border border-slate-100">
          <div class="space-y-1.5">
            <label class="text-[9px] font-bold text-slate-400 uppercase">{{ $t('product.minimum_stock') }}</label>
            <InputNumber v-model="form.minimum_stock" class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !border-slate-200" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[9px] font-bold text-slate-400 uppercase">{{ $t('product.maximum_stock') }}</label>
            <InputNumber v-model="form.maximum_stock" class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !border-slate-200" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[9px] font-bold text-slate-400 uppercase">{{ $t('product.reorder_quantity') }}</label>
            <InputNumber v-model="form.reorder_quantity" class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !border-slate-200" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[9px] font-bold text-slate-400 uppercase">{{ $t('product.safety_stock') }}</label>
            <InputNumber v-model="form.safety_stock" class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !border-slate-200" />
          </div>
          <div class="space-y-1.5">
            <label class="text-[9px] font-bold text-slate-400 uppercase">{{ $t('product.lead_time') }} (Hari)</label>
            <InputNumber v-model="form.lead_time" class="w-full" pt:input:class="!w-full !rounded-xl !bg-white !p-3 !border-slate-200" />
          </div>
        </div>
      </section>

      <!-- Section: Additional -->
      <section class="grid gap-6 md:grid-cols-2">
        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{ $t('common.image')
            }}</label>
          <div
            class="p-4 bg-slate-50 rounded-2xl border border-slate-100 border-dashed hover:border-emerald-300 transition-colors">
            <div class="flex items-center gap-4">
              <div class="relative group">
                <img v-if="imagePreview" :src="imagePreview" class="h-20 w-20 object-cover rounded-xl shadow-md" />
                <img v-else-if="form.current_image" :src="getImageUrl(form.current_image)"
                  class="h-20 w-20 object-cover rounded-xl shadow-md" />
                <div v-else class="h-20 w-20 rounded-xl bg-slate-200 flex items-center justify-center text-slate-400">
                  <i class="pi pi-image text-2xl"></i>
                </div>
              </div>
              <div class="flex-1">
                <p class="text-[10px] font-bold text-slate-500 mb-2">Unggah foto produk untuk tampilan menu.</p>
                <input type="file" accept="image/*" @change="onFileChange" class="hidden" ref="fileInput" />
                <Button :label="imagePreview || form.current_image ? 'Ganti Foto' : 'Pilih Foto'" icon="pi pi-upload"
                  size="small" outlined class="!rounded-lg !text-xs" @click="$refs.fileInput.click()" />
              </div>
            </div>
          </div>
        </div>
        <div class="space-y-1.5">
          <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">{{
            $t('common.description') }}</label>
          <textarea v-model="form.description"
            class="w-full !rounded-2xl !bg-slate-50 !p-4 !border-slate-100 focus:!ring-emerald-500 focus:!border-emerald-500 outline-none text-sm text-slate-700 min-h-[110px]"
            placeholder="Tambahkan detail atau keterangan produk..."></textarea>
        </div>
      </section>

      <!-- Status Toggle -->
      <div class="flex items-center gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 transition-colors"
        :class="{ 'bg-emerald-50 border-emerald-100': form.is_active }">
        <Checkbox v-model="form.is_active" binary inputId="activeField" />
        <label for="activeField" class="text-sm font-bold cursor-pointer"
          :class="form.is_active ? 'text-emerald-700' : 'text-slate-500'">Produk ini Aktif dan dapat dijual</label>
      </div>
    </div>

    <template #footer>
      <div class="flex gap-3 p-4 pt-2 border-t border-slate-50">
        <Button :label="$t('common.cancel')" text class="!rounded-xl !text-slate-400 hover:!bg-slate-100"
          @click="emit('update:visible', false)" />
        <Button :label="props.product ? $t('common.edit') : $t('common.save')" icon="pi pi-check" :loading="saving"
          class="!rounded-xl !bg-emerald-600 !border-none !px-10 h-12 font-bold shadow-lg shadow-emerald-100"
          @click="save" />
      </div>
    </template>
  </Dialog>
</template>

<script setup>
import { reactive, ref, watch, computed } from 'vue';
import { useToast } from 'primevue/usetoast';
import { productApi } from '@/api/master';

import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Select from 'primevue/select';
import Checkbox from 'primevue/checkbox';

const props = defineProps({
  visible: { type: Boolean, default: false },
  title: { type: String, default: 'Add Product' },
  product: { type: Object, default: null }, // if null, it's create mode
  categories: { type: Array, default: () => [] },
  suppliers: { type: Array, default: () => [] },
  units: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:visible', 'saved']);

const toast = useToast();
const saving = ref(false);
const loadingCode = ref(false);
const imagePreview = ref(null);
const fileInput = ref(null);

const stockTypes = computed(() => [
  { label: $t('product.trackable'), value: 'trackable' },
  { label: $t('product.untrackable'), value: 'untrackable' }
]);

import { useI18n } from 'vue-i18n';
const { t: $t } = useI18n();

const form = reactive({
  name: '',
  short_name: '',
  code: '',
  category_id: null,
  brand_name: '',
  supplier_id: null,
  unit_id: null,
  stock_type: 'trackable',
  cost_price: 0,
  price: 0,
  discount_amount: 0,
  ojol_price: 0,
  ojol_discount: 0,
  wholesale_price: 0,
  wholesale_discount: 0,
  barcode: '',
  description: '',
  tax_rate: 0,
  service_charge_rate: 0,
  is_active: true,
  minimum_stock: 0,
  maximum_stock: 0,
  reorder_quantity: 0,
  safety_stock: 0,
  lead_time: 0,
  imageFile: null,
  current_image: null
});

async function fetchNextCode() {
  loadingCode.value = true;
  try {
    const response = await productApi.getNextCode();
    if (response?.data?.data?.next_code) {
      form.code = response.data.data.next_code;
    }
  } catch (error) {
    console.error('Failed to fetch next code:', error);
  } finally {
    loadingCode.value = false;
  }
}

function getImageUrl(path) {
  if (!path) return '';
  const baseUrl = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
    ? 'http://restoku.test'
    : window.location.origin;
  return `${baseUrl}/storage/${path}`;
}

function onFileChange(event) {
  const file = event.target.files[0];
  if (file) {
    form.imageFile = file;
    imagePreview.value = URL.createObjectURL(file);
  } else {
    form.imageFile = null;
    imagePreview.value = null;
  }
}

function resetForm() {
  imagePreview.value = null;
  if (fileInput.value) fileInput.value.value = '';

  Object.assign(form, {
    name: '',
    short_name: '',
    code: '',
    category_id: null,
    brand_name: '',
    supplier_id: null,
    unit_id: null,
    stock_type: 'trackable',
    cost_price: 0,
    price: 0,
    discount_amount: 0,
    ojol_price: 0,
    ojol_discount: 0,
    wholesale_price: 0,
    wholesale_discount: 0,
    barcode: '',
    description: '',
    tax_rate: 0,
    service_charge_rate: 0,
    is_active: true,
    minimum_stock: 0,
    maximum_stock: 0,
    reorder_quantity: 0,
    safety_stock: 0,
    lead_time: 0,
    imageFile: null,
    current_image: null
  });
}

// Watch for visible prop to initialize form
watch(() => props.visible, (newVal) => {
  if (newVal) {
    resetForm();
    if (props.product) {
      Object.assign(form, {
        name: props.product.name,
        short_name: props.product.short_name || '',
        code: props.product.code || '',
        category_id: props.product.category?.id,
        brand_name: props.product.brand_name || '',
        supplier_id: props.product.supplier?.id || null,
        unit_id: props.product.unit?.id,
        stock_type: props.product.stock_type,
        cost_price: props.product.cost_price,
        price: props.product.price,
        discount_amount: props.product.discount_amount || 0,
        ojol_price: props.product.ojol_price || 0,
        ojol_discount: props.product.ojol_discount || 0,
        wholesale_price: props.product.wholesale_price || 0,
        wholesale_discount: props.product.wholesale_discount || 0,
        barcode: props.product.barcode || '',
        description: props.product.description || '',
        tax_rate: props.product.tax_rate || 0,
        service_charge_rate: props.product.service_charge_rate || 0,
        is_active: props.product.is_active !== false,
        minimum_stock: props.product.minimum_stock || 0,
        maximum_stock: props.product.maximum_stock || 0,
        reorder_quantity: props.product.reorder_quantity || 0,
        safety_stock: props.product.safety_stock || 0,
        lead_time: props.product.lead_time || 0,
        current_image: props.product.image
      });
    } else {
      // Create mode: Auto-fetch next SKU
      fetchNextCode();
    }
  }
});

async function save() {
  saving.value = true;
  try {
    const payload = new FormData();
    payload.append('name', form.name);
    payload.append('short_name', form.short_name || '');
    payload.append('code', form.code || '');
    payload.append('category_id', form.category_id);
    if (form.brand_name) payload.append('brand_name', form.brand_name);
    if (form.supplier_id) payload.append('supplier_id', form.supplier_id);
    payload.append('unit_id', form.unit_id);
    payload.append('stock_type', form.stock_type);
    payload.append('cost_price', form.cost_price || 0);
    payload.append('price', form.price || 0);
    payload.append('discount_amount', form.discount_amount || 0);
    payload.append('ojol_price', form.ojol_price || 0);
    payload.append('ojol_discount', form.ojol_discount || 0);
    payload.append('wholesale_price', form.wholesale_price || 0);
    payload.append('wholesale_discount', form.wholesale_discount || 0);
    payload.append('barcode', form.barcode || '');
    payload.append('description', form.description || '');
    payload.append('tax_rate', form.tax_rate || 0);
    payload.append('service_charge_rate', form.service_charge_rate || 0);
    payload.append('is_active', form.is_active ? 1 : 0);
    payload.append('minimum_stock', form.minimum_stock || 0);
    payload.append('maximum_stock', form.maximum_stock || 0);
    payload.append('reorder_quantity', form.reorder_quantity || 0);
    payload.append('safety_stock', form.safety_stock || 0);
    payload.append('lead_time', form.lead_time || 0);

    if (form.imageFile) {
      payload.append('image', form.imageFile);
    }

    if (props.product && props.product.id) {
      await productApi.update(props.product.id, payload);
    } else {
      await productApi.create(payload);
    }

    toast.add({ severity: 'success', summary: $t('common.save'), life: 2000 });
    emit('saved');
    emit('update:visible', false);
  } catch (error) {
    toast.add({ severity: 'error', summary: $t('common.save'), detail: error?.response?.data?.message || 'Invalid payload', life: 3000 });
  } finally {
    saving.value = false;
  }
}
</script>
