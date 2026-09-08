<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\TolaSaintService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    protected $paymentService;
    protected $tolaSaintService;

    public function __construct(PaymentService $paymentService, TolaSaintService $tolaSaintService)
    {
        $this->paymentService = $paymentService;
        $this->tolaSaintService = $tolaSaintService;
    }

    /**
     * List payments (used by the "Payment Success" history page).
     *
     * GET /api/payments
     */
    public function index(Request $request)
    {
        try {
            $paidOnly = $request->boolean('paid_only', true);
            $payments = $this->paymentService->getAllPayments($paidOnly);
            return $this->successResponse($payments);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Show a single payment.
     *
     * GET /api/payments/{id}
     */
    public function show($id)
    {
        try {
            $payment = $this->paymentService->findPayment($id);

            if (!$payment) {
                return $this->errorResponse('Payment not found', 404);
            }

            return $this->successResponse($payment);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Record a payment for an order and mark it as paid.
     *
     * POST /api/payments/process
     * Body: { order_id, payment_method, cashier_id?, amount?, transaction_id?, external_payment_id? }
     */
    public function process(Request $request)
    {
        try {
            $data = $request->only([
                'order_id',
                'payment_method',
                'cashier_id',
                'amount',
                'transaction_id',
                'external_payment_id',
            ]);

            if (empty($data['order_id'])) {
                return $this->errorResponse('order_id is required', 422);
            }

            if (empty($data['payment_method'])) {
                $data['payment_method'] = 'Cash';
            } elseif (strtolower((string) $data['payment_method']) === 'cash') {
                $data['payment_method'] = 'Cash';
            }

            $payment = $this->paymentService->processPayment($data);

            return $this->successResponse($payment, 'Payment recorded successfully', 201);
        } catch (\RuntimeException $e) {
            // Idempotency signal for the frontend ("already paid" flow).

            if (str_contains(strtolower($e->getMessage()), 'already paid')) {
                return $this->errorResponse($e->getMessage(), 422);
            }

            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Printable receipt for a payment (by id or external reference).
     *
     * GET /api/payments/{reference}/receipt
     */
    public function receipt($reference)
    {
        try {
            $receipt = $this->paymentService->getReceipt($reference);
            return $this->successResponse($receipt);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Payment not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Generate a KHQR payment for an order (TolaSaint / ABA Bank).
     *
     * POST /api/create-payment
     * Body: { order_id, total_price, currency? }
     *
     * Returns: { id, qr_url, qr_string, transaction_id, payment_id, amount, expiry }
     */
    public function createPayment(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $amount = (float) ($request->input('total_price') ?? $request->input('amount') ?? 0);
            $currency = $request->input('currency', 'USD');

            if (empty($orderId)) {
                return $this->errorResponse('order_id is required', 422);
            }

            $gateway = $this->tolaSaintService->createPayment([
                'amount' => $amount,
                'currency' => $currency,
                'order_id' => $orderId,
                'reference' => 'order-' . $orderId,
            ]);

            // Persist a pending payment row so the QR is linked to the order.

            $payment = $this->paymentService->markOrderPaidPending($orderId, [
                'payment_method' => 'ABA Bank',
                'transaction_id' => $gateway['id'],
                'external_payment_id' => $gateway['id'],
                'amount' => $gateway['amount'] ?: $amount,
            ]);

            return $this->successResponse([
                'id' => $gateway['id'],
                'qr_url' => $gateway['qr_link'],
                'qr_link' => $gateway['qr_link'],
                'qr_string' => $gateway['qr_string'],
                'transaction_id' => $gateway['id'],
                'payment_id' => $gateway['id'],
                'amount' => $gateway['amount'] ?: $amount,
                'expiry' => $gateway['expiry'],
                'status' => $gateway['status'],
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }

    /**
     * Poll KHQR payment status (TolaSaint / ABA Bank).
     *
     * GET /api/check-status?id={transactionId}&payment_id={paymentId}
     *
     * Returns: { is_paid, status, is_terminal, data }
     */
    public function checkStatus(Request $request)
    {
        try {
            $transactionId = (string) ($request->query('id') ?? $request->query('transaction_id') ?? '');
            $paymentId = $request->query('payment_id');

            if ($transactionId === '') {
                return $this->errorResponse('id (transaction id) is required', 422);
            }

            $gateway = $this->tolaSaintService->checkStatus($transactionId);

            // When the gateway reports a paid transaction, finalize the order.

            if ($gateway['is_paid']) {
                $payment = \App\Models\Payment::where('transaction_id', $transactionId)
                    ->orWhere('external_payment_id', $paymentId)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($payment) {
                    $this->paymentService->markOrderPaid($payment->order_id, [
                        'payment_method' => 'ABA Bank',
                        'transaction_id' => $transactionId,
                        'external_payment_id' => $paymentId,
                    ]);
                }
            }

            return $this->successResponse([
                'is_paid' => $gateway['is_paid'],
                'status' => $gateway['status'],
                'is_final' => $gateway['is_final'],
                'is_terminal' => $gateway['is_terminal'],
                'transaction_id' => $gateway['id'],
                'payment_id' => $gateway['id'],
                'data' => $gateway['raw'],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }

    /**
     * Generate an ABA KHQR payment for an order (TolaSaint rail).
     *
     * POST /api/payments/khqr/generate
     * Body: { order_id }
     *
     * Makes POST {TOLASAINT_API_URL} with header `x-api-key` and body
     * { amount: "1.00", currency: "USD", provider: "aba", reference: "order-{id}" }.
     *
     * Returns: { id, qr_string, qr_link, amount, currency, status, expiry, reference }
     */
    public function cash(Request $request)
    {
        return $this->process($request);
    }

    public function khqrGenerate(Request $request)
    {
        try {
            $orderId = $request->input('order_id');

            if (empty($orderId)) {
                return $this->errorResponse('order_id is required', 422);
            }

            $order = \App\Models\Order::findOrFail($orderId);

            if ($order->payment_status === 'paid') {
                return $this->errorResponse('Order payment already paid', 422);
            }

            $apiKey = env('TOLA_SAINT_API_KEY', config('tolasaint.api_key'));
            $payload = [
                'amount' => number_format((float) $order->total_amount, 2, '.', ''),
                'currency' => 'USD',
                'provider' => env('TOLA_SAINT_PROVIDER', config('tolasaint.provider', 'aba')),
                'reference' => 'order-' . $order->id,
            ];

            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post('https://api.tolasaint.com/v1/payment', $payload);

            $body = $response->json() ?? [];
            if ($response->failed()) {
                $message = $body['message'] ?? $body['error'] ?? $body['error_description'] ?? ('TolaSaint API error (HTTP ' . $response->status() . ')');
                return $this->errorResponse((string) $message, 502);
            }

            $data = $body['data'] ?? $body;
            if (is_array($data) && isset($data['data']) && !isset($data['id'])) {
                $data = $data['data'];
            }

            $paymentId = $data['id'] ?? $data['payment_id'] ?? $data['transaction_id'] ?? null;
            $qrLink = $data['qr_link'] ?? $data['qrLink'] ?? $data['qr_url'] ?? $data['qrUrl'] ?? null;
            if (!$qrLink && $paymentId) {
                $qrLink = 'https://api.tolasaint.com/qr/' . $paymentId;
            }

            $this->paymentService->markOrderPaidPending($order->id, [
                'payment_method' => 'ABA KHQR',
                'transaction_id' => $paymentId,
                'external_payment_id' => $paymentId,
                'amount' => (float) $order->total_amount,
            ]);

            return $this->successResponse([
                'id' => $paymentId,
                'qr_string' => $data['qr_string'] ?? $data['qrString'] ?? $data['qr_code'] ?? $data['qrCode'] ?? null,
                'qr_link' => $qrLink,
                'amount' => (float) $order->total_amount,
                'currency' => strtoupper($data['currency'] ?? 'USD'),
                'status' => strtolower((string) ($data['status'] ?? $data['state'] ?? 'pending')),
                'expiry' => $data['expires_at'] ?? $data['expiry'] ?? $data['expiration'] ?? null,
                'reference' => $data['reference'] ?? $payload['reference'],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Order not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }

    /**
     * Poll ABA KHQR payment status (TolaSaint rail).
     *
     * GET /api/payments/khqr/status?id={payment_id}
     *
     * When the gateway reports 'paid' or 'approved', atomically updates the
     * Order to status='completed' and payment_status='paid' and records the
     * payment, then reports the finalized order state to the caller.
     *
     * Returns: { id, status, is_paid, is_final, order: { id, status, payment_status } }
     */
    public function khqrStatus(Request $request)
    {
        try {
            $id = (string) ($request->query('id') ?? '');

            if ($id === '') {
                return $this->errorResponse('id (TolaSaint payment id) is required', 422);
            }

            $response = Http::withHeaders([
                'x-api-key' => env('TOLA_SAINT_API_KEY', config('tolasaint.api_key')),
                'Accept' => 'application/json',
            ])->get('https://api.tolasaint.com/v1/payment/status', ['id' => $id]);

            $body = $response->json() ?? [];
            if ($response->failed()) {
                $message = $body['message'] ?? $body['error'] ?? ('TolaSaint status check failed (HTTP ' . $response->status() . ')');
                return $this->errorResponse((string) $message, 502);
            }

            $data = $body['data'] ?? $body;
            if (is_array($data) && isset($data['data']) && !isset($data['id'])) {
                $data = $data['data'];
            }

            $status = strtolower((string) ($data['status'] ?? $data['state'] ?? 'pending'));
            $isPaid = in_array($status, ['paid', 'approved'], true)
                || filter_var($data['is_paid'] ?? $data['paid'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $payment = \App\Models\Payment::where('transaction_id', $id)
                ->orWhere('external_payment_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();

            $orderId = $payment?->order_id;

            if ($isPaid && $orderId) {
                DB::transaction(function () use ($orderId, $id) {
                    $order = \App\Models\Order::findOrFail($orderId);
                    $order->status = 'completed';
                    $order->payment_status = 'paid';
                    $order->save();

                    $paymentRecord = \App\Models\Payment::where('order_id', $orderId)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if ($paymentRecord) {
                        $paymentRecord->payment_status = 'paid';
                        $paymentRecord->transaction_id = $paymentRecord->transaction_id ?? $id;
                        $paymentRecord->external_payment_id = $paymentRecord->external_payment_id ?? $id;
                        $paymentRecord->paid_at = $paymentRecord->paid_at ?? now();
                        $paymentRecord->save();
                    }
                });
            }

            $order = $orderId ? \App\Models\Order::find($orderId) : null;

            return $this->successResponse([
                'id' => $data['id'] ?? $id,
                'status' => $status,
                'is_paid' => $isPaid,
                'is_final' => in_array($status, ['paid', 'approved', 'failed', 'expired'], true),
                'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
                'currency' => isset($data['currency']) ? strtoupper($data['currency']) : 'USD',
                'order' => $order ? [
                    'id' => $order->id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                ] : null,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }
}