<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Admin (role_id = 1) has unrestricted access
        if ((int)$user->role_id === 1) {
            return $next($request);
        }

        // Determine required permission name
        $requiredPermission = $permission;

        if (!$requiredPermission) {
            $route = $request->route();
            if ($route) {
                $actionName = $route->getActionName(); // e.g. "App\Http\Controllers\Api\UserController@index"
                if ($actionName && str_contains($actionName, '@')) {
                    [$controllerClass, $method] = explode('@', $actionName);
                    $controllerName = class_basename($controllerClass);

                    // Map special actions / methods
                    $specialMethods = [
                        'updatePermissions' => 'manage_permissions',
                        'seatGuest' => 'seat_guest',
                        'seat_guest' => 'seat_guest',
                        'printReceipt' => 'print_receipt',
                        'print_receipt' => 'print_receipt',
                    ];

                    if (isset($specialMethods[$method])) {
                        $requiredPermission = $specialMethods[$method];
                    } else {
                        // Table/resource mapping
                        $map = [
                            'UserController' => 'users',
                            'RoleController' => 'roles',
                            'CategoryController' => 'categories',
                            'SubCategoryController' => 'sub_categories',
                            'MenuController' => 'menu',
                            'TableController' => 'tables',
                            'CustomerController' => 'customers',
                            'OrderController' => 'orders',
                            'PaymentController' => 'payments',
                            'ReservationController' => 'reservations',
                            'ReportController' => 'reports',
                            'InventoryController' => 'inventory',
                            'SupplierController' => 'suppliers',
                            'PurchaseController' => 'purchases',
                            'PartnerController' => 'partners',
                            'AttendanceController' => 'attendances',
                        ];

                        $tableName = $map[$controllerName] ?? null;

                        if ($tableName) {
                            $crudMap = [
                                'index' => 'view',
                                'show' => 'view',
                                'store' => 'create',
                                'create' => 'create',
                                'update' => 'update',
                                'destroy' => 'delete',
                            ];

                            $actionPrefix = $crudMap[$method] ?? null;
                            if ($actionPrefix) {
                                $requiredPermission = "{$actionPrefix}_{$tableName}";
                            }
                        }
                    }
                }
            }
        }

        if (!$requiredPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have the required permission.'
            ], 403);
        }

        // Check if the user has this permission with can_access = true/1
        $hasPermission = DB::table('role_permissions')
            ->where('role_id', $user->role_id)
            ->where('permission_name', $requiredPermission)
            ->where('can_access', true)
            ->exists();

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have the required permission.'
            ], 403);
        }

        return $next($request);
    }
}
