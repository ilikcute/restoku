<template>
  <div class="kitchen-page fixed inset-0 bg-slate-950 flex flex-col overflow-hidden" style="font-family: 'Inter', system-ui, sans-serif;">

    <!-- TOP HEADER -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-3.5 flex items-center justify-between shrink-0 z-20">
      <div class="flex items-center gap-4">
        <button @click="router.push('/dashboard')" class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 flex items-center justify-center transition-all group">
          <i class="pi pi-arrow-left text-slate-400 group-hover:text-white text-sm"></i>
        </button>
        <div>
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            <h1 class="text-base font-extrabold text-white tracking-tight">Kitchen Display System</h1>
          </div>
          <p class="text-[11px] text-slate-500 font-medium mt-0.5">Real-time kitchen order management</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <!-- Clock -->
        <div class="text-right">
          <p class="text-2xl font-black text-white tabular-nums">{{ currentTime }}</p>
          <p class="text-[10px] text-slate-500">{{ currentDate }}</p>
        </div>

        <!-- Station Filter -->
        <div class="flex bg-slate-800 rounded-xl border border-slate-700 p-1 gap-1">
          <button v-for="st in stations" :key="st.id" @click="activeStation = st.id"
            :class="['px-3 py-1.5 rounded-lg text-[11px] font-bold transition-all',
              activeStation === st.id ? 'bg-amber-500 text-slate-900' : 'text-slate-400 hover:text-white']">
            {{ st.label }}
          </button>
        </div>

        <!-- Sound Toggle -->
        <button @click="soundEnabled = !soundEnabled" :class="['w-9 h-9 rounded-xl border flex items-center justify-center transition-all', soundEnabled ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400' : 'bg-slate-800 border-slate-700 text-slate-500']">
          <i :class="soundEnabled ? 'pi pi-volume-up' : 'pi pi-volume-off'" class="text-sm"></i>
        </button>

        <!-- Refresh -->
        <button @click="loadKitchenOrders" :class="['w-9 h-9 rounded-xl border flex items-center justify-center transition-all', 'bg-slate-800 border-slate-700']">
          <i :class="['pi pi-refresh text-sm', loading ? 'pi-spin text-amber-400' : 'text-slate-400']"></i>
        </button>
      </div>
    </header>

    <!-- KITCHEN STATS BAR -->
    <div class="bg-slate-900 border-b border-slate-800 px-6 py-3 flex items-center gap-4 shrink-0">
      <div v-for="stat in kitchenStats" :key="stat.label" class="flex items-center gap-3">
        <div :class="['w-8 h-8 rounded-lg flex items-center justify-center', stat.bg]">
          <i :class="[stat.icon, stat.iconColor, 'text-sm']"></i>
        </div>
        <div>
          <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wider leading-none">{{ stat.label }}</p>
          <p :class="['text-sm font-black mt-0.5', stat.valueColor]">{{ stat.value }}</p>
        </div>
        <div class="w-px h-7 bg-slate-800 ml-2"></div>
      </div>

      <!-- SLA Indicator -->
      <div class="ml-auto flex items-center gap-2">
        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">SLA Target:</span>
        <span class="text-sm font-black text-amber-400">15 min</span>
        <div class="px-2.5 py-1 rounded-lg" :class="avgCookingTime <= 15 ? 'bg-emerald-500/20 border border-emerald-500/30' : 'bg-red-500/20 border border-red-500/30'">
          <span :class="['text-[11px] font-black', avgCookingTime <= 15 ? 'text-emerald-400' : 'text-red-400']">Avg: {{ avgCookingTime }}m</span>
        </div>
      </div>
    </div>

    <!-- MAIN KITCHEN GRID -->
    <div class="flex-1 overflow-auto p-5">
      <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

        <!-- Kitchen Order Card -->
        <div v-for="order in filteredKitchenOrders" :key="order.id"
          :class="['rounded-2xl border overflow-hidden transition-all',
            order.is_urgent ? 'border-red-500/60 shadow-lg shadow-red-500/10' :
            order.status === 'ready' ? 'border-emerald-500/40 shadow-lg shadow-emerald-500/10' :
            'border-slate-700 shadow-lg shadow-black/20']">

          <!-- Card Header -->
          <div :class="['px-4 py-3 flex items-center justify-between',
            order.is_urgent ? 'bg-gradient-to-r from-red-500/20 to-orange-500/10' :
            order.status === 'ready' ? 'bg-gradient-to-r from-emerald-500/20 to-teal-500/10' :
            order.status === 'preparing' ? 'bg-gradient-to-r from-amber-500/15 to-yellow-500/5' :
            'bg-slate-800']">

            <div class="flex items-center gap-2.5">
              <div :class="['w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm',
                order.is_urgent ? 'bg-red-500 text-white' :
                order.status === 'ready' ? 'bg-emerald-500 text-white' :
                order.status === 'preparing' ? 'bg-amber-500 text-slate-900' :
                'bg-slate-700 text-slate-300']">
                {{ order.table_number || 'GO' }}
              </div>
              <div>
                <p class="text-xs font-black text-white">#{{ order.order_number }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span :class="['text-[9px] font-bold px-1.5 py-0.5 rounded-md uppercase', getChannelClass(order.channel)]">{{ order.channel }}</span>
                  <span v-if="order.is_urgent" class="text-[9px] font-black px-1.5 py-0.5 rounded-md bg-red-500 text-white uppercase animate-pulse">URGENT</span>
                </div>
              </div>
            </div>

            <!-- Timer -->
            <div class="text-right">
              <div :class="['text-lg font-black tabular-nums', order.elapsed_mins > 20 ? 'text-red-400' : order.elapsed_mins > 12 ? 'text-amber-400' : 'text-emerald-400']">
                {{ order.elapsed_display }}
              </div>
              <p class="text-[9px] text-slate-500">elapsed</p>
            </div>
          </div>

          <!-- Items List -->
          <div class="bg-slate-900 px-4 py-3 flex-1">
            <div class="space-y-2 max-h-[240px] overflow-y-auto custom-scrollbar">
              <div v-for="(item, idx) in order.items" :key="idx"
                :class="['flex items-center gap-3 p-2.5 rounded-xl transition-all cursor-pointer',
                  item.done ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-slate-800 border border-slate-700 hover:border-slate-600']"
                @click="toggleItemDone(order, idx)">

                <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all',
                  item.done ? 'bg-emerald-500 border-emerald-500' : 'border-slate-600']">
                  <i v-if="item.done" class="pi pi-check text-[9px] text-white font-black"></i>
                </div>

                <div class="flex-1 min-w-0">
                  <p :class="['text-xs font-bold truncate', item.done ? 'text-slate-500 line-through' : 'text-white']">{{ item.name }}</p>
                  <p v-if="item.notes" class="text-[10px] text-amber-400 mt-0.5 truncate italic">{{ item.notes }}</p>
                </div>

                <span :class="['text-xs font-black shrink-0', item.done ? 'text-slate-600' : 'text-slate-300']">×{{ item.quantity }}</span>
              </div>
            </div>

            <!-- Progress -->
            <div class="mt-3">
              <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] text-slate-500 font-bold">{{ order.items.filter(i => i.done).length }}/{{ order.items.length }} items done</span>
                <span class="text-[10px] text-slate-500 font-bold">{{ Math.round(order.items.filter(i => i.done).length / order.items.length * 100) }}%</span>
              </div>
              <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div :class="['h-full rounded-full transition-all duration-500',
                  order.items.filter(i => i.done).length === order.items.length ? 'bg-emerald-500' : 'bg-amber-500']"
                  :style="{width: (order.items.filter(i => i.done).length / order.items.length * 100) + '%'}"></div>
              </div>
            </div>
          </div>

          <!-- Card Footer / Actions -->
          <div class="bg-slate-800/60 px-4 py-3 flex items-center gap-2 border-t border-slate-700">
            <div class="flex-1 text-[10px] text-slate-500 font-semibold">
              {{ order.customer_name || 'Walk-in' }}
            </div>

            <button v-if="order.status === 'pending'" @click="markPreparing(order)"
              class="flex-1 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-900 text-[11px] font-black transition-all">
              Start Prep
            </button>
            <button v-else-if="order.status === 'preparing'" @click="markReady(order)"
              :disabled="order.items.filter(i => i.done).length < order.items.length"
              :class="['flex-1 py-2 rounded-xl text-[11px] font-black transition-all',
                order.items.filter(i => i.done).length === order.items.length
                  ? 'bg-emerald-500 hover:bg-emerald-400 text-white'
                  : 'bg-slate-700 text-slate-500 cursor-not-allowed']">
              Mark Ready
            </button>
            <button v-else-if="order.status === 'ready'" @click="markServed(order)"
              class="flex-1 py-2 rounded-xl bg-blue-500 hover:bg-blue-400 text-white text-[11px] font-black transition-all">
              Served ✓
            </button>
            <div v-else class="flex-1 py-2 rounded-xl bg-slate-700 text-slate-500 text-center text-[11px] font-bold">Completed</div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredKitchenOrders.length === 0" class="col-span-full flex flex-col items-center justify-center py-24 text-center">
          <div class="w-20 h-20 rounded-2xl bg-slate-800 flex items-center justify-center mb-5">
            <i class="pi pi-check-circle text-3xl text-emerald-500"></i>
          </div>
          <h3 class="text-lg font-black text-white mb-2">All caught up! 🎉</h3>
          <p class="text-sm text-slate-500">No pending orders in the kitchen</p>
        </div>

      </div>
    </div>

    <!-- BOTTOM COMPLETED STRIP -->
    <div class="bg-slate-900 border-t border-slate-800 px-6 py-3 flex items-center gap-4 shrink-0">
      <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Recently Completed:</span>
      <div class="flex gap-3 overflow-x-auto hide-scrollbar flex-1">
        <div v-for="ord in completedOrders.slice(0, 8)" :key="'c-' + ord.id"
          class="flex items-center gap-2 px-3 py-1.5 bg-slate-800 rounded-xl border border-slate-700 whitespace-nowrap shrink-0">
          <i class="pi pi-check-circle text-emerald-400 text-[10px]"></i>
          <span class="text-[10px] font-bold text-slate-400">#{{ ord.order_number }}</span>
          <span class="text-[10px] text-slate-500">{{ ord.completed_at }}</span>
        </div>
        <div v-if="completedOrders.length === 0" class="text-[11px] text-slate-600 italic">No completed orders yet today</div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { orderApi } from '@/api/sales';

