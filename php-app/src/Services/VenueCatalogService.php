<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\VenueRepository;

final class VenueCatalogService
{
    private VenueRepository $venueRepository;

    public function __construct()
    {
        $this->venueRepository = new VenueRepository();
    }

    public function all(): array
    {
        return array_map(fn (array $venue): array => $this->hydrate($venue), $this->venueRepository->allFeatured());
    }

    public function allManageable(?int $ownerId = null): array
    {
        return array_map(fn (array $venue): array => $this->hydrate($venue), $this->venueRepository->allManageable($ownerId));
    }

    public function findBySlug(string $slug): ?array
    {
        $venue = $this->venueRepository->findBySlug($slug);
        if ($venue === null) {
            return null;
        }

        $hydrated = $this->hydrate($venue);
        $hydrated['availableSlots'] = $this->hydrateSlots(
            $this->venueRepository->availableSlotsForVenue((int) ($venue['venue_id'] ?? 0))
        );
        $hydrated['reviews'] = $this->hydrateReviews(
            $this->venueRepository->recentReviewsForVenue((int) ($venue['venue_id'] ?? 0), 6)
        );
        $hydrated['userPosts'] = $this->buildUserPosts($hydrated['name']);

        return $hydrated;
    }

    public function findById(int $id): ?array
    {
        foreach ($this->allManageable() as $venue) {
            if ((int) ($venue['venueId'] ?? 0) === $id) {
                return $venue;
            }
        }

        return null;
    }

    public function topRatedByEventType(string $eventType): array
    {
        return array_map(fn (array $venue): array => $this->hydrate($venue), $this->venueRepository->topRatedByEventType($eventType));
    }

    private function hydrate(array $venue): array
    {
        $basePrice = (float) ($venue['base_price'] ?? 0);
        $gallery = array_values(array_filter(array_map(
            fn (string $path): string => $this->normalizeImagePath($path),
            explode('||', (string) ($venue['image_gallery'] ?? ''))
        )));
        $cardImage = $gallery[0] ?? $this->normalizeImagePath((string) ($venue['cover_image'] ?? 'assets/venue-default.svg'));

        return [
            'venueId' => (int) ($venue['venue_id'] ?? 0),
            'ownerId' => (int) ($venue['owner_id'] ?? 0),
            'slotId' => isset($venue['slot_id']) ? (int) $venue['slot_id'] : null,
            'slug' => (string) ($venue['slug'] ?? ''),
            'name' => (string) ($venue['venue_name'] ?? ''),
            'neighborhood' => (string) ($venue['neighborhood'] ?? ''),
            'price' => 'INR ' . number_format($basePrice / 100000, 1) . 'L',
            'totalAmount' => $basePrice,
            'eventType' => (string) ($venue['event_category'] ?? ''),
            'rating' => round((float) ($venue['rating'] ?? 0), 1),
            'aiSentiment' => round((float) ($venue['ai_sentiment'] ?? 0.5), 2),
            'capacity' => (string) ($venue['capacity_range'] ?? 'Capacity on request'),
            'holdWindow' => '10 min',
            'ownerPhone' => (string) ($venue['owner_phone'] ?? ''),
            'ownerName' => (string) ($venue['owner_name'] ?? ''),
            'ownerEmail' => (string) ($venue['owner_email'] ?? ''),
            'description' => (string) ($venue['description'] ?? ''),
            'cardImage' => $cardImage,
            'galleryImages' => $gallery,
        ];
    }

    private function hydrateSlots(array $slots): array
    {
        return array_map(function (array $slot): array {
            $start = (string) ($slot['slot_start'] ?? '');
            $end = (string) ($slot['slot_end'] ?? '');
            $timestamp = strtotime($start) ?: time();

            return [
                'slotId' => (int) ($slot['id'] ?? 0),
                'date' => date('Y-m-d', $timestamp),
                'label' => date('d M Y', $timestamp) . ' | ' . date('h:i A', $timestamp) . ' - ' . date('h:i A', strtotime($end) ?: $timestamp),
                'startsAt' => $start,
                'endsAt' => $end,
            ];
        }, $slots);
    }

    private function hydrateReviews(array $reviews): array
    {
        return array_map(static function (array $review): array {
            $timestamp = strtotime((string) ($review['created_at'] ?? '')) ?: time();

            return [
                'reviewer' => (string) ($review['reviewer_name'] ?? 'Guest reviewer'),
                'rating' => (int) ($review['rating'] ?? 0),
                'text' => (string) ($review['review_text'] ?? ''),
                'date' => date('d M Y', $timestamp),
            ];
        }, $reviews);
    }

    private function buildUserPosts(string $venueName): array
    {
        return [
            [
                'title' => 'Guest photo drop',
                'description' => 'Reserved for customer snapshots and celebration highlights from ' . $venueName . '.',
            ],
            [
                'title' => 'Event story post',
                'description' => 'Reserved for a short user-written recap or carousel from a hosted event.',
            ],
            [
                'title' => 'Ambience upload',
                'description' => 'Reserved for guest-taken decor, stage, crowd, or ambience moments.',
            ],
        ];
    }

    private function normalizeImagePath(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $trimmed) === 1) {
            return $trimmed;
        }

        $basePath = rtrim((string) Config::get('services.app.base_path', ''), '/');
        return ($basePath !== '' ? $basePath : '') . '/' . ltrim($trimmed, '/');
    }
}
