<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

final class RazorpayOrderService
{
    public function createOrder(array $booking, array $venue): array
    {
        $keyId = (string) Config::get('services.razorpay_api.key_id', '');
        $keySecret = (string) Config::get('services.razorpay_api.key_secret', '');

        if ($keyId === '' || $keySecret === '') {
            return [
                'id' => 'demo_order_' . strtolower($booking['booking_reference']),
                'status' => 'created',
                'amount' => (int) round(((float) $booking['deposit_amount']) * 100),
                'currency' => 'INR',
                'notes' => [
                    'booking_reference' => $booking['booking_reference'],
                    'owner_phone' => $venue['ownerPhone'] ?? '',
                    'venue_name' => $venue['name'] ?? '',
                ],
                'mode' => 'demo',
            ];
        }

        $payload = json_encode([
            'amount' => (int) round(((float) $booking['deposit_amount']) * 100),
            'currency' => 'INR',
            'receipt' => $booking['booking_reference'],
            'notes' => [
                'booking_reference' => $booking['booking_reference'],
                'owner_phone' => $venue['ownerPhone'] ?? '',
                'venue_name' => $venue['name'] ?? '',
            ],
        ], JSON_UNESCAPED_SLASHES);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", [
                    'Authorization: Basic ' . base64_encode($keyId . ':' . $keySecret),
                    'Content-Type: application/json',
                ]) . "\r\n",
                'content' => $payload ?: '{}',
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $response = file_get_contents('https://api.razorpay.com/v1/orders', false, $context);
        return is_string($response) ? (json_decode($response, true) ?: []) : [];
    }
}
