<template>
  <aside :class="[
    'fixed inset-y-0 left-0 z-50 flex flex-col transform bg-white shadow-xl lg:static lg:shadow-none lg:border-r lg:border-slate-200 transition-all duration-300',
    modelValue ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    isCollapsed ? 'w-20' : 'w-64'
  ]">
    <!-- Logo Section -->
    <div class="flex h-20 shrink-0 items-center justify-between px-4 pt-4">
      <div v-if="!isCollapsed" class="flex items-center gap-3 overflow-hidden">
        <img :src="tenant.logo_url || '/images/logo-restoku.png'" alt="Restoku"
          class="h-10 w-10 object-contain rounded-lg">
        <div class="flex flex-col">
          <span class="text-lg font-extrabold text-slate-900 tracking-tight leading-tight">{{ tenant.name || 'Restoku'
            }}</span>
          <span class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold">Dashboard</span>
        </div>
      </div>
      <button @click="toggleSidebar" class="p-2 rounded-xl hover:bg-slate-100 text-slate-500 transition-colors mx-auto">
        <i :class="['pi', isCollapsed ? 'pi-align-left' : 'pi-bars', 'text-xl']"></i>
      </button>
    </div>

    <!-- Navigation Section -->
    <nav class="flex-1 space-y-2 overflow-y-auto px-4 py-6 mt-4 pb-24">
      <div v-for="group in visibleGroups" :key="group.label" class="space-y-1">

        <!-- Group Header (Expandable) -->
        <button v-if="group.label !== 'common.dashboard'" @click="toggleGroup(group.label)" :class="[
          'w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-xl text-sm font-bold transition-all duration-200',
          isGroupOpen(group.label) ? 'text-slate-900 bg-slate-50' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-50'
        ]">
          <div class="flex items-center gap-3 overflow-hidden">
            <i :class="[group.icon || 'pi pi-folder', 'text-lg']"></i>
            <span v-if="!isCollapsed" class="whitespace-nowrap uppercase tracking-wider text-[11px]">{{ $t(group.label)
              }}</span>
          </div>
          <i v-if="!isCollapsed"
            :class="['pi pi-chevron-down text-[10px] transition-transform duration-300', isGroupOpen(group.label) ? 'rotate-180' : '']"></i>
        </button>

        <!-- Direct Link (for Dashboard) -->
        <router-link v-else to="/dashboard" :class="[
          'flex items-center gap-4 rounded-xl py-3 px-4 text-sm font-semibold transition-all duration-200 group',
          route.path === '/dashboard' ? 'bg-emerald-50 text-emerald-600 shadow-sm' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
        ]">
          <i class="pi pi-home text-lg" />
          <span v-if="!isCollapsed">{{ $t('common.overview') }}</span>
        </router-link>

        <!-- Submenu Items -->
        <div v-if="group.label === 'common.dashboard' || isGroupOpen(group.label)"
          :class="['space-y-1 transition-all duration-300 overflow-hidden', group.label !== 'common.dashboard' && !isCollapsed ? 'pl-4 border-l-2 border-slate-100 ml-6 mt-1' : '']">
          <template v-if="group.label !== 'common.dashboard'">
            <component :is="item.external ? 'a' : 'router-link'" v-for="item in group.items" :key="item.to"
              :to="item.external ? undefined : item.to" :href="item.external ? item.to : undefined"
              :target="item.external ? '_blank' : undefined" :class="[
                'flex items-center gap-4 rounded-xl py-2.5 text-sm font-medium transition-all duration-200 group relative',
                isCollapsed ? 'justify-center px-0' : 'px-4',
                !item.external && (route.path === item.to || route.path.startsWith(item.to + '/'))
                  ? 'text-emerald-600 font-bold'
                  : 'text-slate-500 hover:text-slate-900'
              ]" @click="item.external ? null : $emit('update:modelValue', false)">
              <i v-if="isCollapsed"
                :class="[item.icon, 'text-lg transition-transform duration-200 group-hover:scale-110']" />
              <span v-if="!isCollapsed" class="whitespace-nowrap">{{ $t(item.label) }}</span>

              <!-- Tooltip for collapsed state -->
              <div v-if="isCollapsed"
                class="absolute left-full ml-4 px-3 py-1.5 bg-slate-800 text-white text-xs font-bold rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50 shadow-lg border border-slate-700 pointer-events-none">
                {{ $t(item.label) }}
              </div>
            </component>
          </template>
        </div>
      </div>
    </nav>

    <!-- Footer Section -->
    <div class="mt-auto p-4 text-xs text-slate-400 border-t border-slate-100 flex justify-center" v-if="isCollapsed">
      <i class="pi pi-info-circle text-lg"></i>
    </div>
    <div class="mt-auto p-6 text-xs text-slate-400 border-t border-slate-100" v-else>
      <p class="font-semibold text-slate-500 mb-1">Restoku POS v2.0</p>
      <p>© 2026 All Rights Reserved</p>
    </div>
  </aside>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
  modelValue: { type: Boolean, default: false }
});

