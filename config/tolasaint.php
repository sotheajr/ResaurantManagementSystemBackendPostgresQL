<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TolaSaint KHQR Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Configuration for the TolaSaint KHQR payment provider used for
    | ABA Bank / Bakong mobile payment (scan-to-pay) in Cambodia.
    |
    */

    // API key sent as the `x-api-key` header on every request.

    'api_key' => env('TOLASAINT_API_KEY', env('TOLA_SAINT_API_KEY')),

    // Endpoint used to create a new payment / generate the KHQR code.
    'api_url' => env('TOLASAINT_API_URL', env('TOLA_SAINT_API_URL', 'https://api.tolasaint.com/v1/payment')),

    // Endpoint used to poll the status of an existing payment (?id=...).
    'status_url' => env('TOLASAINT_STATUS_URL', env('TOLA_SAINT_STATUS_URL', 'https://api.tolasaint.com/v1/payment/status')),

    // Endpoint listing payments (used for reconciliation / lookups).
    'list_url' => env('TOLASAINT_LIST_URL', env('TOLA_SAINT_LIST_URL', 'https://api.tolasaint.com/v1/payments')),

    // Payment rail: 'aba' (default) or 'bakong'.
    'provider' => env('TOLASAINT_PROVIDER', env('TOLA_SAINT_PROVIDER', 'aba')),

    // Base URL that renders a KHQR as a scannable image: /qr/{token}.
    'qr_base_url' => env('TOLA_SAINT_QR_BASE_URL', 'https://api.tolasaint.com/qr'),
];