const router = useRouter();
const loading = ref(false);
const soundEnabled = ref(true);
const activeStation = ref('all');
const allOrders = ref([]);
const currentTime = ref('');
const currentDate = ref('');
let clockInterval = null;
let refreshInterval = null;

const stations = [
  { id: 'all', label: 'All Stations' },
  { id: 'bar', label: 'Bar' },
  { id: 'kitchen', label: 'Kitchen' },
  { id: 'bakery', label: 'Bakery' },
];

// Mock kitchen orders for demo
const mockKitchenOrders = ref([
  {
    id: 1, order_number: 'ORD-0001', status: 'pending', channel: 'dine-in',
    customer_name: 'Budi Santoso', table_number: 'T-03',
    is_urgent: true, station: 'kitchen', elapsed_mins: 2,
    elapsed_display: '02:14',
    items: [
      { name: 'Nasi Goreng Spesial', quantity: 1, notes: 'Extra pedas, no sayur', done: false },
      { name: 'Ayam Bakar', quantity: 1, notes: '', done: false },
      { name: 'Es Teh Manis', quantity: 2, notes: '', done: false },
    ],
  },
  {
    id: 2, order_number: 'ORD-0002', status: 'preparing', channel: 'dine-in',
    customer_name: 'Siti Rahayu', table_number: 'T-05',
    is_urgent: false, station: 'bar', elapsed_mins: 8,
    elapsed_display: '08:32',
    items: [
      { name: 'Cappuccino', quantity: 2, notes: 'Less sugar', done: true },
      { name: 'Matcha Latte', quantity: 1, notes: 'Oat milk', done: false },
      { name: 'Cold Brew', quantity: 1, notes: '', done: false },
    ],
  },
  {
    id: 3, order_number: 'ORD-0003', status: 'preparing', channel: 'take-away',
    customer_name: 'Ahmad Fauzi', table_number: null,
    is_urgent: false, station: 'bakery', elapsed_mins: 5,
    elapsed_display: '05:45',
    items: [
      { name: 'Croissant', quantity: 2, notes: '', done: true },
      { name: 'Cinnamon Roll', quantity: 1, notes: 'Warm please', done: false },
    ],
  },
  {
    id: 4, order_number: 'ORD-0004', status: 'preparing', channel: 'online',
    customer_name: 'Dewi Kusuma', table_number: null,
    is_urgent: true, station: 'kitchen', elapsed_mins: 22,
    elapsed_display: '22:10',
    items: [
      { name: 'Pasta Carbonara', quantity: 1, notes: 'No mushroom', done: false },
      { name: 'Caesar Salad', quantity: 1, notes: '', done: true },
      { name: 'Garlic Bread', quantity: 1, notes: '', done: true },
    ],
  },
  {
    id: 5, order_number: 'ORD-0005', status: 'ready', channel: 'dine-in',
    customer_name: 'Rizky Pratama', table_number: 'T-08',
    is_urgent: false, station: 'kitchen', elapsed_mins: 14,
    elapsed_display: '14:05',
    items: [
      { name: 'Mie Goreng', quantity: 2, notes: '', done: true },
      { name: 'Sate Ayam 10pcs', quantity: 1, notes: '', done: true },
    ],
  },
  {
    id: 6, order_number: 'ORD-0006', status: 'pending', channel: 'dine-in',
    customer_name: 'Maria Santos', table_number: 'T-02',
    is_urgent: false, station: 'bar', elapsed_mins: 1,
    elapsed_display: '01:30',
    items: [
      { name: 'Americano', quantity: 3, notes: '', done: false },
      { name: 'Lemonade', quantity: 1, notes: 'Extra ice', done: false },
    ],
  },
]);

