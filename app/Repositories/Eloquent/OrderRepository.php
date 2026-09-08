<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function getAll(string $type = 'history', array $filters = [])
    {
        $query = Order::with(['table', 'customer', 'user', 'items.menuItem']);

        switch ($type) {
            case 'active':
                $query->whereIn('status', ['pending', 'preparing', 'ready']);
                break;
            case 'completed':
                $query->where('status', 'completed');
                break;
            case 'history':
            default:
                // All statuses - paginated
                break;
        }

        // Apply date range filter
        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('customer_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('table', function ($tq) use ($search) {
                        $tq->where('table_number', 'like', "%{$search}%");
                    });
            });
        }

        // Apply status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply payment status filter
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        $query->orderBy('created_at', 'desc');

        // Paginate for history, get all for active/completed
        if ($type === 'history' || !empty($filters['paginate'])) {
            return $query->paginate($filters['per_page'] ?? 15);
        }

        return $query->get();
    }

    public function findById(int $id)
    {
        return Order::with(['table', 'customer', 'user', 'items.menuItem'])->findOrFail($id);
    }

    public function create(array $data, array $items = [])
    {
        return DB::transaction(function () use ($data, $items) {
            $order = Order::create($data);

            $totalAmount = 0;

            if (!empty($items)) {
                foreach ($items as $item) {
                    $price = $item['price'] ?? 0;
                    $quantity = $item['quantity'] ?? 1;
                    $subtotal = $price * $quantity;
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'menu_item_id' => $item['menu_item_id'],
                        'quantity' => $quantity,
                        'price' => $price,
                        'special_instructions' => $item['special_instructions'] ?? null,
                    ]);
                }
            }

            // Update total amount
            $order->total_amount = $totalAmount;
            $order->save();

            return $order->load(['table', 'customer', 'user', 'items.menuItem']);
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $order = Order::findOrFail($id);
            $order->update($data);
            return $order->load(['table', 'customer', 'user', 'items.menuItem']);
        });
    }

    public function updateStatus(int $id, string $status)
    {
        return DB::transaction(function () use ($id, $status) {
            $order = Order::findOrFail($id);
            $order->status = $status;

            // Automatically mark payment_status as 'paid' when status is 'completed'
            if ($status === 'completed') {
                $order->payment_status = 'paid';
            }

            $order->save();
            return $order->load(['table', 'customer', 'user', 'items.menuItem']);
        });
    }

    public function delete(int $id)
    {
        return DB::transaction(function () use ($id) {
            $order = Order::findOrFail($id);
            // Order items will be deleted via cascade
            return $order->delete();
        });
    }
}
