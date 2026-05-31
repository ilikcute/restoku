<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DailyClosingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DatabaseController;
use App\Http\Controllers\Api\DpkadSyncController;
use App\Http\Controllers\Api\FinancialController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProcurementController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\PublicMenuController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReturnController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public Menu
    Route::get('/catalog', [PublicMenuController::class, 'products']);
    Route::post('/save-order', [PublicMenuController::class, 'storeOrder']);

    // Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:login')
            ->name('auth.login');

        // Protected Auth Routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        });
    });

    // Protected Business Routes
    Route::middleware('auth:sanctum')->group(function () {

        // --- DASHBOARD ---
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])
            ->middleware('permission:view-dashboard');

        // --- MASTER DATA ---
        Route::get('/master/options', [MasterController::class, 'options'])
            ->middleware('permission:view-master-data');
        Route::get('/products/init', [MasterController::class, 'initProducts'])
            ->middleware('permission:view-products');
        Route::apiResource('categories', CategoryController::class)
            ->middleware('permission:view-categories');
        Route::apiResource('units', UnitController::class)
            ->middleware('permission:view-units');
        Route::get('/products/next-code', [ProductController::class, 'getNextCode'])
            ->middleware('permission:view-products');
        Route::get('/products/template', [ProductController::class, 'downloadTemplate'])
            ->middleware('permission:view-products');
        Route::get('/products/export', [ProductController::class, 'export'])
            ->middleware('permission:view-products');
        Route::post('/products/import', [ProductController::class, 'import'])
            ->middleware('permission:create-products');
        Route::apiResource('products', ProductController::class)
            ->middleware('permission:view-products');
        Route::apiResource('suppliers', SupplierController::class)
            ->middleware('permission:view-suppliers');
        Route::apiResource('customers', CustomerController::class)
            ->middleware('permission:view-customers');
        Route::apiResource('promotions', PromotionController::class)
            ->middleware('permission:view-promotions');

        // --- INVENTORY & STOCK ---
        Route::get('/inventory/stocks', [InventoryController::class, 'stockLevels'])->middleware('permission:view-stocks')->name('inventory.index');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->middleware('permission:view-stock-movements')->name('inventory.movements');
        Route::get('/inventory/movements/{product}', [InventoryController::class, 'movementDetail'])->middleware('permission:view-stock-movements')->name('inventory.movementDetail');
        Route::post('/inventory/adjustments', [InventoryController::class, 'adjust'])->middleware('permission:view-stock-adjustments')->name('inventory.adjust');
        Route::get('/inventory/adjustments', [InventoryController::class, 'adjustmentHistory'])->middleware('permission:view-stock-adjustments')->name('inventory.adjustmentHistory');
        Route::get('/inventory/adjustments/{adjustment}', [InventoryController::class, 'showAdjustment'])->middleware('permission:view-stock-adjustments')->name('inventory.showAdjustment');
        Route::get('/inventory/recommendations', [ProcurementController::class, 'recommendations'])->middleware('permission:view-procurement');
        Route::get('/inventory/alerts', [ProcurementController::class, 'alerts'])->middleware('permission:view-inventory-alerts');

        // --- SALES (KASIR) ---
        Route::get('/shifts/current', [ShiftController::class, 'current'])->middleware('permission:view-shifts');
        Route::post('/shifts/open', [ShiftController::class, 'open'])->middleware('permission:view-shifts');
        Route::post('/shifts/close', [ShiftController::class, 'close'])->middleware('permission:view-shifts');
        Route::get('/shifts/{id}/report', [ShiftController::class, 'downloadReport'])->middleware('permission:view-shifts');
        Route::apiResource('shifts', ShiftController::class)->only(['index', 'show'])->middleware('permission:view-shifts');
        Route::get('/orders/{order}/receipt', [OrderController::class, 'downloadReceipt'])->middleware('permission:view-orders');
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show'])->middleware('permission:view-orders');
        Route::get('/orders/pending/{token}', [PublicMenuController::class, 'fetchOrder'])->middleware('permission:view-pos');

        // --- PURCHASING ---
        Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'downloadPdf'])->middleware('permission:view-purchases');
        Route::apiResource('purchases', PurchaseController::class)->only(['index', 'store', 'show'])->middleware('permission:view-purchases');

        // --- FINANCIAL & CLOSING ---
        Route::get('/finance/accounts', [FinancialController::class, 'accounts'])->middleware('permission:view-accounts');
        Route::post('/finance/accounts', [FinancialController::class, 'storeAccount'])->middleware('permission:view-accounts');
        Route::put('/finance/accounts/{account}', [FinancialController::class, 'updateAccount'])->middleware('permission:view-accounts');
        Route::delete('/finance/accounts/{account}', [FinancialController::class, 'destroyAccount'])->middleware('permission:view-accounts');
        Route::get('/finance/transactions', [FinancialController::class, 'transactions'])->middleware('permission:view-transactions');
        Route::post('/finance/transactions', [FinancialController::class, 'storeTransaction'])->middleware('permission:view-transactions');
        Route::get('/finance/expense-categories', [FinancialController::class, 'expenseCategories'])->middleware('permission:view-transactions');
        Route::get('/finance/income-categories', [FinancialController::class, 'incomeCategories'])->middleware('permission:view-transactions');

        // --- RETURNS ---
        Route::get('/returns/search', [ReturnController::class, 'search'])->middleware('permission:view-sales-returns|view-purchase-returns');
        Route::post('/returns/orders', [ReturnController::class, 'storeOrderReturn'])->middleware('permission:view-sales-returns');
        Route::post('/returns/purchases', [ReturnController::class, 'storePurchaseReturn'])->middleware('permission:view-purchase-returns');

        Route::get('/daily-closings/{id}/report', [DailyClosingController::class, 'downloadReport'])->middleware('permission:view-closings');
        Route::apiResource('daily-closings', DailyClosingController::class)->only(['index', 'store', 'show'])->middleware('permission:view-closings');

        // --- SETTINGS & USERS ---
        Route::middleware('permission:view-business-profile')->group(function () {
            Route::get('/settings/tenant', [TenantController::class, 'show']);
            Route::post('/settings/tenant', [TenantController::class, 'update']);
            Route::get('/settings/printer', [TenantController::class, 'showPrinterSettings']);
            Route::put('/settings/printer', [TenantController::class, 'updatePrinterSettings']);
            Route::put('/settings/printer/kitchen', [TenantController::class, 'updateKitchenPrinterSettings']);
            Route::post('/settings/printer/test', [TenantController::class, 'testPrinter']);
            Route::get('/settings/printer/scan', [TenantController::class, 'scanReadyPrinters']);
        });
        Route::middleware('permission:view-profile')->group(function () {
            Route::get('/profile', [ProfileController::class, 'show']);
            Route::put('/profile', [ProfileController::class, 'update']);
            Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        });
        Route::get('/permissions', [UserController::class, 'permissions'])->middleware('permission:manage-users|manage-roles');
        Route::get('/roles/list', [UserController::class, 'roles'])->middleware('permission:manage-users|manage-roles');

        Route::middleware('permission:manage-roles')->group(function () {
            Route::apiResource('roles', RoleController::class);
        });

        Route::middleware('permission:manage-users')->group(function () {
            Route::apiResource('users', UserController::class);
        });

        // --- REPORTS ---
        Route::prefix('reports')->middleware('permission:view-reports')->group(function () {
            Route::get('/summary', [ReportController::class, 'summary']);
            Route::get('/daily-chart', [ReportController::class, 'dailyChart']);
            Route::get('/top-products', [ReportController::class, 'topProducts']);
            Route::get('/transactions', [ReportController::class, 'transactions']);
            Route::get('/purchases', [ReportController::class, 'purchases']);
            Route::get('/sales-returns', [ReportController::class, 'salesReturns']);
            Route::get('/purchase-returns', [ReportController::class, 'purchaseReturns']);
            Route::get('/export/excel', [ReportController::class, 'exportExcel']);
            Route::get('/export/sales', [ReportController::class, 'exportExcelSales']);
            Route::get('/export/sales-detail', [ReportController::class, 'exportExcelSalesDetail']);
            Route::get('/export/sales-shift', [ReportController::class, 'exportExcelSalesShift']);
            Route::get('/export/pdf', [ReportController::class, 'exportPdf']);
            Route::get('/export/tax-excel', [ReportController::class, 'exportExcelTax']);
            Route::get('/export/tax-pdf', [ReportController::class, 'exportPdfTax']);

            Route::get('/tax', [ReportController::class, 'taxReport']);
            Route::get('/tax-fixed', [ReportController::class, 'taxFixedReport']);
            Route::get('/export/tax-fixed-excel', [ReportController::class, 'exportExcelTaxFixed']);
            Route::get('/export/tax-fixed-pdf', [ReportController::class, 'exportPdfTaxFixed']);
            Route::get('/audit-logs', [ActivityLogController::class, 'index']);
            Route::get('/audit-logs/{activity}', [ActivityLogController::class, 'show']);

            // DPKAD Sync
            Route::post('/sync-dpkad', [DpkadSyncController::class, 'sync']);
        });
        // Database Backup & Restore
        Route::get('/database/export', [DatabaseController::class, 'export'])
            ->middleware('permission:manage-tenant-settings');
        Route::post('/database/import', [DatabaseController::class, 'import'])
            ->middleware('permission:manage-tenant-settings');
    });
});
