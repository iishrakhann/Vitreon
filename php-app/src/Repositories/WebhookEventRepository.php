<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class WebhookEventRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function create(array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO payment_webhook_events (
                provider, event_name, booking_reference, razorpay_order_id, razorpay_payment_id, payload_json
            ) VALUES (
                :provider, :event_name, :booking_reference, :razorpay_order_id, :razorpay_payment_id, :payload_json
            )'
        );

        $statement->execute([
            'provider' => $data['provider'],
            'event_name' => $data['event_name'],
            'booking_reference' => $data['booking_reference'] ?? null,
            'razorpay_order_id' => $data['razorpay_order_id'] ?? null,
            'razorpay_payment_id' => $data['razorpay_payment_id'] ?? null,
            'payload_json' => $data['payload_json'],
        ]);
    }
}
