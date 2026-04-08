<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BookingRepository;

final class BookingsController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->currentUser();
        $bookings = [];

        if (is_array($currentUser) && !empty($currentUser['id'])) {
            $bookings = (new BookingRepository())->findByUserId((int) $currentUser['id']);
        }

        $this->render('bookings', [
            'title' => 'My Bookings | VITREON',
            'bookings' => $bookings,
        ]);
    }
}