const completedOrders = ref([
  { id: 10, order_number: 'ORD-9991', completed_at: '2m ago' },
  { id: 11, order_number: 'ORD-9992', completed_at: '8m ago' },
  { id: 12, order_number: 'ORD-9993', completed_at: '15m ago' },
  { id: 13, order_number: 'ORD-9994', completed_at: '22m ago' },
]);

// Computed
const filteredKitchenOrders = computed(() => {
  let orders = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  orders = orders.filter(o => o.status !== 'completed' && o.status !== 'cancelled');
  if (activeStation.value !== 'all') {
    orders = orders.filter(o => o.station === activeStation.value);
  }
  // Sort: urgent first, then by elapsed time desc
  return [...orders].sort((a, b) => {
    if (a.is_urgent && !b.is_urgent) return -1;
    if (!a.is_urgent && b.is_urgent) return 1;
    return b.elapsed_mins - a.elapsed_mins;
  });
});

const kitchenStats = computed(() => {
  const orders = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  const active = orders.filter(o => o.status !== 'completed');
  return [
    { label: 'Queue', value: active.length, icon: 'pi pi-list', bg: 'bg-slate-800', iconColor: 'text-slate-400', valueColor: 'text-white' },
    { label: 'Pending', value: active.filter(o => o.status === 'pending').length, icon: 'pi pi-clock', bg: 'bg-amber-500/20', iconColor: 'text-amber-400', valueColor: 'text-amber-400' },
    { label: 'Preparing', value: active.filter(o => o.status === 'preparing').length, icon: 'pi pi-cog', bg: 'bg-blue-500/20', iconColor: 'text-blue-400', valueColor: 'text-blue-400' },
    { label: 'Ready', value: active.filter(o => o.status === 'ready').length, icon: 'pi pi-check-circle', bg: 'bg-emerald-500/20', iconColor: 'text-emerald-400', valueColor: 'text-emerald-400' },
    { label: 'Urgent', value: active.filter(o => o.is_urgent).length, icon: 'pi pi-exclamation-triangle', bg: 'bg-red-500/20', iconColor: 'text-red-400', valueColor: 'text-red-400' },
  ];
});

