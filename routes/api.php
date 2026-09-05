<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SubCategoryController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\PayrollController;

/*
|--------------------------------------------------------------------------
| Public Routes (Authentication)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Bearer Token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- Dashboard Summary ---
    Route::get('/admin/dashboard-summary', [DashboardController::class, 'summary']);

    // --- User Profile & Logout ---
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/image', [AuthController::class, 'updateImage']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- Categories Module ---
    // (View: Admin, Waiter, Cashier | Create/Update/Delete: Admin)
    Route::apiResource('categories', CategoryController::class);
    Route::get('categories/{id}/sub-categories', [CategoryController::class, 'subCategories']);

    // Sub-category read routes (open to all authenticated users)
    Route::get('/sub-categories', [SubCategoryController::class, 'index']);
    Route::get('/sub-categories/{id}', [SubCategoryController::class, 'show']);

    // --- Menu Items Module ---
    // (View: Admin, Waiter, Cashier | Create/Update/Delete: Admin)
    Route::get('/menu/available', [MenuItemController::class, 'available']);
    Route::get('/menu', [MenuItemController::class, 'index']);
    Route::get('/menu/{id}', [MenuItemController::class, 'show']);

    // --- Tables Module ---
    // (View: Admin, Waiter, Cashier | Create/Update/Delete: Admin)
    Route::get('/tables/available', [TableController::class, 'available']);
    Route::get('/tables', [TableController::class, 'index']);
    Route::get('/tables/{id}', [TableController::class, 'show']);

    // --- Customers Module ---
    // (View: Admin, Waiter, Cashier | Create/Update/Delete: Admin)
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);

    // --- Reservations Module ---
    // (View: Admin, Waiter, Cashier | Create/Update/Delete: Admin)
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);

    // --- Attendance: self-service clock in/out (all authenticated users) ---
    Route::get('/attendance/today-status', [AttendanceController::class, 'todayStatus']);
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);

    // TESTING UTILITY: reset today's attendance so the flow can be re-tested
    Route::post('/attendance/reset-today', [AttendanceController::class, 'resetToday']);

    // --- Attendance history (employees are force-scoped to their own records) ---
    Route::get('/attendance', [AttendanceController::class, 'index']);

    // --- Dynamic RBAC & Management (Permission Middleware) ---
    Route::middleware('permission')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::put('roles/{id}/permissions', [RoleController::class, 'updatePermissions']);

        // Sub-category CUD routes (Admin only via CheckPermission middleware)
        Route::post('/sub-categories', [SubCategoryController::class, 'store']);
        Route::put('/sub-categories/{id}', [SubCategoryController::class, 'update']);
        Route::delete('/sub-categories/{id}', [SubCategoryController::class, 'destroy']);

        // Menu Item CUD routes (Admin only via CheckPermission middleware)
        Route::post('/menu', [MenuItemController::class, 'store']);
        Route::put('/menu/{id}', [MenuItemController::class, 'update']);
        Route::delete('/menu/{id}', [MenuItemController::class, 'destroy']);

        // Table CUD routes (Admin only via CheckPermission middleware)
        Route::post('/tables', [TableController::class, 'store']);
        Route::put('/tables/{id}', [TableController::class, 'update']);
        Route::delete('/tables/{id}', [TableController::class, 'destroy']);

        // Customer CUD routes (Admin only via CheckPermission middleware)
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

        // Reservation CUD routes (Admin only via CheckPermission middleware)
        Route::post('/reservations', [ReservationController::class, 'store']);
        Route::put('/reservations/{id}', [ReservationController::class, 'update']);
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    });

        // --- Inventory, Suppliers & Purchases Modules ---
    // (Restricted to Admin via CheckPermission middleware, Waiter/Cashier have NO access)
    Route::middleware('permission')->group(function () {
        // Inventory - Admin only
        Route::get('/inventory', [InventoryController::class, 'index']);
        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock']);
        Route::post('/inventory', [InventoryController::class, 'store']);
        Route::get('/inventory/{id}', [InventoryController::class, 'show']);
        Route::put('/inventory/{id}', [InventoryController::class, 'update']);
        Route::put('/inventory/{id}/stock', [InventoryController::class, 'updateStock']);
        Route::delete('/inventory/{id}', [InventoryController::class, 'destroy']);

        // Suppliers - Admin only
        Route::apiResource('suppliers', SupplierController::class);

        // Purchases - Admin only
        Route::apiResource('purchases', PurchaseController::class);

        // Partners - Admin only
        Route::apiResource('partners', PartnerController::class);

        // Attendance manual management - Admin only
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
        Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);

        // Payroll - Admin only
        Route::get('/payroll', [PayrollController::class, 'index']);
        Route::post('/payroll/preview', [PayrollController::class, 'preview']);
        Route::post('/payroll/confirm-save', [PayrollController::class, 'confirmSave']);
        Route::post('/payroll/generate', [PayrollController::class, 'generate']);
        Route::get('/payroll/{id}', [PayrollController::class, 'show']);
        Route::put('/payroll/{id}/pay', [PayrollController::class, 'pay']);
        Route::put('/payroll/{id}', [PayrollController::class, 'update']);
        Route::delete('/payroll/{id}', [PayrollController::class, 'destroy']);
    });

});