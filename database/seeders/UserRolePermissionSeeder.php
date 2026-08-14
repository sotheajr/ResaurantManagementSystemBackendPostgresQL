<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Insert Roles (if not already present)
        $roles = [
            ['role_id' => 1, 'role_name' => 'Admin', 'role_name_kh' => 'អ្នកគ្រប់គ្រង'],
            ['role_id' => 2, 'role_name' => 'Waiter', 'role_name_kh' => 'អ្នករត់តុ'],
            ['role_id' => 3, 'role_name' => 'Cashier', 'role_name_kh' => 'អ្នកគិតលុយ'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['role_id' => $role['role_id']],
                $role
            );
        }

        // 2. Define all tables/resources and actions
        $resources = [
            'users', 'roles', 'categories', 'sub_categories', 'menu', 'tables', 
            'customers', 'orders', 'payments', 'reservations', 'reports', 
            'inventory', 'suppliers', 'purchases', 'partners', 'attendances'
        ];

        $actions = ['view', 'create', 'update', 'delete'];
        $allPermissions = [];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                $allPermissions[] = "{$action}_{$resource}";
            }
        }

        // Add special actions
        $specialActions = ['seat_guest', 'print_receipt', 'manage_permissions'];
        foreach ($specialActions as $special) {
            $allPermissions[] = $special;
        }

        // Define default allowed permissions for Waiter (Role_ID: 2) and Cashier (Role_ID: 3)
        $waiterAllowed = [
            'view_menu', 'view_categories', 'view_sub_categories', 'view_tables', 
            'view_orders', 'create_orders', 'update_orders', 
            'view_payments', 'view_reservations', 'create_reservations', 
            'seat_guest'
        ];

        $cashierAllowed = [
            'view_categories', 'create_categories', 'update_categories', 'delete_categories',
            'view_sub_categories', 'create_sub_categories', 'update_sub_categories', 'delete_sub_categories',
            'view_menu', 'create_menu', 'update_menu', 'delete_menu',
            'view_tables', 'create_tables', 'update_tables', 'delete_tables',
            'view_customers', 'create_customers', 'update_customers', 'delete_customers',
            'view_reservations', 'create_reservations', 'update_reservations', 'delete_reservations',
            'view_orders', 'create_orders', 'update_orders', 'delete_orders',
            'view_payments', 'create_payments', 'update_payments', 'delete_payments',
            'view_reports', 
            'view_inventory', 'create_inventory', 'update_inventory', 'delete_inventory',
            'view_suppliers', 'create_suppliers', 'update_suppliers', 'delete_suppliers',
            'print_receipt', 'seat_guest'
        ];

        // 3. Upsert permissions for roles
        foreach ($allPermissions as $permName) {
            // Admin (1): can_access = true for all
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => 1, 'permission_name' => $permName],
                ['can_access' => true, 'created_at' => now(), 'updated_at' => now()]
            );

            // Waiter (2)
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => 2, 'permission_name' => $permName],
                ['can_access' => in_array($permName, $waiterAllowed), 'created_at' => now(), 'updated_at' => now()]
            );

            // Cashier (3)
            DB::table('role_permissions')->updateOrInsert(
                ['role_id' => 3, 'permission_name' => $permName],
                ['can_access' => in_array($permName, $cashierAllowed), 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 4. Insert or update initial Users
        $users = [
            [
                'username' => 'admin',
                'full_name' => 'System Admin',
                'role_id' => 1,
                'password' => Hash::make('admin123'),
                'email' => 'admin@restaurant.com',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'waiter1',
                'full_name' => 'John Waiter',
                'role_id' => 2,
                'password' => Hash::make('admin123'),
                'email' => 'waiter1@restaurant.com',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'cashier1',
                'full_name' => 'Sarah Cashier',
                'role_id' => 3,
                'password' => Hash::make('admin123'),
                'email' => 'cashier1@restaurant.com',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($users as $userData) {
            DB::table('users')->updateOrInsert(
                ['username' => $userData['username']],
                $userData
            );
        }
    }
}

