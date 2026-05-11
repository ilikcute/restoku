<?php

namespace App\Providers;

use App\Interfaces\AuthRepositoryInterface;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\DailyClosingRepositoryInterface;
use App\Interfaces\DashboardRepositoryInterface;
use App\Interfaces\FinancialRepositoryInterface;
use App\Interfaces\InventoryRepositoryInterface;
use App\Interfaces\OrderRepositoryInterface;
use App\Interfaces\ProcurementRepositoryInterface;
use App\Interfaces\ProductRepositoryInterface;
use App\Interfaces\ProfileRepositoryInterface;
use App\Interfaces\PublicMenuRepositoryInterface;
use App\Interfaces\PurchaseRepositoryInterface;
use App\Interfaces\ReportRepositoryInterface;
use App\Interfaces\ReturnRepositoryInterface;
use App\Interfaces\ShiftRepositoryInterface;
use App\Interfaces\SupplierRepositoryInterface;
use App\Interfaces\UnitRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\DailyClosingRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\FinancialRepository;
use App\Repositories\InventoryRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProcurementRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\PublicMenuRepository;
use App\Repositories\PurchaseRepository;
use App\Repositories\ReportRepository;
use App\Repositories\ReturnRepository;
use App\Repositories\ShiftRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UnitRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(PurchaseRepositoryInterface::class, PurchaseRepository::class);
        $this->app->bind(ProcurementRepositoryInterface::class, ProcurementRepository::class);
        $this->app->bind(FinancialRepositoryInterface::class, FinancialRepository::class);
        $this->app->bind(ReportRepositoryInterface::class, ReportRepository::class);
        $this->app->bind(DailyClosingRepositoryInterface::class, DailyClosingRepository::class);
        $this->app->bind(ReturnRepositoryInterface::class, ReturnRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(PublicMenuRepositoryInterface::class, PublicMenuRepository::class);
        $this->app->bind(ShiftRepositoryInterface::class, ShiftRepository::class);
        $this->app->bind(DashboardRepositoryInterface::class, DashboardRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
