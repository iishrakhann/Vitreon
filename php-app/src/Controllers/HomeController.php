<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Repositories\VenueRepository;
use App\Services\VenueCatalogService;

final class HomeController extends Controller
{
    public function index(): void
    {
        $venues = (new VenueCatalogService())->all();
        $this->render('home', [
            'title' => 'VITREON | Venue Discovery',
            'venues' => array_slice($venues, 0, 6),
            'googleMapsEmbedUrl' => Config::get('services.app.google_maps_embed_url'),
        ]);
    }

    public function venues(): void
    {
        $this->render('venues', [
            'title' => 'Venues | VITREON',
            'venues' => (new VenueCatalogService())->all(),
        ]);
    }

    public function about(): void
    {
        $this->render('about', [
            'title' => 'About | VITREON',
        ]);
    }

    public function contact(): void
    {
        $this->render('contact', [
            'title' => 'Contact | VITREON',
        ]);
    }

    public function showVenue(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $venue = (new VenueCatalogService())->findBySlug((string) ($params['slug'] ?? ''));

        if ($venue === null) {
            http_response_code(404);
            echo 'Venue not found.';
            return;
        }

        $this->render('venue-detail', [
            'title' => $venue['name'] . ' | VITREON',
            'venue' => $venue,
            'reviewFlash' => $this->pullReviewFlash($slug),
        ]);
    }

    public function submitReview(array $params): void
    {
        $slug = (string) ($params['slug'] ?? '');
        $repository = new VenueRepository();
        $venue = $repository->findBySlug($slug);

        if ($venue === null) {
            http_response_code(404);
            echo 'Venue not found.';
            return;
        }

        $user = $this->currentUser();
        if ($user === null || empty($user['id'])) {
            $_SESSION['post_login_redirect'] = $this->path('venues/' . $slug) . '#guest-reviews';
            $this->redirect('/login');
        }

        $rating = max(1, min(5, (int) ($_POST['rating'] ?? 0)));
        $reviewText = trim((string) ($_POST['review_text'] ?? ''));

        if ($reviewText === '' || strlen($reviewText) < 10) {
            $this->setReviewFlash($slug, 'Please write at least 10 characters before submitting your review.', 'error');
            $this->redirect('/venues/' . $slug . '#guest-reviews');
        }

        $repository->createReview((int) ($venue['venue_id'] ?? 0), (int) $user['id'], $rating, $reviewText);
        $this->setReviewFlash($slug, 'Thank you. Your review has been added to this venue.', 'success');
        $this->redirect('/venues/' . $slug . '#guest-reviews');
    }

    private function setReviewFlash(string $slug, string $message, string $type): void
    {
        $_SESSION['venue_review_flash'][$slug] = [
            'message' => $message,
            'type' => $type,
        ];
    }

    private function pullReviewFlash(string $slug): ?array
    {
        $flash = $_SESSION['venue_review_flash'][$slug] ?? null;
        unset($_SESSION['venue_review_flash'][$slug]);

        return is_array($flash) ? $flash : null;
    }
}
