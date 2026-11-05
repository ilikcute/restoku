# Audit Integrasi Endpoint Backend → Frontend

> **Status**: ✅ All issues resolved — last updated 2026-06-16

## Legend

| Status | Meaning |
|--------|---------|
| ✅ **Connected** | Backend route exists, frontend calls it via `api/` module |
| 🟡 **Unused** | Backend route exists, frontend never calls it (dead code) |
| 🔵 **No Backend** | Frontend view exists but needs no API (static/placeholder) |

---

## 1. Auth & Public

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 1 | POST | `/auth/register` | ✅ AuthController@register | `authStore.register()` | `stores/auth.js:49` | ✅ |
| 2 | POST | `/auth/login` | ✅ AuthController@login | `authStore.login()` | `stores/auth.js:22` | ✅ |
| 3 | GET | `/auth/me` | ✅ AuthController@me | `authStore.fetchUser()` | `stores/auth.js:90` | ✅ |
| 4 | POST | `/auth/logout` | ✅ AuthController@logout | `authStore.logout()` | `stores/auth.js:79` | ✅ |
| 5 | GET | `/catalog` | ✅ PublicMenuController@products | `publicApi.getMenu()` | `api/public.js:11` | ✅ |
| 6 | POST | `/save-order` | ✅ PublicMenuController@storeOrder | `publicApi.createOrder()` | `api/public.js:12` | ✅ |

---

## 2. Dashboard

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 7 | GET | `/dashboard/stats` | ✅ DashboardController@stats | `dashboardApi.getStats()` | `api/dashboard.js:4` | ✅ |

---

## 3. Master Data — Products

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 8 | GET | `/master/options` | ✅ MasterController@options | `masterApi.getOptions()` | `api/master.js:4` | ✅ |
| 9 | GET | `/products/init` | ✅ MasterController@initProducts | `productApi.getInitData()` | `views/master/Products.vue` | ✅ |
| 10 | GET | `/products/next-code` | ✅ ProductController@getNextCode | `productApi.getNextCode()` | `components/master/ProductFormModal.vue` | ✅ |
| 11 | GET | `/products/template` | ✅ ProductController@downloadTemplate | `productApi.downloadTemplate()` | `views/master/Products.vue` | ✅ |
| 12 | GET | `/products/export` | ✅ ProductController@export | `productApi.export()` | `views/master/Products.vue` | ✅ |
| 13 | POST | `/products/import` | ✅ ProductController@import | `productApi.import()` | `views/master/Products.vue` | ✅ |
| 14 | GET | `/products` | ✅ ProductController@index | `productApi.getAll(params)` | `views/master/Products.vue` | ✅ |
| 15 | POST | `/products` | ✅ ProductController@store | `productApi.create()` | `views/master/Products.vue` | ✅ |
| 16 | GET | `/products/{id}` | ✅ ProductController@show | `productApi.getById(id)` | via ProductDetailModal | ✅ |
| 17 | PUT | `/products/{id}` | ✅ ProductController@update | `productApi.update(id, data)` | `views/master/Products.vue` | ✅ |
| 18 | DELETE | `/products/{id}` | ✅ ProductController@destroy | `productApi.delete(id)` | `views/master/Products.vue` | ✅ |

## 4. Master Data — Categories, Units, Suppliers, Customers

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 19 | CRUD | `/categories` | ✅ CategoryController | `categoryApi.*` | `api/master.js` | ✅ |
| 20 | CRUD | `/units` | ✅ UnitController | `unitApi.*` | `api/master.js` | ✅ |
| 21 | CRUD | `/suppliers` | ✅ SupplierController | `supplierApi.*` | `api/master.js` | ✅ |
| 22 | CRUD | `/customers` | ✅ CustomerController | `customerApi.*` | `api/master.js` | ✅ |

All delegated via `SimpleCrudPage.vue` (loader → getAll, creator → create, updater → update, deleter → delete).

## 5. Master Data — Brands

