<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Inventory;
use App\Models\Table;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function summary()
    {
        try {
            $totalUsers = User::count();
            $totalOrders = Order::count();
            $pendingOrders = Order::where('status', 'Pending')->count();
            $activeTables = Table::where('status', 'Occupied')->count();
            $lowStockItems = Inventory::whereColumn('quantity', '<=', 'minimum_stock')->count();

            // Calculate today's revenue
            $todayRevenue = Payment::whereDate('created_at', Carbon::today())
                ->sum('amount');

            $data = [
                'total_users' => $totalUsers,
                'total_orders' => $totalOrders,
                'today_revenue' => $todayRevenue,
                'pending_orders' => $pendingOrders,
                'active_tables' => $activeTables,
                'low_stock_items' => $lowStockItems,
            ];

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}