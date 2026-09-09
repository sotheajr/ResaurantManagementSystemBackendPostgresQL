<?php

namespace Tests\Feature;

use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StripeCheckoutSessionTest extends TestCase
{
    public function test_legacy_checkout_session_route_returns_payment_intent_client_secret(): void
    {
        Http::fake([
            'https://api.stripe.com/v1/payment_intents' => Http::response([
                'id' => 'pi_test_123',
                'client_secret' => 'pi_test_123_secret_456',
                'amount' => 2550,
                'currency' => 'usd',
                'status' => 'requires_payment_method',
            ], 200),
        ]);

        $service = new StripePaymentService();
        $result = $service->createCheckoutSession([
            'amount' => 25.5,
            'order_id' => 42,
            'email' => 'customer@example.com',
        ]);

        $this->assertSame('pi_test_123_secret_456', $result['clientSecret']);
        $this->assertStringStartsWith('pi_', $result['clientSecret']);

        Http::assertSent(function ($request) {
            $url = (string) $request->url();
            $body = $request->data();

            return str_contains($url, '/v1/payment_intents')
                && isset($body['amount'])
                && (string) $body['amount'] === '2550';
        });
    }
}
