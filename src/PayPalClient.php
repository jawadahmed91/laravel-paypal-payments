<?php

namespace JawadAhmed\LaravelPayPal;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class PayPalClient
{
    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $mode = 'sandbox',
        private readonly ?string $webhookId = null,
    ) {}

    /** @return array<string, mixed> */
    public function createOrder(string $amount, string $currency = 'USD', ?string $invoiceId = null): array
    {
        return $this->api()->post('/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'invoice_id' => $invoiceId,
                'amount' => ['currency_code' => $currency, 'value' => $amount],
            ]],
        ])->throw()->json();
    }

    /** @return array<string, mixed> */
    public function captureOrder(string $orderId): array
    {
        return $this->api()->post("/v2/checkout/orders/{$orderId}/capture")->throw()->json();
    }

    /** @return array<string, mixed> */
    public function refundCapture(string $captureId, ?string $amount = null, string $currency = 'USD'): array
    {
        $payload = $amount === null ? [] : ['amount' => ['value' => $amount, 'currency_code' => $currency]];
        return $this->api()->post("/v2/payments/captures/{$captureId}/refund", $payload)->throw()->json();
    }

    /** @param array<string, string> $headers @param array<string, mixed> $event */
    public function verifyWebhook(array $headers, array $event): bool
    {
        if ($this->webhookId === null) {
            throw new RuntimeException('PAYPAL_WEBHOOK_ID is required.');
        }

        $response = $this->api()->post('/v1/notifications/verify-webhook-signature', [
            'auth_algo' => $headers['paypal-auth-algo'] ?? null,
            'cert_url' => $headers['paypal-cert-url'] ?? null,
            'transmission_id' => $headers['paypal-transmission-id'] ?? null,
            'transmission_sig' => $headers['paypal-transmission-sig'] ?? null,
            'transmission_time' => $headers['paypal-transmission-time'] ?? null,
            'webhook_id' => $this->webhookId,
            'webhook_event' => $event,
        ])->throw()->json();

        return ($response['verification_status'] ?? null) === 'SUCCESS';
    }

    private function api(): PendingRequest
    {
        $token = Http::asForm()->withBasicAuth($this->clientId, $this->clientSecret)
            ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()->json('access_token');

        return Http::baseUrl($this->baseUrl())->acceptJson()->withToken((string) $token);
    }

    private function baseUrl(): string
    {
        return $this->mode === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }
}
