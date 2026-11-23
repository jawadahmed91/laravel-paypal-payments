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
