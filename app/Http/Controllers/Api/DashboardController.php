<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ApiResponseTrait;

    public function summary()
    {
        try {
            $totalUsers = User::count();
            $totalOrders = Order::count();
            $pendingOrders = Order::whereIn('status', ['pending', 'preparing', 'ready'])->count();
            $activeTables = Table::whereIn('status', ['Occupied', 'Reserved'])->count();
            $lowStockItems = Inventory::whereColumn('quantity', '<=', 'minimum_stock')->count();

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

    public function stats(Request $request)
    {
        try {
            $range = strtolower((string) $request->query('range', 'today'));
            $range = in_array($range, ['today', 'week', 'month', 'year'], true) ? $range : 'today';

            [$start, $end] = $this->resolveRange($range);

            $ordersQuery = Order::whereBetween('created_at', [$start, $end]);
            $paymentsQuery = Payment::whereBetween('created_at', [$start, $end]);

            $totalRevenue = (float) $paymentsQuery->sum('amount');
            $totalOrders = (int) $ordersQuery->count();
            $pendingOrders = (int) Order::whereBetween('created_at', [$start, $end])
                ->whereIn('status', ['pending', 'preparing', 'ready'])
                ->count();
            $activeTables = (int) Table::whereIn('status', ['Occupied', 'Reserved'])->count();
            $lowStockItems = (int) Inventory::whereColumn('quantity', '<=', 'minimum_stock')->count();

            $summary = [
                'revenue' => $totalRevenue,
                'orders' => $totalOrders,
                'customers' => (int) User::count(),
                'avgOrderValue' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                'pendingOrders' => $pendingOrders,
                'activeTables' => $activeTables,
                'lowStockItems' => $lowStockItems,
                'tableOccupancy' => $this->getTableOccupancyPercent(),
            ];

            $chart = $this->buildChartData($range, $start, $end);
            $paymentMethods = $this->buildPaymentMethodBreakdown($start, $end);
            $topItems = $this->buildTopItems($start, $end);
            $tableStatus = $this->buildTableStatusBreakdown();
            $recentOrders = $this->buildRecentOrders();
            $weeklyRevenue = $this->buildMonthlyWeeklyRevenueComparison();

            return $this->successResponse([
                'range' => $range,
                'summary' => $summary,
                'chart' => $chart,
                'current_month_weeks' => $weeklyRevenue['current_month_weeks'],
                'previous_month_weeks' => $weeklyRevenue['previous_month_weeks'],
                'paymentMethods' => $paymentMethods,
                'topItems' => $topItems,
                'tableStatus' => $tableStatus,
                'recentOrders' => $recentOrders,
            ], 'Dashboard stats loaded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    private function resolveRange(string $range): array
    {
        $now = Carbon::now();

        return match ($range) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    private function buildChartData(string $range, Carbon $start, Carbon $end): array
    {
        $points = [];

        if ($range === 'today') {
            for ($hour = 0; $hour < 24; $hour++) {
                $bucketStart = $start->copy()->addHours($hour)->startOfHour();
                $bucketEnd = $bucketStart->copy()->endOfHour();

                $sales = (float) Payment::whereBetween('created_at', [$bucketStart, $bucketEnd])->sum('amount');
                $orders = (int) Order::whereBetween('created_at', [$bucketStart, $bucketEnd])->count();

                $points[] = [
                    'label' => $bucketStart->format('D hA'),
                    'date' => $bucketStart->format('M d'),
                    'sales' => $sales,
                    'orders' => $orders,
                ];
            }

            return $points;
        }

        if ($range === 'week') {
            $periods = 7;
            $step = 'day';
        } elseif ($range === 'month') {
            $periods = $start->daysInMonth;
            $step = 'day';
        } else {
            $periods = 12;
            $step = 'month';
        }

        for ($i = 0; $i < $periods; $i++) {
            if ($step === 'month') {
                $bucketStart = $start->copy()->addMonths($i)->startOfMonth();
                $bucketEnd = $bucketStart->copy()->endOfMonth();
                $label = $bucketStart->format('M');
            } else {
                $bucketStart = $start->copy()->addDays($i)->startOfDay();
                $bucketEnd = $bucketStart->copy()->endOfDay();
                $label = $bucketStart->format('d');
            }

            $sales = (float) Payment::whereBetween('created_at', [$bucketStart, $bucketEnd])->sum('amount');
            $orders = (int) Order::whereBetween('created_at', [$bucketStart, $bucketEnd])->count();

            $points[] = [
                'label' => $label,
                'date' => $bucketStart->format('M d'),
                'sales' => $sales,
                'orders' => $orders,
            ];
        }

        return $points;
    }

    private function buildPaymentMethodBreakdown(Carbon $start, Carbon $end): array
    {
        return Payment::selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'payment_method' => $item->payment_method ?? 'Unknown',
                    'total' => (float) $item->total,
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();
    }

    private function buildTopItems(Carbon $start, Carbon $end): array
    {
        return OrderItem::select([
            'order_items.menu_item_id',
            'menu_items.menu_name as name',
            DB::raw('SUM(order_items.quantity) as qty_sold'),
            DB::raw('SUM(order_items.quantity * order_items.price) as revenue'),
        ])
            ->join('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->whereBetween('order_items.created_at', [$start, $end])
            ->groupBy('order_items.menu_item_id', 'menu_items.menu_name')
            ->orderByDesc('qty_sold')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name ?? 'Menu item',
                    'qty_sold' => (int) $item->qty_sold,
                    'revenue' => (float) $item->revenue,
                ];
            })
            ->toArray();
    }

    private function buildTableStatusBreakdown(): array
    {
        return Table::select(['status', DB::raw('COUNT(*) as count')])
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status ?? 'Unknown',
                    'count' => (int) $item->count,
                ];
            })
            ->toArray();
    }

    private function buildRecentOrders(): array
    {
        return Order::with(['table', 'customer'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'table_number' => $order->table?->table_number ?? 'N/A',
                    'customer_name' => $order->customer?->customer_name ?? 'Walk-in',
                    'total_amount' => (float) $order->total_amount,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                ];
            })
            ->toArray();
    }

    private function buildMonthlyWeeklyRevenueComparison(): array
    {
        $currentMonthStart = Carbon::now()->copy()->startOfMonth();
        $previousMonthStart = Carbon::now()->copy()->subMonth()->startOfMonth();

        return [
            'current_month_weeks' => $this->buildWeeklyRevenueForMonth($currentMonthStart),
            'previous_month_weeks' => $this->buildWeeklyRevenueForMonth($previousMonthStart),
        ];
    }

    private function buildWeeklyRevenueForMonth(Carbon $monthStart): array
    {
        $monthEnd = $monthStart->copy()->endOfMonth();
        $weeks = [0, 0, 0, 0];

        $payments = Payment::whereBetween('created_at', [$monthStart, $monthEnd])->get();

        foreach ($payments as $payment) {
            if (empty($payment->created_at)) {
                continue;
            }

            $dayOfMonth = Carbon::parse($payment->created_at)->day;
            $weekIndex = min(3, (int) floor(($dayOfMonth - 1) / 7));
            $weeks[$weekIndex] += (float) ($payment->amount ?? 0);
        }

        return array_map(fn ($value) => round((float) $value, 2), $weeks);
    }

    private function getTableOccupancyPercent(): float
    {
        $totalTables = (int) Table::count();
        if ($totalTables === 0) {
            return 0;
        }

        $occupied = (int) Table::whereIn('status', ['Occupied', 'Reserved'])->count();

        return round(($occupied / $totalTables) * 100, 1);
    }
}