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
    // Falls back to the test secret key when the primary key is not set.
    'secret_key' => env('STRIPE_SECRET_KEY', env('STRIPE_TEST_SK')),

    // Publishable key used by the frontend for the card modal.
    'test_pk' => env('STRIPE_TEST_PK'),

    // Base URL of the Stripe API.
    'base_url' => env('STRIPE_API_URL', 'https://api.stripe.com/v1'),
];