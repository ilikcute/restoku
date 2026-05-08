import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
const roleAll = ['admin', 'manager', 'cashier'];
const roleAdminManager = ['admin', 'manager'];
const roleAdmin = ['admin'];

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/Login.vue'),
      meta: { guest: true }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/Register.vue'),
      meta: { guest: true }
    },
    {
      path: '/menu',
      name: 'public-menu',
      component: () => import('@/views/PublicMenu.vue')
    },
    {
      path: '/',
      component: () => import('@/layouts/AppLayout.vue'),
      meta: { auth: true },
      children: [
        { path: '', redirect: '/dashboard' },
        { path: 'dashboard', name: 'dashboard', component: () => import('@/views/Dashboard.vue'), meta: { auth: true, permission: 'view-dashboard' } },
        
        // Master Data
        { path: 'master/products', name: 'products', component: () => import('@/views/master/Products.vue'), meta: { auth: true, permission: 'view-products' } },
        { path: 'master/categories', name: 'categories', component: () => import('@/views/master/Categories.vue'), meta: { auth: true, permission: 'view-categories' } },
        { path: 'master/brands', name: 'brands', component: () => import('@/views/master/Brands.vue'), meta: { auth: true, permission: 'view-master-data' } }, // Keep for safety
        { path: 'master/units', name: 'units', component: () => import('@/views/master/Units.vue'), meta: { auth: true, permission: 'view-units' } },
        { path: 'master/suppliers', name: 'suppliers', component: () => import('@/views/master/Suppliers.vue'), meta: { auth: true, permission: 'view-suppliers' } },
        { path: 'master/customers', name: 'customers', component: () => import('@/views/master/Customers.vue'), meta: { auth: true, permission: 'view-customers' } },
        
        // Inventory
        { path: 'inventory/stocks', name: 'stock-levels', component: () => import('@/views/inventory/StockLevels.vue'), meta: { auth: true, permission: 'view-stocks' } },
        { path: 'inventory/movements', name: 'stock-movements', component: () => import('@/views/inventory/StockMovements.vue'), meta: { auth: true, permission: 'view-stock-movements' } },
        { path: 'inventory/adjustments', name: 'stock-adjustments', component: () => import('@/views/inventory/StockAdjustments.vue'), meta: { auth: true, permission: 'view-stock-adjustments' } },
        { path: 'inventory/alerts', name: 'inventory-alerts', component: () => import('@/views/inventory/InventoryAlerts.vue'), meta: { auth: true, permission: 'view-inventory-alerts' } },
        
        // Sales
        { path: 'sales/shifts', name: 'shifts', component: () => import('@/views/sales/ShiftManager.vue'), meta: { auth: true, permission: 'view-shifts' } },
        { path: 'sales/orders', name: 'orders', component: () => import('@/views/sales/Orders.vue'), meta: { auth: true, permission: 'view-orders' } },
        { path: 'sales/orders/:id', name: 'order-detail', component: () => import('@/views/sales/OrderDetail.vue'), meta: { auth: true, permission: 'view-orders' } },
        { path: 'sales/returns', name: 'sales-returns', component: () => import('@/views/shared/Returns.vue'), meta: { auth: true, permission: 'view-sales-returns' }, props: { type: 'order' } },
        
        // Purchasing
        { path: 'purchasing/purchases', name: 'purchases', component: () => import('@/views/purchasing/Purchases.vue'), meta: { auth: true, permission: 'view-purchases' } },
        { path: 'purchasing/purchases/:id', name: 'purchase-detail', component: () => import('@/views/purchasing/PurchaseDetail.vue'), meta: { auth: true, permission: 'view-purchases' } },
        { path: 'purchasing/returns', name: 'purchase-returns', component: () => import('@/views/shared/Returns.vue'), meta: { auth: true, permission: 'view-purchase-returns' }, props: { type: 'purchase' } },
        { path: 'purchasing/procurement', name: 'purchasing-procurement', component: () => import('@/views/inventory/ProcurementRecommendations.vue'), meta: { auth: true, permission: 'view-procurement' } },
        
        // Finance
        { path: 'finance/accounts', name: 'accounts', component: () => import('@/views/finance/Accounts.vue'), meta: { auth: true, permission: 'view-accounts' } },
        { path: 'finance/transactions', name: 'transactions', component: () => import('@/views/finance/Transactions.vue'), meta: { auth: true, permission: 'view-transactions' } },
        { path: 'finance/closings', name: 'daily-closings', component: () => import('@/views/finance/DailyClosings.vue'), meta: { auth: true, permission: 'view-closings' } },
        
        // Reports
        { path: 'reports/recap', name: 'reports-recap', component: () => import('@/views/reports/Reports.vue'), meta: { auth: true, permission: 'view-reports' } },
        { path: 'reports/sales', name: 'reports-sales', component: () => import('@/views/reports/SalesDetailReport.vue'), meta: { auth: true, permission: 'view-report-sales' } },
        { path: 'reports/purchases', name: 'reports-purchases', component: () => import('@/views/reports/PurchaseReport.vue'), meta: { auth: true, permission: 'view-report-purchases' } },
        { path: 'reports/sales-returns', name: 'reports-sales-returns', component: () => import('@/views/reports/SalesReturnReport.vue'), meta: { auth: true, permission: 'view-report-returns' } },
        { path: 'reports/purchase-returns', name: 'reports-purchase-returns', component: () => import('@/views/reports/PurchaseReturnReport.vue'), meta: { auth: true, permission: 'view-report-returns' } },
        { path: 'reports/tax', name: 'reports-tax', component: () => import('@/views/reports/TaxReport.vue'), meta: { auth: true, permission: 'view-report-tax' } },
        { path: 'reports/audit-logs', name: 'reports-audit', component: () => import('@/views/reports/AuditLog.vue'), meta: { auth: true, permission: 'view-reports' } },
        
        // Settings
        { path: 'settings/profile', name: 'profile', component: () => import('@/views/settings/Profile.vue'), meta: { auth: true, permission: 'view-profile' } },
        { path: 'settings/tenant', name: 'business-profile', component: () => import('@/views/settings/BusinessProfile.vue'), meta: { auth: true, permission: 'view-business-profile' } },
        { path: 'settings/printer', name: 'printer-settings', component: () => import('@/views/settings/PrinterSettings.vue'), meta: { auth: true, permission: 'view-business-profile' } },
        { path: 'settings/users', name: 'users', component: () => import('@/views/settings/Users.vue'), meta: { auth: true, permission: 'manage-users' } },
        { path: 'settings/promotions', name: 'promotions', component: () => import('@/views/settings/Promotions.vue'), meta: { auth: true, permission: 'view-promotions' } }
      ]
    },
    {
      path: '/sales/pos',
      name: 'pos',
      component: () => import('@/views/sales/POS.vue'),
      meta: { auth: true, permission: 'view-pos' }
    },
    {
      path: '/sales/pos/display',
      name: 'pos-display',
      component: () => import('@/views/sales/CustomerDisplay.vue')
    }
  ]
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser();
    } catch (error) {
      // invalid token is handled in auth store
    }
  }

  if (to.meta.auth && !authStore.isAuthenticated) {
    return '/login';
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    return '/dashboard';
  }

  if (to.meta.permission) {
    const permissions = authStore.user?.attributes?.permissions ?? null;

    if (permissions !== null && !permissions.includes(to.meta.permission)) {
      if (to.name !== 'dashboard') {
        return { name: 'dashboard' };
      }
    }
  }

  return true;
});

export default router;
