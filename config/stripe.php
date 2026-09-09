<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Stripe Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe card payments (Visa / Mastercard).
    |
    */

    // Secret key used for server-side API calls.
    'secret_key' => env('STRIPE_SECRET_KEY', env('STRIPE_TEST_SK')),

    // Publishable key exposed to the frontend for embedded Stripe elements.
    'key' => env('STRIPE_KEY', env('STRIPE_PUBLISHABLE_KEY', env('STRIPE_TEST_PK'))),

    // Backward-compatible alias used by older code.
    'test_pk' => env('STRIPE_TEST_PK', env('STRIPE_KEY', env('STRIPE_PUBLISHABLE_KEY'))),

    // Base URL of the Stripe API.
    'base_url' => env('STRIPE_API_URL', 'https://api.stripe.com/v1'),
];