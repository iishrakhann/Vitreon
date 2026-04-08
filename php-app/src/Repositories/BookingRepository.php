<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class BookingRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function createPending(array $data): array
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO bookings (
                user_id, venue_slot_id, booking_reference, hold_reference, total_amount, deposit_amount,
                payment_status, booking_status, razorpay_order_id, venue_name, owner_phone
            ) VALUES (
                :user_id, :venue_slot_id, :booking_reference, :hold_reference, :total_amount, :deposit_amount,
                :payment_status, :booking_status, :razorpay_order_id, :venue_name, :owner_phone
            )'
        );

        $statement->execute([
            'user_id' => $data['user_id'],
            'venue_slot_id' => $data['venue_slot_id'],
            'booking_reference' => $data['booking_reference'],
            'hold_reference' => $data['hold_reference'],
            'total_amount' => $data['total_amount'],
            'deposit_amount' => $data['deposit_amount'],
            'payment_status' => $data['payment_status'] ?? 'PENDING',
            'booking_status' => $data['booking_status'] ?? 'PENDING_REVIEW',
            'razorpay_order_id' => $data['razorpay_order_id'] ?? null,
            'venue_name' => $data['venue_name'] ?? null,
            'owner_phone' => $data['owner_phone'] ?? null,
        ]);

        return $this->findByReference((string) $data['booking_reference']) ?? [];
    }

    public function attachRazorpayOrder(string $bookingReference, string $orderId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings SET razorpay_order_id = :razorpay_order_id WHERE booking_reference = :booking_reference'
        );
        $statement->execute([
            'razorpay_order_id' => $orderId,
            'booking_reference' => $bookingReference,
        ]);
    }

    public function markManualPaymentSubmitted(string $bookingReference, string $paymentReference): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings
             SET razorpay_payment_id = :payment_reference
             WHERE booking_reference = :booking_reference'
        );
        $statement->execute([
            'payment_reference' => $paymentReference,
            'booking_reference' => $bookingReference,
        ]);
    }

    public function markDepositPaid(string $bookingReference, string $paymentId, string $status = 'DEPOSIT_PAID'): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings
             SET payment_status = :payment_status, razorpay_payment_id = :razorpay_payment_id, paid_at = CURRENT_TIMESTAMP
             WHERE booking_reference = :booking_reference'
        );
        $statement->execute([
            'payment_status' => $status,
            'razorpay_payment_id' => $paymentId,
            'booking_reference' => $bookingReference,
        ]);

        $booking = $this->findByReference($bookingReference);
        if (is_array($booking) && !empty($booking['venue_slot_id'])) {
            $slotStatement = $this->pdo->prepare(
                'UPDATE venue_slots
                 SET status = \'BOOKED\',
                     hold_reference = :hold_reference,
                     hold_expires_at = NULL
                 WHERE id = :id'
            );
            $slotStatement->execute([
                'id' => $booking['venue_slot_id'],
                'hold_reference' => $booking['hold_reference'] ?? null,
            ]);
        }
    }

    public function markFailed(string $bookingReference): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings SET payment_status = :payment_status WHERE booking_reference = :booking_reference'
        );
        $statement->execute([
            'payment_status' => 'FAILED',
            'booking_reference' => $bookingReference,
        ]);

        $booking = $this->findByReference($bookingReference);
        if (is_array($booking) && !empty($booking['venue_slot_id'])) {
            $slotStatement = $this->pdo->prepare(
                'UPDATE venue_slots
                 SET status = \'AVAILABLE\',
                     hold_reference = NULL,
                     hold_expires_at = NULL
                 WHERE id = :id'
            );
            $slotStatement->execute(['id' => $booking['venue_slot_id']]);
        }
    }

    public function findByReference(string $bookingReference): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM bookings WHERE booking_reference = :booking_reference LIMIT 1');
        $statement->execute(['booking_reference' => $bookingReference]);
        $booking = $statement->fetch();

        return is_array($booking) ? $booking : null;
    }

    public function findDetailedByReference(string $bookingReference): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                b.*,
                vs.slot_start,
                vs.slot_end,
                v.id AS venue_id,
                v.slug AS venue_slug,
                v.name AS venue_name_full
             FROM bookings b
             INNER JOIN venue_slots vs ON vs.id = b.venue_slot_id
             INNER JOIN venues v ON v.id = vs.venue_id
             WHERE b.booking_reference = :booking_reference
             LIMIT 1'
        );
        $statement->execute(['booking_reference' => $bookingReference]);
        $booking = $statement->fetch();

        return is_array($booking) ? $booking : null;
    }

    public function findByUserId(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM bookings WHERE user_id = :user_id ORDER BY created_at DESC'
        );
        $statement->execute(['user_id' => $userId]);

        return $statement->fetchAll() ?: [];
    }

    public function findManageableBookings(?int $ownerId = null): array
    {
        $sql = <<<SQL
SELECT
    b.*,
    u.full_name AS customer_name,
    u.email AS customer_email,
    v.id AS venue_id,
    v.slug AS venue_slug,
    v.owner_id,
    vs.slot_start,
    vs.slot_end
FROM bookings b
INNER JOIN users u ON u.id = b.user_id
INNER JOIN venue_slots vs ON vs.id = b.venue_slot_id
INNER JOIN venues v ON v.id = vs.venue_id
SQL;

        if ($ownerId !== null) {
            $statement = $this->pdo->prepare($sql . ' WHERE v.owner_id = :owner_id ORDER BY b.created_at DESC');
            $statement->execute(['owner_id' => $ownerId]);
        } else {
            $statement = $this->pdo->query($sql . ' ORDER BY b.created_at DESC');
        }

        return $statement->fetchAll() ?: [];
    }

    public function updateBookingStatus(string $bookingReference, string $status): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE bookings SET booking_status = :booking_status, reviewed_at = CURRENT_TIMESTAMP WHERE booking_reference = :booking_reference'
        );
        $statement->execute([
            'booking_status' => $status,
            'booking_reference' => $bookingReference,
        ]);

        $booking = $this->findByReference($bookingReference);
        if (!is_array($booking) || empty($booking['venue_slot_id'])) {
            return;
        }

        $slotStatement = $this->pdo->prepare(
            'UPDATE venue_slots
             SET status = :status,
                 hold_reference = :hold_reference,
                 hold_expires_at = :hold_expires_at
             WHERE id = :id'
        );
        $slotStatement->execute([
            'id' => $booking['venue_slot_id'],
            'status' => $status === 'REJECTED' ? 'AVAILABLE' : 'BOOKED',
            'hold_reference' => $status === 'REJECTED' ? null : ($booking['hold_reference'] ?? null),
            'hold_expires_at' => null,
        ]);
    }
}
