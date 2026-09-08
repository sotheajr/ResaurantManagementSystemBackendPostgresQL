<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * List payments, newest first. Optionally filter to paid only.
     */
    public function getAllPayments(bool $paidOnly = true)
    {
        $query = Payment::with(['order.table', 'order.customer', 'cashier']);

        if ($paidOnly) {
            $query->paid();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Find a payment by id or external reference (transaction / payment id).
     */
    public function findPayment($reference)
    {
        return Payment::with(['order.table', 'order.customer', 'order.items.menuItem', 'cashier'])
            ->where('id', $reference)
            ->orWhere('external_payment_id', $reference)
            ->orWhere('transaction_id', $reference)
            ->first();
    }

    /**
     * Record a payment for an order and mark the order as paid.
     *
     * Idempotency: when the order is already paid a RuntimeException with
     * an "already paid" message is thrown so the caller can react.
     *
     * @param  array{order_id: mixed, payment_method: string, cashier_id?: mixed, amount?: float, transaction_id?: string|null, external_payment_id?: string|null}  $data
     * @return Payment
     */
    public function processPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $order = Order::with('items')->findOrFail($data['order_id']);

            if (strtolower((string) ($order->payment_status ?? '')) === 'paid') {
                throw new \RuntimeException('Order payment already paid — a payment record already exists.');
            }

            $amount = isset($data['amount']) && $data['amount'] !== null
                ? (float) $data['amount']
                : (float) $order->total_amount;

            $payment = Payment::create([
                'order_id' => $order->id,
                'cashier_id' => $data['cashier_id'] ?? null,
                'payment_method' => $data['payment_method'] ?? 'Cash',
                'transaction_id' => $data['transaction_id'] ?? null,
                'external_payment_id' => $data['external_payment_id'] ?? null,
                'amount' => $amount,
                'currency' => 'USD',
                'payment_status' => 'paid',
                'receipt_no' => $this->generateReceiptNo($order->id),
                'paid_at' => now(),
            ]);

            // Finalize the order: mark paid and completed (unless cancelled).
            $order->payment_status = 'paid';
            if (strtolower((string) $order->status) !== 'cancelled') {
                $order->status = 'completed';
            }
            $order->save();

            return $payment;
        });
    }

    /**
     * Create (or update) a pending payment record linked to an order.
     * Used when a gateway QR is generated, before the customer pays.
     */
    public function markOrderPaidPending($orderId, array $gateway = []): Payment
    {
        $order = Order::findOrFail($orderId);

        $payment = Payment::where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$payment) {
            $payment = new Payment([
                'order_id' => $order->id,
                'payment_method' => $gateway['payment_method'] ?? 'ABA Bank',
                'amount' => (float) ($gateway['amount'] ?? $order->total_amount),
                'currency' => 'USD',
                'receipt_no' => $this->generateReceiptNo($order->id),
            ]);
        }

        $payment->payment_status = 'pending';
        $payment->transaction_id = $gateway['transaction_id'] ?? $payment->transaction_id;
        $payment->external_payment_id = $gateway['external_payment_id'] ?? $payment->external_payment_id;
        $payment->amount = isset($gateway['amount']) ? (float) $gateway['amount'] : ($payment->amount ?: (float) $order->total_amount);
        $payment->currency = $payment->currency ?: 'USD';
        $payment->payment_method = $gateway['payment_method'] ?? $payment->payment_method ?? 'ABA Bank';
        $payment->save();

        return $payment;
    }

    /**
     * Mark an order as paid (used when a gateway confirms a payment).
     * Creates the payment record when it does not exist yet.
     */
    public function markOrderPaid($orderId, array $gateway = []): Payment
    {
        $order = Order::findOrFail($orderId);

        $existing = Payment::where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existing && strtolower((string) $existing->payment_status) === 'paid') {
            return $existing;
        }

        return DB::transaction(function () use ($order, $gateway, $existing) {
            $payment = $existing ?: new Payment([
                'order_id' => $order->id,
                'payment_method' => $gateway['payment_method'] ?? 'ABA Bank',
                'amount' => (float) $order->total_amount,
                'currency' => 'USD',
                'receipt_no' => $this->generateReceiptNo($order->id),
            ]);

            $payment->payment_status = 'paid';
            $payment->transaction_id = $payment->transaction_id ?? ($gateway['transaction_id'] ?? null);
            $payment->external_payment_id = $payment->external_payment_id ?? ($gateway['external_payment_id'] ?? null);
            $payment->cashier_id = $payment->cashier_id ?? ($gateway['cashier_id'] ?? null);
            $payment->amount = (float) ($gateway['amount'] ?? $payment->amount ?? $order->total_amount);
            $payment->currency = $payment->currency ?: 'USD';
            $payment->payment_method = $gateway['payment_method'] ?? $payment->payment_method ?? 'ABA Bank';
            $payment->paid_at = $payment->paid_at ?? now();
            $payment->save();

            $order->payment_status = 'paid';
            if (strtolower((string) $order->status) !== 'cancelled') {
                $order->status = 'completed';
            }
            $order->save();

            return $payment->load(['order.table', 'order.customer', 'cashier']);
        });
    }

    /**
     * Build the printable receipt payload for a payment (by id or external reference).
     */
    public function getReceipt($reference): array
    {
        $payment = $this->findPayment($reference);

        if (!$payment) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Payment not found');
        }

        $order = $payment->order()->with(['items.menuItem', 'table', 'customer', 'user'])->first();

        $items = collect($order?->items ?? [])->map(function ($item) {
            $name = $item->menuItem->name ?? 'Item';
            $qty = (int) ($item->quantity ?? 1);
            $price = (float) ($item->price ?? 0);

            return [
                'item' => $name,
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->values()->all();

        $subtotal = (float) ($order->total_amount ?? $payment->amount);

        return [
            'receipt_no' => $payment->receipt_no ?? ('RCP-' . $payment->id),
            'date' => $payment->paid_at ?? $payment->created_at,
            'order_id' => $order?->id ?? $payment->order_id,
            'payment_id' => $payment->id,
            'payment_method' => $payment->payment_method,
            'payment_status' => $payment->payment_status,
            'transaction_id' => $payment->transaction_id,
            'customer' => $order?->customer->customer_name ?? 'Walk-in',
            'cashier' => $payment->cashier->username ?? $order?->user->username ?? null,
            'table' => $order?->table->table_number ?? null,
            'items' => $items,
            'subtotal' => $subtotal,
            'discount_amount' => null,
            'discount_percent' => null,
            'total' => $subtotal,
        ];
    }

    /**
     * Generate a unique receipt number for an order.
     */
    protected function generateReceiptNo($orderId): string
    {
        return 'RCP-' . date('Ymd') . '-' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
    }
}