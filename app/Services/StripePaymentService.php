<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Stripe card payments (Visa / Mastercard) via the Stripe REST API.
 *
 * Uses the framework HTTP client with the configured secret key —
 * no Stripe SDK package required.
 */
class StripePaymentService
{
    /**
     * Create a PaymentIntent and return the normalized payload.
     *
     * @param  array{amount: float, currency?: string, order_id?: mixed, email?: string}  $params
     * @return array{client_secret: string, clientSecret: string, id: string, amount: float, currency: string}
     */
    public function createPaymentIntent(array $params): array
    {
        $amount = round((float) ($params['amount'] ?? 0), 2);
        $currency = strtolower($params['currency'] ?? 'usd');

        $form = [
            'amount' => (int) round($amount * 100),
            'currency' => $currency,
            'payment_method_types[0]' => 'card',
            'metadata[order_id]' => (string) ($params['order_id'] ?? ''),
        ];

        if (!empty($params['email'])) {
            $form['receipt_email'] = $params['email'];
        }

        $response = $this->client()->asForm()->post($this->url('payment_intents'), $form);
        $body = $response->json() ?? [];

        if ($response->failed()) {
            throw new \RuntimeException($body['error']['message'] ?? ('Stripe API error (HTTP ' . $response->status() . ')'));
        }

        return $this->normalizeIntent($body, $amount);
    }

    /**
     * Backward-compatible alias for the card-modal flow.
     *
     * Stripe PaymentElement requires a PaymentIntent client secret (pi_..._secret_...),
     * not a Checkout Session client secret (cs_...). This method therefore returns
     * the same normalized PaymentIntent payload as createPaymentIntent().
     */
    public function createCheckoutSession(array $params): array
    {
        return $this->createPaymentIntent($params);
    }

    /**
     * Confirm / verify a PaymentIntent by retrieving its latest state.
     *
     * @param  string  $paymentIntentId
     * @param  mixed   $orderId  optional order id for reconciliation when metadata is absent
     * @return array{status: string, paid: bool, amount: float, order_id: mixed, data: array}
     */
    public function confirmPayment(string $paymentIntentId, $orderId = null): array
    {
        $response = $this->client()->get($this->url('payment_intents/' . urlencode($paymentIntentId)));
        $body = $response->json() ?? [];

        if ($response->failed()) {
            throw new \RuntimeException($body['error']['message'] ?? ('Stripe API error (HTTP ' . $response->status() . ')'));
        }

        $status = strtolower((string) ($body['status'] ?? 'unknown'));
        $paid = in_array($status, ['succeeded', 'requires_capture'], true);

        return [
            'status' => $status,
            'paid' => $paid,
            'amount' => isset($body['amount']) ? ((float) $body['amount']) / 100 : 0,
            'order_id' => $body['metadata']['order_id'] ?? $orderId,
            'data' => $body,
        ];
    }

    /**
     * Build an authorized HTTP client for the Stripe API.
     */
    protected function client()
    {
        $secret = config('stripe.secret_key');

        if (!$secret) {
            throw new \RuntimeException('Stripe secret key is not configured');
        }

        return Http::withToken($secret)->acceptJson();
    }

    /**
     * Build a full Stripe API URL for the given path.
     */
    protected function url(string $path): string
    {
        return rtrim(config('stripe.base_url'), '/') . '/' . ltrim($path, '/');
    }

    /**
     * Normalize a PaymentIntent response.
     */
    protected function normalizeIntent(array $body, float $fallbackAmount): array
    {
        $clientSecret = $body['client_secret'] ?? null;
        $amount = isset($body['amount']) ? ((float) $body['amount']) / 100 : $fallbackAmount;

        return [
            'client_secret' => $clientSecret,
            'clientSecret' => $clientSecret,
            'id' => $body['id'] ?? null,
            'amount' => $amount,
            'currency' => strtolower($body['currency'] ?? 'usd'),
        ];
    }
}