> ✅ **Resolved** — `BrandController`, `BrandRepository`, `Brand` model, migration, and `/brands` API resource routes all created.

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 23 | GET | `/brands` | ✅ BrandController@index | `brandApi.getAll()` | `views/master/Brands.vue` | ✅ |
| 24 | POST | `/brands` | ✅ BrandController@store | `brandApi.create()` | `views/master/Brands.vue` | ✅ |
| 25 | GET | `/brands/{id}` | ✅ BrandController@show | `brandApi.getById(id)` | `api/master.js` | ✅ |
| 26 | PUT | `/brands/{id}` | ✅ BrandController@update | `brandApi.update(id, data)` | `views/master/Brands.vue` | ✅ |
| 27 | DELETE | `/brands/{id}` | ✅ BrandController@destroy | `brandApi.delete(id)` | `views/master/Brands.vue` | ✅ |

## 6. Master Data — Promotions

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 28 | GET | `/promotions` | ✅ PromotionController@index | `promotionApi.getAll(params)` | `views/settings/Promotions.vue` | ✅ |
| 29 | POST | `/promotions` | ✅ PromotionController@store | `promotionApi.create(data)` | `views/settings/Promotions.vue` | ✅ |
| 30 | GET | `/promotions/{id}` | ✅ PromotionController@show | `promotionApi.getById(id)` | `api/master.js` | ✅ |
| 31 | PUT | `/promotions/{id}` | ✅ PromotionController@update | `promotionApi.update(id, data)` | `views/settings/Promotions.vue` | ✅ |
| 32 | DELETE | `/promotions/{id}` | ✅ PromotionController@destroy | `promotionApi.delete(id)` | `views/settings/Promotions.vue` | ✅ |

Also consumed by: POS.vue, CustomerDisplay.vue.

## 7. Inventory & Stock

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 33 | GET | `/inventory/stocks` | ✅ InventoryController@stockLevels | `inventoryApi.getStocks(params)` | `api/inventory.js` | ✅ |
| 34 | GET | `/inventory/movements` | ✅ InventoryController@movements | `inventoryApi.getMovements(params)` | `api/inventory.js` | ✅ |
| 35 | GET | `/inventory/movements/{product}` | ✅ InventoryController@movementDetail | `inventoryApi.getMovementDetail(id)` | `api/inventory.js` | ✅ |
| 36 | POST | `/inventory/adjustments` | ✅ InventoryController@adjust | `inventoryApi.createAdjustment(data)` | `api/inventory.js` | ✅ |
| 37 | GET | `/inventory/adjustments` | ✅ InventoryController@adjustmentHistory | `inventoryApi.getAdjustments(params)` | `api/inventory.js` | ✅ |
| 38 | GET | `/inventory/adjustments/{adj}` | ✅ InventoryController@showAdjustment | `inventoryApi.getAdjustmentById(id)` | `api/inventory.js` | ✅ |
| 39 | GET | `/inventory/recommendations` | ✅ ProcurementController@recommendations | `inventoryApi.getRecommendations(params)` | `api/inventory.js` | ✅ |
| 40 | GET | `/inventory/alerts` | ✅ ProcurementController@alerts | `inventoryApi.getAlerts()` | `api/inventory.js` | ✅ |

## 8. Shifts

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 41 | GET | `/shifts/current` | ✅ ShiftController@current | `shiftApi.getCurrent()` | `stores/shift.js` | ✅ |
| 42 | POST | `/shifts/open` | ✅ ShiftController@open | `shiftApi.open(data)` | `stores/shift.js` | ✅ |
| 43 | POST | `/shifts/close` | ✅ ShiftController@close | `shiftApi.close(data)` | `stores/shift.js` | ✅ |
| 44 | GET | `/shifts` | ✅ ShiftController@index | `shiftApi.getAll(params)` | `stores/shift.js` | ✅ |
| 45 | GET | `/shifts/{id}` | ✅ ShiftController@show | — | — | 🟡 Unused |
| 46 | GET | `/shifts/{id}/report` | ✅ ShiftController@downloadReport | `salesApi.downloadShiftReport(id)` | `api/sales.js` | ✅ |

