<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Contracts\SubCategoryRepositoryInterface;
use App\Repositories\Eloquent\SubCategoryRepository;
use App\Repositories\Contracts\MenuItemRepositoryInterface;
use App\Repositories\Eloquent\MenuItemRepository;
use App\Repositories\Contracts\TableRepositoryInterface;
use App\Repositories\Eloquent\TableRepository;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Repositories\Eloquent\CustomerRepository;
use App\Repositories\Contracts\ReservationRepositoryInterface;
use App\Repositories\Eloquent\ReservationRepository;
use App\Repositories\Contracts\SupplierRepositoryInterface;
use App\Repositories\Eloquent\SupplierRepository;
use App\Repositories\Contracts\InventoryRepositoryInterface;
use App\Repositories\Eloquent\InventoryRepository;
use App\Repositories\Contracts\PurchaseRepositoryInterface;
use App\Repositories\Eloquent\PurchaseRepository;
use App\Repositories\Contracts\PartnerRepositoryInterface;
use App\Repositories\Eloquent\PartnerRepository;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use App\Repositories\Eloquent\AttendanceRepository;
use App\Repositories\Contracts\PayrollRepositoryInterface;
use App\Repositories\Eloquent\PayrollRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubCategoryRepositoryInterface::class, SubCategoryRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(TableRepositoryInterface::class, TableRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class, ReservationRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(PurchaseRepositoryInterface::class, PurchaseRepository::class);
        $this->app->bind(PartnerRepositoryInterface::class, PartnerRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(PayrollRepositoryInterface::class, PayrollRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

