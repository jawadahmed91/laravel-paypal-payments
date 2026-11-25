# Laravel PayPal Payments


Laravel 13 service code for PayPal Orders v2: OAuth authentication, order creation, capture, refunds, and webhook verification.


```bash
composer install
composer test
```


Configure `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`, and `PAYPAL_MODE=sandbox`. Keep all values in the environment; never commit credentials.


```php
$order = $paypal->createOrder('49.99', 'USD', 'INV-1001');
$capture = $paypal->captureOrder($order['id']);
```


Use HTTPS, persist PayPal IDs, enforce idempotency in your application, and verify every webhook before processing it.

## Add this to an existing Laravel project

1. Copy `src/PayPalClient.php` to `app/Payments/PayPal/PayPalClient.php` and change its namespace to `App\Payments\PayPal`.
2. Copy `config/paypal.php` into your application's `config` directory.
3. Add `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`, and `PAYPAL_MODE=sandbox` to `.env` using credentials from a PayPal sandbox application.
4. Bind `PayPalClient` as a singleton in a service provider using the values from `config('paypal')`.
5. In the checkout endpoint, load the order from your database, calculate the amount and currency on the server, then call `createOrder()`. Store the returned PayPal order ID against your order.
6. Send the PayPal order ID to the browser for buyer approval. After approval, call `captureOrder()` on the server and store the capture ID and final status.
7. Add a unique database constraint or payment-attempt record so retries cannot create or capture multiple payments for the same order.
8. Create a webhook endpoint that reads the PayPal transmission headers and decoded event, calls `verifyWebhook()`, rejects unverified requests, and processes each event ID only once.
9. Use the stored capture ID when calling `refundCapture()` and record the refund ID and amount.
10. Copy `tests/PayPalClientTest.php` into your project, update its namespace and imports, and run `php artisan test`.
11. Complete create, approve, capture, webhook, and refund flows in sandbox before changing `PAYPAL_MODE` to `live`.