## 9. Orders (Sales)

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 47 | GET | `/orders` | ✅ OrderController@index | `orderApi.getAll(params)` | `api/sales.js` | ✅ |
| 48 | POST | `/orders` | ✅ OrderController@store | `orderApi.create(data)` | `api/sales.js` | ✅ |
| 49 | GET | `/orders/{id}` | ✅ OrderController@show | `orderApi.getById(id)` | `api/sales.js` | ✅ |
| 50 | GET | `/orders/{id}/receipt` | ✅ OrderController@downloadReceipt | `salesApi.downloadOrderReceipt(id)` | `api/sales.js` | ✅ |
| 51 | GET | `/orders/pending/{token}` | ✅ PublicMenuController@fetchOrder | `orderApi.fetchPending(token)` | `api/sales.js` | ✅ |
| 52 | POST | `/orders/import` | ✅ ImportTransactionController@import | `salesApi.importOrders(formData)` | `api/sales.js` | ✅ |
| 53 | GET | `/orders/import/summary` | ✅ ImportTransactionController@getSummary | `salesApi.getImportSummary()` | `api/sales.js` | ✅ |
| 54 | POST | `/orders/import/confirm` | ✅ ImportTransactionController@confirmImport | `salesApi.confirmImport()` | `api/sales.js` | ✅ |

## 10. Purchasing

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 55 | GET | `/purchases` | ✅ PurchaseController@index | `purchaseApi.getAll(params)` | `api/sales.js` | ✅ |
| 56 | POST | `/purchases` | ✅ PurchaseController@store | `purchaseApi.create(data)` | `api/sales.js` | ✅ |
| 57 | GET | `/purchases/{id}` | ✅ PurchaseController@show | `purchaseApi.getById(id)` | `api/sales.js` | ✅ |
| 58 | GET | `/purchases/{id}/pdf` | ✅ PurchaseController@downloadPdf | `purchaseApi.downloadPdf(id)` | `api/sales.js` | ✅ |

## 11. Finance

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 59 | GET | `/finance/accounts` | ✅ FinancialController@accounts | `financeApi.getAccounts()` | `views/finance/Accounts.vue` | ✅ |
| 60 | POST | `/finance/accounts` | ✅ FinancialController@storeAccount | `financeApi.createAccount(data)` | `views/finance/Accounts.vue` | ✅ |
| 61 | PUT | `/finance/accounts/{id}` | ✅ FinancialController@updateAccount | `financeApi.updateAccount(id, data)` | `views/finance/Accounts.vue` | ✅ |
| 62 | DELETE | `/finance/accounts/{id}` | ✅ FinancialController@destroyAccount | `financeApi.deleteAccount(id)` | `views/finance/Accounts.vue` | ✅ |
| 63 | GET | `/finance/transactions` | ✅ FinancialController@transactions | `financeApi.getTransactions(params)` | `views/finance/Transactions.vue` | ✅ |
| 64 | POST | `/finance/transactions` | ✅ FinancialController@storeTransaction | `financeApi.createTransaction(data)` | `views/finance/Transactions.vue` | ✅ |
| 65 | GET | `/finance/expense-categories` | ✅ FinancialController@expenseCategories | `financeApi.getExpenseCategories()` | `views/finance/Transactions.vue` | ✅ |
| 66 | GET | `/finance/income-categories` | ✅ FinancialController@incomeCategories | `financeApi.getIncomeCategories()` | `views/finance/Transactions.vue` | ✅ |

## 12. Returns

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 67 | GET | `/returns/search` | ✅ ReturnController@search | `returnsApi.search(params)` | `api/returns.js` | ✅ |
| 68 | POST | `/returns/orders` | ✅ ReturnController@storeOrderReturn | `returnsApi.storeOrderReturn(data)` | `api/returns.js` | ✅ |
| 69 | POST | `/returns/purchases` | ✅ ReturnController@storePurchaseReturn | `returnsApi.storePurchaseReturn(data)` | `api/returns.js` | ✅ |

## 13. Daily Closings

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 70 | GET | `/daily-closings` | ✅ DailyClosingController@index | `closingApi.getAll(params)` | `api/finance.js` | ✅ |
| 71 | POST | `/daily-closings` | ✅ DailyClosingController@store | `closingApi.create(data)` | `api/finance.js` | ✅ |
| 72 | GET | `/daily-closings/{id}` | ✅ DailyClosingController@show | `closingApi.getById(id)` | `api/finance.js` | ✅ |
| 73 | GET | `/daily-closings/{id}/report` | ✅ DailyClosingController@downloadReport | `financeApi.downloadClosingReport(id)` | `api/finance.js` | ✅ |

