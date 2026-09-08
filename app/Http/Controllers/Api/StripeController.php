<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\StripePaymentService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    use ApiResponseTrait;

    protected $stripeService;
    protected $paymentService;

    public function __construct(StripePaymentService $stripeService, PaymentService $paymentService)
    {
        $this->stripeService = $stripeService;
        $this->paymentService = $paymentService;
    }

    /**
     * Create a Stripe PaymentIntent.
     *
     * POST /api/stripe/payment-intent
     * Body: { amount, currency?, order_id, email? }
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $intent = $this->stripeService->createPaymentIntent([
                'amount' => $request->input('amount'),
                'currency' => $request->input('currency', 'usd'),
                'order_id' => $request->input('order_id'),
                'email' => $request->input('email'),
            ]);

            return $this->successResponse($intent);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }

    /**
     * Create an embedded Stripe Checkout Session (for the card modal).
     *
     * POST /api/stripe/checkout-session
     * Body: { total_amount, order_id, email? }
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            $session = $this->stripeService->createCheckoutSession([
                'amount' => $request->input('total_amount') ?? $request->input('amount'),
                'currency' => $request->input('currency', 'usd'),
                'order_id' => $request->input('order_id'),
                'email' => $request->input('email'),
            ]);

            return $this->successResponse($session);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }

    /**
     * Confirm / verify a Stripe PaymentIntent and finalize the order when paid.
     *
     * POST /api/stripe/confirm
     * Body: { payment_intent_id | paymentIntentId | id, order_id? }
     */
    public function confirm(Request $request)
    {
        try {
            $intentId = $request->input('payment_intent_id')
                ?? $request->input('paymentIntentId')
                ?? $request->input('payment_intent')
                ?? $request->input('id');

            if (empty($intentId)) {
                return $this->errorResponse('payment_intent_id is required', 422);
            }

            $result = $this->stripeService->confirmPayment($intentId, $request->input('order_id'));

            // When Stripe reports success, record the payment and mark the order paid.

            if ($result['paid']) {
                $orderId = $result['order_id'] ?? $request->input('order_id');

                if (!empty($orderId)) {
                    $this->paymentService->markOrderPaid($orderId, [
                        'payment_method' => 'Card',
                        'transaction_id' => $intentId,
                        'external_payment_id' => $intentId,
                    ]);
                }
            }

            return $this->successResponse([
                'status' => $result['status'],
                'paid' => $result['paid'],
                'amount' => $result['amount'],
                'order_id' => $result['order_id'],
                'data' => $result['data'],
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 502);
        }
    }
}