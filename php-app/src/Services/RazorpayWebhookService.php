<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

final class RazorpayWebhookService
{
    public function verifySignature(string $payload, string $signature): bool
    {
        $secret = (string) Config::get('services.razorpay.webhook_secret', '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);
        return hash_equals($expected, $signature);
    }

    public function parseEvent(string $payload): array
    {
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : [];
    }
}