## 14. Settings — Tenant / Business Profile

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 74 | GET | `/settings/tenant` | ✅ TenantController@show | `tenantApi.get()` | `api/tenant.js` | ✅ |
| 75 | POST | `/settings/tenant` | ✅ TenantController@update | `tenantApi.update(data)` | `api/tenant.js` | ✅ |
| 76 | GET | `/settings/printer` | ✅ TenantController@showPrinterSettings | `tenantApi.getPrinter()` | `api/tenant.js` | ✅ |
| 77 | PUT | `/settings/printer` | ✅ TenantController@updatePrinterSettings | `tenantApi.updatePrinter(data)` | `api/tenant.js` | ✅ |
| 78 | PUT | `/settings/printer/kitchen` | ✅ TenantController@updateKitchenPrinterSettings | `tenantApi.updateKitchenPrinter(data)` | `api/tenant.js` | ✅ |
| 79 | POST | `/settings/printer/test` | ✅ TenantController@testPrinter | `tenantApi.testPrinter(type)` | `api/tenant.js` | ✅ |
| 80 | GET | `/settings/printer/scan` | ✅ TenantController@scanReadyPrinters | `tenantApi.scanReadyPrinters()` | `api/tenant.js` | ✅ |

## 15. Profile

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 81 | GET | `/profile` | ✅ ProfileController@show | `profileApi.get()` | `api/settings.js` | ✅ |
| 82 | PUT | `/profile` | ✅ ProfileController@update | `profileApi.update(data)` | `api/settings.js` | ✅ |
| 83 | PUT | `/profile/password` | ✅ ProfileController@updatePassword | `profileApi.updatePassword(data)` | `api/settings.js` | ✅ |

## 16. Users, Roles & Permissions

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 84 | GET | `/users` | ✅ UserController@index | `userApi.getAll()` | `api/settings.js` | ✅ |
| 85 | POST | `/users` | ✅ UserController@store | `userApi.create(data)` | `api/settings.js` | ✅ |
| 86 | GET | `/users/{id}` | ✅ UserController@show | `userApi.getById(id)` | `api/settings.js` | ✅ |
| 87 | PUT | `/users/{id}` | ✅ UserController@update | `userApi.update(id, data)` | `api/settings.js` | ✅ |
| 88 | DELETE | `/users/{id}` | ✅ UserController@destroy | `userApi.delete(id)` | `api/settings.js` | ✅ |
| 89 | GET | `/roles` | ✅ RoleController@index | `roleApi.getAll()` | `api/settings.js` | ✅ |
| 90 | POST | `/roles` | ✅ RoleController@store | `roleApi.create(data)` | `api/settings.js` | ✅ |
| 91 | GET | `/roles/{id}` | ✅ RoleController@show | `roleApi.getById(id)` | `api/settings.js` | ✅ |
| 92 | PUT | `/roles/{id}` | ✅ RoleController@update | `roleApi.update(id, data)` | `api/settings.js` | ✅ |
| 93 | DELETE | `/roles/{id}` | ✅ RoleController@destroy | `roleApi.delete(id)` | `api/settings.js` | ✅ |
| 94 | GET | `/roles/list` | ✅ UserController@roles | `userApi.getRoles()` | `api/settings.js` | ✅ |
| 95 | GET | `/permissions` | ✅ UserController@permissions | `userApi.getPermissions()` | `api/settings.js` | ✅ |

## 17. Reports

