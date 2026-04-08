<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
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
        $venue = (new VenueCatalogService())->findBySlug((string) ($params['slug'] ?? ''));

        if ($venue === null) {
            http_response_code(404);
            echo 'Venue not found.';
            return;
        }

        $this->render('venue-detail', [
            'title' => $venue['name'] . ' | VITREON',
            'venue' => $venue,
        ]);
    }
}
