<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

final class VenueRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    public function allFeatured(): array
    {
        $statement = $this->pdo->query($this->baseQuery() . $this->groupBySql() . ' ORDER BY ai_sentiment DESC, venue_name ASC');
        return $statement->fetchAll() ?: [];
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare($this->baseQuery() . ' WHERE v.slug = :slug' . $this->groupBySql() . ' LIMIT 1');
        $statement->execute(['slug' => $slug]);
        $venue = $statement->fetch();

        return is_array($venue) ? $venue : null;
    }

    public function topRatedByEventType(string $eventType): array
    {
        $statement = $this->pdo->prepare($this->baseQuery() . ' WHERE LOWER(v.event_category) = :event_type' . $this->groupBySql() . ' ORDER BY ai_sentiment DESC, rating DESC');
        $statement->execute(['event_type' => strtolower($eventType)]);
        return $statement->fetchAll() ?: [];
    }

    public function allManageable(?int $ownerId = null): array
    {
        if ($ownerId !== null) {
            $statement = $this->pdo->prepare($this->baseQuery() . ' WHERE v.owner_id = :owner_id' . $this->groupBySql() . ' ORDER BY venue_name ASC');
            $statement->execute(['owner_id' => $ownerId]);
            return $statement->fetchAll() ?: [];
        }

        $statement = $this->pdo->query($this->baseQuery() . $this->groupBySql() . ' ORDER BY venue_name ASC');
        return $statement->fetchAll() ?: [];
    }

    public function updateVenue(int $venueId, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE venues
             SET name = :name,
                 neighborhood = :neighborhood,
                 event_category = :event_category,
                 base_price = :base_price,
                 capacity_range = :capacity_range,
                 description = :description
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $venueId,
            'name' => $data['name'],
            'neighborhood' => $data['neighborhood'],
            'event_category' => $data['event_category'],
            'base_price' => $data['base_price'],
            'capacity_range' => $data['capacity_range'],
            'description' => $data['description'],
        ]);
    }

    public function slotsForManageableVenues(?int $ownerId = null): array
    {
        $sql = <<<SQL
SELECT
    vs.*,
    v.name AS venue_name,
    v.slug AS venue_slug,
    v.owner_id
FROM venue_slots vs
INNER JOIN venues v ON v.id = vs.venue_id
SQL;

        if ($ownerId !== null) {
            $statement = $this->pdo->prepare($sql . ' WHERE v.owner_id = :owner_id ORDER BY vs.slot_start ASC');
            $statement->execute(['owner_id' => $ownerId]);
        } else {
            $statement = $this->pdo->query($sql . ' ORDER BY vs.slot_start ASC');
        }

        return $statement->fetchAll() ?: [];
    }

    public function availableSlotsForVenue(int $venueId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM venue_slots
             WHERE venue_id = :venue_id
               AND slot_start >= CURRENT_DATE()
               AND (
                    status = \'AVAILABLE\'
                    OR (status = \'HELD\' AND (hold_expires_at IS NULL OR hold_expires_at < NOW()))
               )
             ORDER BY slot_start ASC'
        );
        $statement->execute(['venue_id' => $venueId]);

        return $statement->fetchAll() ?: [];
    }

    public function findBookableSlot(int $venueId, int $slotId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM venue_slots
             WHERE id = :id
               AND venue_id = :venue_id
               AND slot_start >= CURRENT_DATE()
               AND (
                    status = \'AVAILABLE\'
                    OR (status = \'HELD\' AND (hold_expires_at IS NULL OR hold_expires_at < NOW()))
               )
             LIMIT 1'
        );
        $statement->execute([
            'id' => $slotId,
            'venue_id' => $venueId,
        ]);

        $slot = $statement->fetch();
        return is_array($slot) ? $slot : null;
    }

    public function findBookableSlotByStart(int $venueId, string $slotStart): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM venue_slots
             WHERE venue_id = :venue_id
               AND slot_start = :slot_start
               AND slot_start >= CURRENT_DATE()
               AND (
                    status = \'AVAILABLE\'
                    OR (status = \'HELD\' AND (hold_expires_at IS NULL OR hold_expires_at < NOW()))
               )
             LIMIT 1'
        );
        $statement->execute([
            'venue_id' => $venueId,
            'slot_start' => $slotStart,
        ]);

        $slot = $statement->fetch();
        return is_array($slot) ? $slot : null;
    }

    public function resolveRequestedSlot(int $venueId, string $slotStart): ?array
    {
        $timestamp = strtotime($slotStart);
        if ($timestamp === false || $timestamp < time()) {
            return null;
        }

        $normalizedStart = date('Y-m-d H:i:s', $timestamp);
        $normalizedEnd = date('Y-m-d H:i:s', strtotime('+10 hours', $timestamp) ?: $timestamp);

        try {
            $this->pdo->beginTransaction();

            $existing = $this->findBookableSlotByStart($venueId, $normalizedStart);
            if (is_array($existing)) {
                $this->pdo->commit();
                return $existing;
            }

            $blockingStatement = $this->pdo->prepare(
                'SELECT *
                 FROM venue_slots
                 WHERE venue_id = :venue_id
                   AND DATE(slot_start) = DATE(:slot_start)
                   AND (
                        status = \'BOOKED\'
                        OR (status = \'HELD\' AND (hold_expires_at IS NULL OR hold_expires_at >= NOW()))
                   )
                 LIMIT 1'
            );
            $blockingStatement->execute([
                'venue_id' => $venueId,
                'slot_start' => $normalizedStart,
            ]);

            $blockingSlot = $blockingStatement->fetch();
            if (is_array($blockingSlot)) {
                $this->pdo->commit();
                return null;
            }

            $insertStatement = $this->pdo->prepare(
                'INSERT INTO venue_slots (venue_id, slot_start, slot_end, status)
                 VALUES (:venue_id, :slot_start, :slot_end, \'AVAILABLE\')'
            );
            $insertStatement->execute([
                'venue_id' => $venueId,
                'slot_start' => $normalizedStart,
                'slot_end' => $normalizedEnd,
            ]);

            $newSlotId = (int) $this->pdo->lastInsertId();
            $slotStatement = $this->pdo->prepare('SELECT * FROM venue_slots WHERE id = :id LIMIT 1');
            $slotStatement->execute(['id' => $newSlotId]);
            $slot = $slotStatement->fetch();

            $this->pdo->commit();
            return is_array($slot) ? $slot : null;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return null;
        }
    }

    public function markSlotBooked(int $slotId, ?string $holdReference = null): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE venue_slots
             SET status = \'BOOKED\',
                 hold_reference = :hold_reference,
                 hold_expires_at = NULL
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $slotId,
            'hold_reference' => $holdReference,
        ]);
    }

    public function placeLocalHold(int $slotId, string $holdReference): bool
    {
        $statement = $this->pdo->prepare(
            'UPDATE venue_slots
             SET status = \'HELD\',
                 hold_reference = :hold_reference,
                 hold_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE)
             WHERE id = :id
               AND (
                    status = \'AVAILABLE\'
                    OR (status = \'HELD\' AND (hold_expires_at IS NULL OR hold_expires_at < NOW()))
               )'
        );
        $statement->execute([
            'id' => $slotId,
            'hold_reference' => $holdReference,
        ]);

        return $statement->rowCount() > 0;
    }

    public function extendHoldForManualReview(int $slotId, string $holdReference, int $hours = 12): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE venue_slots
             SET status = \'HELD\',
                 hold_reference = :hold_reference,
                 hold_expires_at = DATE_ADD(NOW(), INTERVAL :hours HOUR)
             WHERE id = :id'
        );
        $statement->bindValue(':id', $slotId, PDO::PARAM_INT);
        $statement->bindValue(':hold_reference', $holdReference, PDO::PARAM_STR);
        $statement->bindValue(':hours', $hours, PDO::PARAM_INT);
        $statement->execute();
    }

    public function releaseSlot(int $slotId): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE venue_slots
             SET status = \'AVAILABLE\',
                 hold_reference = NULL,
                 hold_expires_at = NULL
             WHERE id = :id'
        );
        $statement->execute(['id' => $slotId]);
    }

    public function updateSlot(int $slotId, array $data): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE venue_slots
             SET slot_start = :slot_start,
                 slot_end = :slot_end,
                 status = :status
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $slotId,
            'slot_start' => $data['slot_start'],
            'slot_end' => $data['slot_end'],
            'status' => $data['status'],
        ]);
    }

    public function recentReviewsForVenue(int $venueId, int $limit = 3): array
    {
        $statement = $this->pdo->prepare(
            'SELECT
                r.rating,
                r.review_text,
                r.created_at,
                u.full_name AS reviewer_name
             FROM reviews r
             INNER JOIN users u ON u.id = r.user_id
             WHERE r.venue_id = :venue_id
             ORDER BY r.created_at DESC, r.id DESC
             LIMIT :limit'
        );
        $statement->bindValue(':venue_id', $venueId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll() ?: [];
    }

    public function createReview(int $venueId, int $userId, int $rating, string $reviewText): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO reviews (venue_id, user_id, rating, review_text, ai_sentiment_score)
             VALUES (:venue_id, :user_id, :rating, :review_text, :ai_sentiment_score)'
        );
        $statement->execute([
            'venue_id' => $venueId,
            'user_id' => $userId,
            'rating' => $rating,
            'review_text' => $reviewText,
            'ai_sentiment_score' => match (true) {
                $rating >= 5 => 0.95,
                $rating === 4 => 0.82,
                $rating === 3 => 0.62,
                $rating === 2 => 0.38,
                default => 0.2,
            },
        ]);
    }

    private function baseQuery(): string
    {
        return <<<SQL
SELECT
    v.id AS venue_id,
    v.owner_id,
    next_slot.id AS slot_id,
    v.slug,
    v.name AS venue_name,
    v.neighborhood,
    v.event_category,
    v.base_price,
    v.capacity_range,
    v.description,
    owner.phone_number AS owner_phone,
    owner.full_name AS owner_name,
    owner.email AS owner_email,
    (
        SELECT vi.image_path
        FROM venue_images vi
        WHERE vi.venue_id = v.id
        ORDER BY vi.sort_order ASC
        LIMIT 1
    ) AS cover_image,
    (
        SELECT GROUP_CONCAT(vi.image_path ORDER BY vi.sort_order ASC SEPARATOR '||')
        FROM venue_images vi
        WHERE vi.venue_id = v.id
    ) AS image_gallery,
    COALESCE(AVG(r.rating), 0) AS rating,
    COALESCE(AVG(r.ai_sentiment_score), 0.5) AS ai_sentiment
FROM venues v
INNER JOIN users owner ON owner.id = v.owner_id
LEFT JOIN reviews r ON r.venue_id = v.id
LEFT JOIN venue_slots next_slot ON next_slot.id = (
    SELECT vs.id
    FROM venue_slots vs
    WHERE vs.venue_id = v.id
      AND vs.slot_start >= CURRENT_DATE()
      AND (
        vs.status = 'AVAILABLE'
        OR (vs.status = 'HELD' AND (vs.hold_expires_at IS NULL OR vs.hold_expires_at < NOW()))
      )
    ORDER BY vs.slot_start ASC
    LIMIT 1
)
SQL;
    }

    private function groupBySql(): string
    {
        return <<<SQL
 GROUP BY
    v.id,
    v.owner_id,
    next_slot.id,
    v.slug,
    v.name,
    v.neighborhood,
    v.event_category,
    v.base_price,
    v.capacity_range,
    v.description,
    owner.phone_number,
    owner.full_name,
    owner.email
SQL;
    }
}
