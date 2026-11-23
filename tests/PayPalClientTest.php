<?php

namespace Tests;

use Illuminate\Support\Facades\Http;
use JawadAhmed\LaravelPayPal\PayPalClient;
use Orchestra\Testbench\TestCase;

final class PayPalClientTest extends TestCase
{
    public function test_it_creates_an_order(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'token']),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response(['id' => 'ORDER-1'], 201),
        ]);

        $result = (new PayPalClient('client', 'secret'))->createOrder('49.99', 'USD', 'INV-1');

        self::assertSame('ORDER-1', $result['id']);
        Http::assertSent(fn ($request) => $request->url() === 'https://api-m.sandbox.paypal.com/v2/checkout/orders'
            && $request['purchase_units'][0]['amount']['value'] === '49.99');
    }
}
