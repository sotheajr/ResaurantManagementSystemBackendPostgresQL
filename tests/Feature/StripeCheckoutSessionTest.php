<?php

namespace Tests\Feature;

use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeCheckoutSessionTest extends TestCase
{
    public function test_embedded_checkout_session_requires_return_url_or_redirect_policy(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'client_secret' => 'cs_test_secret',
                'status' => 'open',
            ], 200),
        ]);

        $service = new StripePaymentService();
        $service->createCheckoutSession([
            'amount' => 25.5,
            'order_id' => 42,
            'email' => 'customer@example.com',
        ]);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return isset($body['return_url'])
                && $body['ui_mode'] === 'embedded';
        });
    }
}