const avgCookingTime = computed(() => {
  const orders = (allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value).filter(o => o.status === 'preparing');
  if (!orders.length) return 0;
  return Math.round(orders.reduce((s, o) => s + o.elapsed_mins, 0) / orders.length);
});

// Methods
function getChannelClass(channel) {
  const map = {
    'dine-in': 'bg-emerald-500/20 text-emerald-400',
    'take-away': 'bg-blue-500/20 text-blue-400',
    'delivery': 'bg-orange-500/20 text-orange-400',
    'online': 'bg-violet-500/20 text-violet-400',
  };
  return map[channel] || 'bg-slate-700 text-slate-400';
}

function toggleItemDone(order, idx) {
  const list = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  const target = list.find(o => o.id === order.id);
  if (target) {
    target.items[idx].done = !target.items[idx].done;
  }
}

async function markPreparing(order) {
  const list = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  const target = list.find(o => o.id === order.id);
  if (target) target.status = 'preparing';
  try {
    if (order.id && typeof order.id === 'number') {
      await orderApi.updateStatus(order.id, 'preparing');
    }
  } catch (e) {
    console.error('Failed to update kitchen order to preparing:', e);
  }
}

async function markReady(order) {
  const list = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  const target = list.find(o => o.id === order.id);
  if (target) {
    target.status = 'ready';
    target.items.forEach(i => i.done = true);
  }
  try {
    if (order.id && typeof order.id === 'number') {
      await orderApi.updateStatus(order.id, 'ready');
    }
  } catch (e) {
    console.error('Failed to update kitchen order to ready:', e);
  }
}

