<template>
  <div class="min-h-screen bg-slate-50 flex flex-col max-w-md mx-auto shadow-xl">
    <!-- Header -->
    <header class="bg-primary-600 text-white p-4 sticky top-0 z-10 shadow-md">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-bold">Restoku Digital Menu</h1>
        <div class="bg-primary-500/50 px-3 py-1 rounded-full text-xs font-bold backdrop-blur-sm border border-white/20">
          Table: {{ tableNumber || '-' }}
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-4 space-y-6">
      <!-- Search & Category -->
      <div class="space-y-3">
        <InputText v-model="search" class="w-full" placeholder="Cari menu favoritmu..." />
        <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
          <Button 
            v-for="cat in categories" 
            :key="cat" 
            :label="cat" 
            :severity="selectedCategory === cat ? 'primary' : 'secondary'"
            size="small"
            rounded
            @click="selectedCategory = cat"
          />
        </div>
      </div>

      <!-- Product List -->
      <div class="grid gap-4">
        <div v-for="product in filteredProducts" :key="product.id" class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 flex gap-3">
          <img :src="product.image_url" class="w-20 h-20 rounded-xl object-cover bg-slate-100" />
          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-slate-800 truncate">{{ product.name }}</h3>
            <p class="text-xs text-slate-500 line-clamp-1 mb-2">{{ product.description }}</p>
            <div class="flex justify-between items-center">
              <span class="font-bold text-primary-600">Rp {{ money(product.price) }}</span>
              <Button icon="pi pi-plus" rounded size="small" @click="addToCart(product)" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Floating Cart Button -->
    <div v-if="cart.length > 0" class="fixed bottom-6 left-1/2 -translate-x-1/2 w-full max-w-[calc(100%-2rem)] md:max-w-[380px] z-20">
      <Button 
        class="w-full h-14 shadow-2xl rounded-2xl flex justify-between px-6" 
        @click="showCart = true"
      >
        <div class="flex items-center gap-3">
          <i class="pi pi-shopping-basket text-xl"></i>
          <span class="font-bold">{{ cartItemsCount }} Items</span>
        </div>
        <span class="font-bold">Rp {{ money(cartTotal) }}</span>
      </Button>
    </div>

    <!-- Cart Modal -->
    <Dialog v-model:visible="showCart" header="Pesanan Saya" modal class="w-[90%] max-w-md">
      <div class="space-y-4">
        <div v-for="item in cart" :key="item.id" class="flex gap-3 items-start pb-4 border-b border-slate-100">
          <div class="flex-1">
            <div class="font-bold">{{ item.name }}</div>
            <div class="text-sm text-slate-500">Rp {{ money(item.price) }}</div>
            <div class="mt-2">
              <InputText v-model="item.notes" class="w-full text-xs p-2" placeholder="Catatan (misal: pedas, tanpa sayur)" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <Button icon="pi pi-minus" text rounded size="small" @click="updateQty(item, -1)" />
            <span class="font-bold w-4 text-center">{{ item.qty }}</span>
            <Button icon="pi pi-plus" text rounded size="small" @click="updateQty(item, 1)" />
          </div>
        </div>

        <div class="p-4 bg-slate-50 rounded-xl space-y-2 border border-slate-100">
          <div class="flex justify-between text-sm">
            <span class="text-slate-600">Subtotal</span>
            <span>Rp {{ money(cartSubtotal) }}</span>
          </div>
          <div v-if="cartServiceCharge > 0" class="flex justify-between text-sm">
            <span class="text-slate-600">Service Charge</span>
            <span>Rp {{ money(cartServiceCharge) }}</span>
          </div>
          <div v-if="cartTax > 0" class="flex justify-between text-sm">
            <span class="text-slate-600">Pajak (PPN)</span>
            <span>Rp {{ money(cartTax) }}</span>
          </div>
          <div v-if="cartRounding > 0" class="flex justify-between text-sm">
            <span class="text-slate-600">Pembulatan</span>
            <span>Rp {{ money(cartRounding) }}</span>
          </div>
          <div class="flex justify-between font-bold text-primary-600 pt-2 border-t border-slate-200">
            <span>Total Bayar</span>
            <span>Rp {{ money(cartTotal) }}</span>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-2">
          <div class="space-y-2">
            <label class="text-sm font-medium">Nomor Meja</label>
            <InputText v-model="tableNumber" class="w-full" placeholder="No. Meja" />
          </div>
          <div class="space-y-2">
            <label class="text-sm font-medium">Nama Pemesan</label>
            <InputText v-model="customerName" class="w-full" placeholder="Nama Anda" />
          </div>
        </div>

        <Button label="Selesai & Tampilkan QR" class="w-full mt-4" :loading="saving" @click="submitOrder" />
      </div>
    </Dialog>

    <!-- Success Modal with QR -->
    <Dialog v-model:visible="showQR" header="Tunjukkan ke Kasir" modal :closable="false" class="w-[90%] max-w-md">
      <div class="text-center py-6 space-y-6">
        <p class="text-slate-600">Berikan kode ini ke kasir untuk memproses pesananmu:</p>
        
        <div class="bg-white p-4 rounded-3xl shadow-inner inline-block mx-auto border-2 border-primary-100">
          <!-- QR Code placeholder using external API -->
          <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${orderToken}`" class="w-48 h-48" alt="QR Code" />
          <div class="mt-4 font-mono font-bold text-2xl tracking-widest text-primary-600">{{ orderToken }}</div>
        </div>

        <div class="bg-primary-50 p-4 rounded-2xl">
          <p class="text-sm font-medium text-primary-700">Meja: {{ tableNumber || '-' }} | Atas Nama: {{ customerName }}</p>
          <p class="text-xs text-primary-500 mt-1">Total Sementara: Rp {{ money(cartTotal) }}</p>
        </div>

        <Button label="Buat Pesanan Baru" severity="secondary" text @click="resetAll" />
      </div>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { publicApi } from '@/api/public';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';

const products = ref([]);
const categories = ref(['Semua']);
const selectedCategory = ref('Semua');
const search = ref('');
const cart = ref([]);
const showCart = ref(false);
const showQR = ref(false);
const saving = ref(false);
const orderToken = ref('');
const customerName = ref('');
const tableNumber = ref('');
const tenantId = ref(null);

onMounted(() => {
  // Get tenant_id and table number from URL query (?tenant_id=2&table=5)
  const urlParams = new URLSearchParams(window.location.search);
  tenantId.value = urlParams.get('tenant_id') || '1'; // Default to 1 based on DB data
  tableNumber.value = urlParams.get('table') || '';
  loadMenu();
});

async function loadMenu() {
  try {
    const response = await publicApi.getMenu({ tenant_id: tenantId.value });
    // Handle nested data: { data: { data: [] } } from Resource Collection
    const resultData = response.data.data;
    products.value = Array.isArray(resultData) ? resultData : (resultData.data || []);
    
    const cats = [...new Set(products.value.map(p => p.category?.name))].filter(Boolean);
    categories.value = ['Semua', ...cats];
  } catch (error) {
    console.error('Failed to load menu via publicApi', error);
    if (error.response) {
      console.error('Response data:', error.response.data);
      console.error('Response status:', error.response.status);
    }
    
    // Fallback: try native fetch
    try {
      console.log('Attempting fallback fetch...');
      const apiUrl = (import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api/v1') + '/catalog';
      const response = await fetch(apiUrl);
      const result = await response.json();
      if (result.status === 'success') {
        products.value = result.data;
        const cats = [...new Set(products.value.map(p => p.category?.name))].filter(Boolean);
        categories.value = ['Semua', ...cats];
      }
    } catch (fetchError) {
      console.error('Fallback fetch also failed', fetchError);
    }
  }
}

const filteredProducts = computed(() => {
  return products.value.filter(p => {
    const matchCat = selectedCategory.value === 'Semua' || p.category?.name === selectedCategory.value;
    const matchSearch = p.name.toLowerCase().includes(search.value.toLowerCase());
    return matchCat && matchSearch;
  });
});

const cartItemsCount = computed(() => cart.value.reduce((sum, item) => sum + item.qty, 0));
const cartSubtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.qty), 0);
});

const cartServiceCharge = computed(() => {
  return cart.value.reduce((sum, item) => {
    const subtotal = item.price * item.qty;
    return sum + (subtotal * (item.service_charge_rate || 0) / 100);
  }, 0);
});

const cartTax = computed(() => {
  return cart.value.reduce((sum, item) => {
    const subtotal = item.price * item.qty;
    return sum + (subtotal * (item.tax_rate || 0) / 100);
  }, 0);
});

const cartTotalBeforeRounding = computed(() => {
  return cartSubtotal.value + cartServiceCharge.value + cartTax.value;
});

const cartRounding = computed(() => {
  const total = cartTotalBeforeRounding.value;
  const remainder = total % 1000;
  return remainder > 0 ? 1000 - remainder : 0;
});

const cartTotal = computed(() => {
  return cartTotalBeforeRounding.value + cartRounding.value;
});

function addToCart(product) {
  const existing = cart.value.find(i => i.id === product.id);
  if (existing) {
    existing.qty++;
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      tax_rate: product.tax_rate || 0,
      service_charge_rate: product.service_charge_rate || 0,
      qty: 1,
      notes: ''
    });
  }
}

function updateQty(item, delta) {
  item.qty += delta;
  if (item.qty <= 0) {
    cart.value = cart.value.filter(i => i.id !== item.id);
  }
}

async function submitOrder() {
  if (!customerName.value) {
    alert('Masukkan namamu dulu ya!');
    return;
  }
  
  saving.value = true;
  try {
    const payload = {
      tenant_id: tenantId.value,
      customer_name: customerName.value,
      table_number: tableNumber.value,
      items: cart.value.map(i => ({
        product_id: i.id,
        quantity: i.qty,
        notes: i.notes
      }))
    };
    
    const response = await publicApi.createOrder(payload);
    orderToken.value = response.data.data.token;
    showCart.value = false;
    showQR.value = true;
  } catch (error) {
    alert('Gagal mengirim pesanan. Coba lagi.');
  } finally {
    saving.value = false;
  }
}

function resetAll() {
  cart.value = [];
  customerName.value = '';
  showQR.value = false;
}

function money(val) {
  return Number(val).toLocaleString('id-ID');
}
</script>


