<template>
  <div class="min-h-screen bg-slate-900 text-white flex flex-col font-sans selection:bg-primary-500/30">
    <!-- Header / Branding -->
    <header class="p-8 flex justify-between items-center bg-slate-800/50 backdrop-blur-md border-b border-white/5">
      <div class="flex items-center gap-4">
        <div class="w-16 h-16 bg-slate-700/50 rounded-2xl flex items-center justify-center overflow-hidden border border-white/10 shadow-xl">
          <img v-if="tenant.logo_url" :src="tenant.logo_url" class="w-full h-full object-contain p-2" />
          <i v-else class="pi pi-shopping-bag text-3xl text-primary-500"></i>
        </div>
        <div>
          <h1 class="text-3xl font-black tracking-tight text-white uppercase italic leading-none">{{ tenant.name || 'Restoku POS' }}</h1>
          <p class="text-[10px] text-slate-400 uppercase tracking-[0.2em] font-bold mt-2">Customer Experience Display</p>
        </div>
      </div>
      <div class="text-right">
        <p class="text-4xl font-mono font-black text-primary-400">{{ currentTime }}</p>
        <p class="text-[10px] text-slate-500 uppercase font-bold tracking-widest">{{ currentDate }}</p>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex overflow-hidden p-8 gap-8">
      <!-- Left: Cart Items -->
      <div class="flex-[1.5] flex flex-col bg-slate-800/30 rounded-3xl border border-white/5 overflow-hidden backdrop-blur-sm relative">
        <div class="p-6 border-b border-white/5 flex justify-between items-center bg-white/[0.02]">
          <h2 class="text-xl font-bold flex items-center gap-3">
            <i class="pi pi-list text-primary-500"></i>
            Daftar Belanja
          </h2>
          <span class="px-4 py-1 bg-primary-500/10 text-primary-400 rounded-full text-xs font-black uppercase tracking-tighter border border-primary-500/20">
            {{ cart.length }} Items
          </span>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4 no-scrollbar">
          <TransitionGroup name="list">
            <div v-for="item in cart" :key="item.id" class="flex items-center gap-6 p-4 rounded-2xl bg-white/[0.03] border border-white/5 hover:bg-white/[0.05] transition-all">
              <div class="w-16 h-16 rounded-xl bg-slate-700/50 flex items-center justify-center overflow-hidden border border-white/10">
                <img v-if="item.image_url" :src="item.image_url" class="w-full h-full object-cover" />
                <i v-else class="pi pi-image text-2xl text-slate-500"></i>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold truncate">{{ item.name }}</h3>
                <p class="text-sm text-slate-400">Rp {{ money(item.price) }} x {{ item.qty }}</p>
              </div>
              <div class="text-right">
                <p class="text-xl font-black text-white">Rp {{ money(item.price * item.qty) }}</p>
              </div>
            </div>
          </TransitionGroup>

          <div v-if="!cart.length" class="h-full flex flex-col items-center justify-center text-slate-500 space-y-4 py-20">
            <div class="w-24 h-24 rounded-full bg-slate-800 flex items-center justify-center animate-pulse">
              <i class="pi pi-shopping-cart text-5xl"></i>
            </div>
            <p class="text-xl font-medium italic opacity-50">Menunggu pesanan...</p>
          </div>
        </div>
      </div>

      <!-- Right: Summary & Dynamic Content -->
      <div class="flex-1 flex flex-col gap-8">
        <!-- Summary Card -->
        <div class="bg-gradient-to-br from-primary-600 to-primary-800 rounded-3xl p-8 shadow-2xl shadow-primary-900/40 relative overflow-hidden">
          <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-32 translate-x-32 blur-3xl"></div>
          
          <div class="relative z-10 space-y-6">
            <h3 class="text-sm font-black uppercase tracking-[0.3em] text-primary-200">Total Pembayaran</h3>
            
            <div class="space-y-4">
              <div class="flex justify-between items-center text-primary-100/60 font-medium">
                <span>Subtotal</span>
                <span>Rp {{ money(subtotal) }}</span>
              </div>
              <div v-if="tax > 0" class="flex justify-between items-center text-primary-100/60 font-medium">
                <span>Pajak & Layanan</span>
                <span>Rp {{ money(tax + serviceCharge) }}</span>
              </div>
              <div class="pt-6 border-t border-white/10">
                <div class="flex justify-between items-end">
                  <span class="text-sm font-bold text-primary-200 uppercase">Grand Total</span>
                  <div class="text-right">
                    <p class="text-5xl font-black leading-none tracking-tighter">Rp {{ money(total) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dynamic Message / QR / Promo -->
        <div class="flex-1 bg-slate-800/50 rounded-3xl border border-white/5 p-8 flex flex-col items-center justify-center text-center relative overflow-hidden group">
          <div v-if="status === 'idle'" class="space-y-6">
            <div class="w-32 h-32 mx-auto rounded-full bg-slate-700/30 flex items-center justify-center border border-white/5 relative">
              <i class="pi pi-sparkles text-5xl text-primary-500 animate-pulse"></i>
              <div class="absolute inset-0 rounded-full border-2 border-primary-500/20 scale-125 animate-ping"></div>
            </div>
            <div>
              <h4 class="text-2xl font-black mb-2">Selamat Datang!</h4>
              <p class="text-slate-400 leading-relaxed">Nikmati hidangan terbaik kami yang dibuat dengan bahan pilihan berkualitas tinggi.</p>
            </div>
          </div>

          <div v-else-if="status === 'paying'" class="space-y-6">
             <div class="bg-white p-4 rounded-2xl shadow-xl inline-block mb-4">
                <div class="w-48 h-48 bg-slate-100 flex items-center justify-center text-slate-400 italic">
                  <i class="pi pi-qrcode text-8xl text-slate-800"></i>
                </div>
             </div>
             <div>
                <h4 class="text-2xl font-black text-emerald-400">Silakan Bayar</h4>
                <p class="text-slate-400">Scan QRIS di atas atau berikan uang tunai kepada kasir.</p>
             </div>
          </div>

          <div v-else-if="status === 'success'" class="space-y-6">
             <div class="w-32 h-32 mx-auto rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30">
                <i class="pi pi-check-circle text-6xl text-emerald-500"></i>
             </div>
             <div>
                <h4 class="text-3xl font-black text-emerald-400">Terima Kasih!</h4>
                <p class="text-slate-400">Pembayaran berhasil. Silakan tunggu pesanan Anda.</p>
             </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer / Marquee -->
    <footer class="h-16 bg-slate-950 border-t border-white/5 flex items-center px-8">
      <div class="flex items-center gap-4 w-full">
        <span class="px-3 py-1 bg-red-500 text-white text-[10px] font-black rounded uppercase tracking-widest animate-pulse">Hot Promo</span>
        <div class="overflow-hidden flex-1 relative h-6">
          <div v-if="promotions.length" class="absolute whitespace-nowrap animate-marquee flex gap-12 text-sm font-medium text-slate-500 italic">
            <span v-for="(promo, idx) in [...promotions, ...promotions]" :key="idx">{{ promo.title }}</span>
          </div>
          <div v-else class="absolute whitespace-nowrap animate-marquee flex gap-12 text-sm font-medium text-slate-500 italic">
            <span>Selamat Datang di {{ tenant.name }}! Nikmati hidangan lezat kami.</span>
            <span>Selamat Datang di {{ tenant.name }}! Nikmati hidangan lezat kami.</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { promotionApi } from '@/api/master';

const authStore = useAuthStore();
const tenant = computed(() => authStore.user?.relationships?.tenant?.data?.attributes || {});

const cart = ref([]);
const promotions = ref([]);
const subtotal = ref(0);
const tax = ref(0);
const serviceCharge = ref(0);
const total = ref(0);
const status = ref('idle'); // idle, paying, success
const currentTime = ref('');
const currentDate = ref('');

let channel = null;

function money(val) {
  return Number(val || 0).toLocaleString('id-ID');
}

function updateClock() {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
}

async function fetchPromotions() {
  try {
    const response = await promotionApi.getAll({ active_only: 1 });
    promotions.value = response?.data?.data || [];
  } catch (error) {
    console.error('Failed to fetch promotions', error);
  }
}

onMounted(() => {
  updateClock();
  fetchPromotions();
  setInterval(updateClock, 60000);
  setInterval(fetchPromotions, 300000); // Refresh every 5 minutes

  channel = new BroadcastChannel('pos_customer_display');
  channel.onmessage = (event) => {
    const { type, data } = event.data;
    if (type === 'update_cart') {
      cart.value = data.cart;
      subtotal.value = data.subtotal;
      tax.value = data.tax;
      serviceCharge.value = data.serviceCharge;
      total.value = data.total;
      status.value = data.cart.length > 0 ? 'active' : 'idle';
    } else if (type === 'payment_start') {
      status.value = 'paying';
    } else if (type === 'payment_success') {
      status.value = 'success';
      setTimeout(() => {
        if (status.value === 'success') status.value = 'idle';
      }, 5000);
    } else if (type === 'reset') {
      cart.value = [];
      subtotal.value = 0;
      tax.value = 0;
      serviceCharge.value = 0;
      total.value = 0;
      status.value = 'idle';
    }
  };

  // Request initial data in case POS is already open
  channel.postMessage({ type: 'request_sync' });
});

onUnmounted(() => {
  if (channel) channel.close();
});
</script>


