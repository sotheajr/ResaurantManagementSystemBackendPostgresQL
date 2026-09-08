<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * TolaSaint KHQR Payment Gateway (ABA Bank / Bakong scan-to-pay).
 *
 * Documented API:
 *   - POST {api_url}              → create a KHQR payment.
 *       Headers: x-api-key: <key>
 *       Body:    { amount: "1.00", currency: "USD", provider: "aba", reference: "order-778" }
 *       Returns: id, qr_string, qr_link (hosted QR image), amount, currency, status, expiry
 *   - GET  {status_url}?id={id}   → { id, amount, currency, status }
 *       Statuses: pending, scanned, processing, paid, approved, failed, expired
 *       "paid" means the money arrived; approved/failed/expired are final.
 *   - GET  /qr/:token             → renders the KHQR as a scannable image (qr_link)
 */
class TolaSaintService
{
    /**
     * Create a KHQR payment and return the normalized gateway payload.
     *
     * @param  array{amount: float|string, currency?: string, reference?: string|null, order_id?: mixed}  $params
     * @return array{id: string, qr_string: string|null, qr_link: string|null, amount: float, currency: string,
     *               status: string, expiry: mixed, reference: string|null, transaction_id: string, payment_id: string, qr_url: string|null, raw: array}
     */
    public function createPayment(array $params): array
    {
        $apiUrl = config('tolasaint.api_url') ?: env('TOLA_SAINT_API_URL', env('TOLASAINT_API_URL', 'https://api.tolasaint.com/v1/payment'));
        $apiKey = config('tolasaint.api_key') ?: env('TOLA_SAINT_API_KEY', env('TOLASAINT_API_KEY'));

        if (!$apiUrl) {
            throw new \RuntimeException('TolaSaint API URL is not configured');
        }
        if (!$apiKey) {
            throw new \RuntimeException('TolaSaint API key is not configured');
        }

        $payload = [
            'amount' => number_format((float) ($params['amount'] ?? 0), 2, '.', ''),
            'currency' => strtoupper($params['currency'] ?? 'USD'),
            'provider' => strtolower((string) ($params['provider'] ?? config('tolasaint.provider', env('TOLA_SAINT_PROVIDER', env('TOLASAINT_PROVIDER', 'aba'))))),
            'reference' => $params['reference'] ?? ('order-' . ($params['order_id'] ?? '')),
        ];

        $response = Http::asJson()
            ->withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post($apiUrl, $payload);

        $body = $response->json() ?? [];

        if ($response->failed()) {
            $message = $body['message'] ?? $body['error'] ?? $body['error_description'] ?? ('TolaSaint API error (HTTP ' . $response->status() . ')');
            throw new \RuntimeException((string) $message);
        }

        $data = is_array($body) ? ($body['data'] ?? $body) : [];
        if (is_array($data) && isset($data['data']) && !isset($data['id'])) {
            $data = $data['data'];
        }

        return $this->normalizeCreated(is_array($data) ? $data : [], $payload);
    }

    /**
     * Poll the status of an existing payment by gateway id.
     *
     * @return array{id: string, status: string, is_paid: bool, is_final: bool, is_terminal: bool,
     *               amount: float|null, currency: string|null, raw: array}
     */
    public function checkStatus(string $id): array
    {
        $statusUrl = config('tolasaint.status_url') ?: env('TOLA_SAINT_STATUS_URL', env('TOLASAINT_STATUS_URL', 'https://api.tolasaint.com/v1/payment/status'));
        $apiKey = config('tolasaint.api_key') ?: env('TOLA_SAINT_API_KEY', env('TOLASAINT_API_KEY'));

        if (!$statusUrl) {
            throw new \RuntimeException('TolaSaint status URL is not configured');
        }
        if (!$apiKey) {
            throw new \RuntimeException('TolaSaint API key is not configured');
        }

        $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Accept' => 'application/json',
            ])
            ->get($statusUrl, ['id' => $id]);

        $body = $response->json() ?? [];

        if ($response->failed()) {
            $message = $body['message'] ?? $body['error'] ?? ('TolaSaint status check failed (HTTP ' . $response->status() . ')');
            throw new \RuntimeException((string) $message);
        }

        $data = is_array($body) ? ($body['data'] ?? $body) : [];
        if (is_array($data) && isset($data['data']) && !isset($data['id'])) {
            $data = $data['data'];
        }

        return $this->normalizeStatus(is_array($data) ? $data : []);
    }

    /**
     * Normalize a create-payment response from the gateway.
     *
     * TolaSaint returns: id, qr_string, qr_link, amount, currency, status, expiry.
     * Legacy aliases (transaction_id / payment_id / qr_url) are included so the
     * existing AbaPaymentModal flow keeps working unchanged.
     */
    protected function normalizeCreated(array $data, array $requestPayload = []): array
    {
        $id = $data['id'] ?? $data['payment_id'] ?? $data['transaction_id'] ?? $data['reference'] ?? null;

        $qrLink = $data['qr_link'] ?? $data['qrLink'] ?? $data['qr_url'] ?? $data['qrUrl'] ?? null;
        if (!$qrLink && $id) {
            $qrLink = rtrim(config('tolasaint.qr_base_url') ?: env('TOLA_SAINT_QR_BASE_URL', 'https://api.tolasaint.com/qr'), '/') . '/' . $id;
        }

        $qrString = $data['qr_string'] ?? $data['qrString'] ?? $data['qr_code'] ?? $data['qrCode'] ?? null;
        $amount = $data['amount'] ?? $requestPayload['amount'] ?? 0;
        $expiry = $data['expires_at'] ?? $data['expiry'] ?? $data['expiration'] ?? null;
        $statusValue = strtolower((string) ($data['status'] ?? $data['state'] ?? 'pending'));

        return [
            'id' => $id,
            'qr_string' => $qrString,
            'qr_link' => $qrLink,
            'amount' => (float) $amount,
            'currency' => strtoupper((string) ($data['currency'] ?? ($requestPayload['currency'] ?? 'USD'))),
            'status' => $statusValue,
            'expiry' => $expiry,
            'reference' => $data['reference'] ?? ($requestPayload['reference'] ?? null),
            'transaction_id' => $id,
            'payment_id' => $id,
            'qr_url' => $qrLink,
            'raw' => $data,
        ];
    }

    /**
     * Normalize a status-check response from the gateway.
     *
     * Statuses: pending, scanned, processing, paid, approved, failed, expired.
     * "paid" means the money arrived; approved/failed/expired are final.
     */
    protected function normalizeStatus(array $data): array
    {
        $statusValue = strtolower((string) ($data['status'] ?? $data['state'] ?? 'pending'));

        $isPaid = in_array($statusValue, ['paid', 'approved', 'success', 'completed', 'succeeded'], true)
            || filter_var($data['is_paid'] ?? $data['paid'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $isFinal = in_array($statusValue, ['paid', 'approved', 'failed', 'expired', 'cancelled', 'canceled', 'declined', 'error', 'rejected'], true);
        $isTerminal = in_array($statusValue, ['failed', 'expired', 'cancelled', 'canceled', 'declined', 'error', 'rejected'], true);

        return [
            'id' => $data['id'] ?? $data['payment_id'] ?? $data['transaction_id'] ?? null,
            'status' => $statusValue,
            'is_paid' => (bool) $isPaid,
            'is_final' => (bool) $isFinal,
            'is_terminal' => (bool) $isTerminal,
            'amount' => isset($data['amount']) ? (float) $data['amount'] : null,
            'currency' => isset($data['currency']) ? strtoupper((string) $data['currency']) : null,
            'raw' => $data,
        ];
    }
}