> ✅ **Resolved** — `api/reports.js` module created. All 21 report endpoints now called through centralized module. No raw axios calls remain in report views.

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 96 | GET | `/reports/summary` | ✅ ReportController@summary | `reportsApi.getSummary(params)` | `api/reports.js` | ✅ |
| 97 | GET | `/reports/daily-chart` | ✅ ReportController@dailyChart | `reportsApi.getDailyChart(params)` | `api/reports.js` | ✅ |
| 98 | GET | `/reports/top-products` | ✅ ReportController@topProducts | `reportsApi.getTopProducts(params)` | `api/reports.js` | ✅ |
| 99 | GET | `/reports/transactions` | ✅ ReportController@transactions | `reportsApi.getTransactions(params)` | `api/reports.js` | ✅ |
| 100 | GET | `/reports/purchases` | ✅ ReportController@purchases | `reportsApi.getPurchases(params)` | `api/reports.js` | ✅ |
| 101 | GET | `/reports/sales-returns` | ✅ ReportController@salesReturns | `reportsApi.getSalesReturns(params)` | `api/reports.js` | ✅ |
| 102 | GET | `/reports/purchase-returns` | ✅ ReportController@purchaseReturns | `reportsApi.getPurchaseReturns(params)` | `api/reports.js` | ✅ |
| 103 | GET | `/reports/tax` | ✅ ReportController@taxReport | `reportsApi.getTaxReport(params)` | `api/reports.js` | ✅ |
| 104 | GET | `/reports/tax-fixed` | ✅ ReportController@taxFixedReport | `reportsApi.getTaxFixedReport(params)` | `api/reports.js` | ✅ |
| 105 | GET | `/reports/audit-logs` | ✅ ActivityLogController@index | `reportsApi.getAuditLogs(params)` | `api/reports.js` | ✅ |
| 106 | GET | `/reports/audit-logs/{id}` | ✅ ActivityLogController@show | — | — | 🟡 Unused |
| 107 | GET | `/reports/export/excel` | ✅ ReportController@exportExcel | `reportsApi.exportExcel(params)` | `api/reports.js` | ✅ |
| 108 | GET | `/reports/export/pdf` | ✅ ReportController@exportPdf | `reportsApi.exportPdf(params)` | `api/reports.js` | ✅ |
| 109 | GET | `/reports/export/sales` | ✅ ReportController@exportExcelSales | `reportsApi.exportSales(params)` | `api/reports.js` | ✅ |
| 110 | GET | `/reports/export/sales-detail` | ✅ ReportController@exportExcelSalesDetail | `reportsApi.exportSalesDetail(params)` | `api/reports.js` | ✅ |
| 111 | GET | `/reports/export/sales-shift` | ✅ ReportController@exportExcelSalesShift | `reportsApi.exportSalesShift(params)` | `api/reports.js` | ✅ |
| 112 | GET | `/reports/export/tax-excel` | ✅ ReportController@exportExcelTax | `reportsApi.exportTaxExcel(params)` | `api/reports.js` | ✅ |
| 113 | GET | `/reports/export/tax-pdf` | ✅ ReportController@exportPdfTax | `reportsApi.exportTaxPdf(params)` | `api/reports.js` | ✅ |
| 114 | GET | `/reports/export/tax-fixed-excel` | ✅ ReportController@exportExcelTaxFixed | `reportsApi.exportTaxFixedExcel(params)` | `api/reports.js` | ✅ |
| 115 | GET | `/reports/export/tax-fixed-pdf` | ✅ ReportController@exportPdfTaxFixed | `reportsApi.exportTaxFixedPdf(params)` | `api/reports.js` | ✅ |
| 116 | POST | `/reports/sync-dpkad` | ✅ DpkadSyncController@sync | `reportsApi.syncDpkad(data)` | `api/reports.js` | ✅ |

## 18. Database

| # | Method | Endpoint | Backend | Frontend Caller | File Location | Status |
|---|--------|----------|---------|-----------------|---------------|--------|
| 117 | GET | `/database/export` | ✅ DatabaseController@export | `databaseApi.export()` | `api/settings.js` | ✅ |
| 118 | POST | `/database/import` | ✅ DatabaseController@import | `databaseApi.import(formData)` | `api/settings.js` | ✅ |

## 19. Pages Without Backend Calls

| Frontend Route | View File | Notes | Status |
|----------------|-----------|-------|--------|
| `/forgot-password` | `ForgotPassword.vue` | Placeholder UI only, no API calls | 🔵 |
| `/menu` | `PublicMenu.vue` | Uses publicApi (no auth token) | ✅ |
| `/sales/pos/display` | `CustomerDisplay.vue` | Uses `promotionApi.getAll()` via SSE-like polling | ✅ |
| `/kitchen` | `Kitchen.vue` | Real-time via SSE/polling, no REST calls | 🔵 |

---

## Summary Statistics

| Category | Count |
|----------|-------|
| ✅ Connected (via api/ module) | 115 |
| 🟡 Unused (backend only, dead code) | 2 |
| 🔵 No Backend Needed | 2 |
| **Total Backend Routes** | **119** |
| **Total Frontend Calls** | **115** |

## Remaining Open Items

| # | Severity | Issue | Decision |
|---|----------|-------|----------|
| 1 | 🟡 Low | `GET /shifts/{id}` show — backend defined, never consumed | Leave as-is; no frontend use case identified |
| 2 | 🟡 Low | `GET /reports/audit-logs/{id}` — backend defined, never consumed | Leave as-is; could be used in a future detail view |
| 3 | 🔵 None | `/forgot-password` page has no backend handler | Intentional placeholder; implement when email reset flow is ready |
