<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

final class BookingValidatorClient
{
    public function holdSlot(int $slotId, string $bookingReference, string $userId): array
    {
        $baseUrl = rtrim((string) Config::get('services.booking_validator.base_url', ''), '/');
        if ($baseUrl === '') {
            return [
                'status' => 'UNAVAILABLE',
                'message' => 'Validator service is not configured.',
            ];
        }

        $url = $baseUrl . '/api/bookings/validate';

        $payload = json_encode([
            'slotId' => $slotId,
            'bookingReference' => $bookingReference,
            'userId' => $userId,
        ], JSON_UNESCAPED_SLASHES);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload ?: '{}',
                'ignore_errors' => true,
                'timeout' => 15,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if (!is_string($response)) {
            return [
                'status' => 'UNAVAILABLE',
                'message' => 'Validator service is offline. Using local booking hold.',
            ];
        }

        return json_decode($response, true) ?: [
            'status' => 'UNAVAILABLE',
            'message' => 'Validator service returned an unreadable response.',
        ];
    }
}