async function markServed(order) {
  const list = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  const idx = list.findIndex(o => o.id === order.id);
  if (idx !== -1) {
    list.splice(idx, 1);
    completedOrders.value.unshift({ id: order.id, order_number: order.order_number, completed_at: 'Just now' });
  }
  try {
    if (order.id && typeof order.id === 'number') {
      await orderApi.updateStatus(order.id, 'completed');
    }
  } catch (e) {
    console.error('Failed to update kitchen order to completed:', e);
  }
}

function updateClock() {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  currentDate.value = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long' });
}

// Increment elapsed display on active orders
function tickTimers() {
  const list = allOrders.value.length > 0 ? allOrders.value : mockKitchenOrders.value;
  list.forEach(order => {
    if (order.status === 'pending' || order.status === 'preparing') {
      order.elapsed_mins += 1 / 60;
      const totalSecs = Math.floor(order.elapsed_mins * 60);
      const mins = Math.floor(totalSecs / 60);
      const secs = totalSecs % 60;
      order.elapsed_display = `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
      if (order.elapsed_mins > 20) order.is_urgent = true;
    }
  });
}

async function loadKitchenOrders() {
  loading.value = true;
  try {
    const response = await orderApi.getAll({ per_page: 50, status: 'pending,preparing,ready' });
    const data = response?.data?.data;
    if (data?.data?.length > 0) {
      allOrders.value = data.data
        .filter(o => ['pending', 'preparing', 'ready'].includes(o.status))
        .map(o => ({
          ...o,
          channel: o.order_type || 'dine-in',
          station: o.station || 'kitchen',
          is_urgent: false,
          elapsed_mins: Math.floor((Date.now() - new Date(o.created_at)) / 60000),
          elapsed_display: formatElapsed(Math.floor((Date.now() - new Date(o.created_at)) / 60000)),
          items: (o.items || []).map(i => ({
            name: i.product_name,
            quantity: i.quantity,
            notes: i.notes || '',
            done: false,
          })),
        }));
    }
  } catch (e) {
    // Use mock data
  } finally {
    loading.value = false;
  }
}

function formatElapsed(mins) {
  const m = Math.floor(mins);
  return `${String(m).padStart(2, '0')}:00`;
}

onMounted(() => {
  loadKitchenOrders();
  updateClock();
  clockInterval = setInterval(() => {
    updateClock();
    tickTimers();
  }, 1000);
  refreshInterval = setInterval(loadKitchenOrders, 20000);
});

onUnmounted(() => {
  if (clockInterval) clearInterval(clockInterval);
  if (refreshInterval) clearInterval(refreshInterval);
});
</script>

<style scoped>
.kitchen-page {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.custom-scrollbar::-webkit-scrollbar { width: 3px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }

.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