const emit = defineEmits(['update:modelValue']);

const route = useRoute();
const authStore = useAuthStore();
const permissions = computed(() => authStore.user?.attributes?.permissions || []);
const tenant = computed(() => authStore.user?.relationships?.tenant?.data?.attributes || {});
const isCollapsed = ref(false);

// State for open groups
const openGroups = ref(new Set());

const toggleSidebar = () => {
  isCollapsed.value = !isCollapsed.value;
  if (isCollapsed.value) {
    openGroups.value.clear(); // Close all when collapsed
  } else {
    autoExpandActiveGroup();
  }
};

const toggleGroup = (label) => {
  if (isCollapsed.value) {
    isCollapsed.value = false; // Auto expand sidebar if group clicked
  }

  if (openGroups.value.has(label)) {
    openGroups.value.delete(label);
  } else {
    // Optional: close other groups (Accordion style)
    // openGroups.value.clear();
    openGroups.value.add(label);
  }
};

const isGroupOpen = (label) => openGroups.value.has(label);

const menuGroups = [
  { label: 'common.dashboard', icon: 'pi pi-home', permission: 'view-dashboard', items: [{ to: '/dashboard', icon: 'pi pi-home', label: 'common.overview' }] },
  {
    label: 'common.master_data', icon: 'pi pi-database',
    permission: 'view-master-data',
    items: [
      { to: '/master/products', icon: 'pi pi-box', label: 'sidebar.products', permission: 'view-products' },
      { to: '/master/categories', icon: 'pi pi-tags', label: 'sidebar.categories', permission: 'view-categories' },
      { to: '/master/units', icon: 'pi pi-sliders-h', label: 'sidebar.units', permission: 'view-units' },
      { to: '/master/suppliers', icon: 'pi pi-truck', label: 'sidebar.suppliers', permission: 'view-suppliers' },
      { to: '/master/customers', icon: 'pi pi-user', label: 'sidebar.customers', permission: 'view-customers' }
    ]
  },
  {
    label: 'common.inventory', icon: 'pi pi-warehouse',
    permission: 'view-inventory',
    items: [
      { to: '/inventory/stocks', icon: 'pi pi-warehouse', label: 'sidebar.stock_levels', permission: 'view-stocks' },
      { to: '/inventory/movements', icon: 'pi pi-sync', label: 'sidebar.movements', permission: 'view-stock-movements' },
      { to: '/inventory/adjustments', icon: 'pi pi-pencil', label: 'sidebar.adjustments', permission: 'view-stock-adjustments' }
    ]
  },
  {
    icon: 'pi pi-shopping-cart',
    label: 'common.sales',
    permission: 'view-sales',
    items: [
      { to: '/sales/shifts', icon: 'pi pi-clock', label: 'sidebar.shift_manager', permission: 'view-shifts' },
      { to: '/sales/pos', icon: 'pi pi-calculator', label: 'sidebar.pos', permission: 'view-pos' },
      { to: '/sales/orders', icon: 'pi pi-list', label: 'sidebar.orders', permission: 'view-orders' },
      { to: '/sales/returns', icon: 'pi pi-plus-circle', label: 'sidebar.create_return', permission: 'view-sales-returns' },
      { to: '/reports/sales-returns', icon: 'pi pi-history', label: 'sidebar.return_history', permission: 'view-report-returns' },
      { to: '/menu', icon: 'pi pi-qrcode', label: 'sidebar.digital_menu' }
    ]
  },
  {
    icon: 'pi pi-shopping-bag',
    label: 'common.purchasing',
    permission: 'view-purchasing',
    items: [
      { to: '/purchasing/purchases', icon: 'pi pi-list', label: 'sidebar.purchases', permission: 'view-purchases' },
      { to: '/purchasing/returns', icon: 'pi pi-plus-circle', label: 'sidebar.create_return', permission: 'view-purchase-returns' },
      { to: '/reports/purchase-returns', icon: 'pi pi-history', label: 'sidebar.return_history', permission: 'view-report-returns' },
      { to: '/purchasing/procurement', icon: 'pi pi-search-plus', label: 'sidebar.procurement', permission: 'view-procurement' },
      { to: '/inventory/alerts', icon: 'pi pi-bell', label: 'sidebar.alerts', permission: 'view-inventory-alerts' }
    ]
  },
  {
    label: 'common.finance', icon: 'pi pi-wallet',
    permission: 'view-finance',
    items: [
      { to: '/finance/accounts', icon: 'pi pi-wallet', label: 'sidebar.accounts', permission: 'view-accounts' },
      { to: '/finance/transactions', icon: 'pi pi-money-bill', label: 'sidebar.transactions', permission: 'view-transactions' },
      { to: '/finance/closings', icon: 'pi pi-check-circle', label: 'sidebar.daily_closings', permission: 'view-closings' }
    ]
  },
  {
    label: 'common.reports', icon: 'pi pi-chart-bar',
    permission: 'view-reports',
    items: [
      { to: '/reports/recap', icon: 'pi pi-chart-bar', label: 'sidebar.recap', permission: 'view-reports' },
      { to: '/reports/sales', icon: 'pi pi-list', label: 'sidebar.sales_report', permission: 'view-report-sales' },
      { to: '/reports/purchases', icon: 'pi pi-shopping-bag', label: 'sidebar.purchase_report', permission: 'view-report-purchases' },
      { to: '/reports/sales-returns', icon: 'pi pi-replay', label: 'sidebar.sales_return_report', permission: 'view-report-returns' },
      { to: '/reports/purchase-returns', icon: 'pi pi-replay', label: 'sidebar.purchase_return_report', permission: 'view-report-returns' },
      { to: '/reports/tax', icon: 'pi pi-percentage', label: 'sidebar.tax_report', permission: 'view-report-tax' },
      { to: '/reports/audit-logs', icon: 'pi pi-history', label: 'sidebar.audit_trail', permission: 'view-reports' }
    ]
  },
  {
    label: 'common.settings', icon: 'pi pi-cog',
    items: [
      { to: '/settings/profile', icon: 'pi pi-user-edit', label: 'sidebar.profile', permission: 'view-profile' },
      { to: '/settings/tenant', icon: 'pi pi-building', label: 'common.business_profile', permission: 'view-business-profile' },
      { to: '/settings/printer', icon: 'pi pi-print', label: 'sidebar.printer_settings', permission: 'view-business-profile' },
      { to: '/settings/users', icon: 'pi pi-users', label: 'sidebar.users', permission: 'manage-users' },
      { to: '/settings/promotions', icon: 'pi pi-megaphone', label: 'sidebar.promotions', permission: 'view-promotions' }
    ]
  }
];

const visibleGroups = computed(() =>
  menuGroups
    .filter((group) => !group.permission || permissions.value.includes(group.permission))
    .map((group) => ({
      ...group,
      items: group.items.filter((item) => !item.permission || permissions.value.includes(item.permission))
    }))
    .filter((group) => group.items.length > 0)
);

const autoExpandActiveGroup = () => {
  const currentPath = route.path;
  visibleGroups.value.forEach(group => {
    if (group.items.some(item => currentPath.startsWith(item.to))) {
      openGroups.value.add(group.label);
    }
  });
};

onMounted(() => {
  autoExpandActiveGroup();
});

watch(() => route.path, () => {
  if (!isCollapsed.value) {
    autoExpandActiveGroup();
  }
});
</script>
