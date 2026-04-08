<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\BookingRepository;
use App\Repositories\UserRepository;
use App\Repositories\VenueRepository;
use App\Services\VenueCatalogService;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $currentUser = $this->requireAuth();
        $role = strtoupper((string) ($currentUser['role'] ?? 'CUSTOMER'));
        $ownerId = $role === 'OWNER' ? (int) ($currentUser['id'] ?? 0) : null;
        $venueCatalog = new VenueCatalogService();
        $venueRepository = new VenueRepository();
        $bookingRepository = new BookingRepository();
        $userRepository = new UserRepository();

        $dashboard = match ($role) {
            'ADMIN' => [
                'title' => 'Admin Dashboard | VITREON',
                'eyebrow' => 'Platform command center',
                'heading' => 'Oversee users, venues, and payment health',
                'description' => 'Admins get a high-level view of marketplace growth, verification queues, and payment event monitoring.',
                'tabs' => ['Overview', 'Users', 'Venue Ops', 'Bookings'],
                'metrics' => [
                    ['label' => 'Registered Customers', 'value' => (string) (new UserRepository())->countByRole('CUSTOMER')],
                    ['label' => 'Active Owners', 'value' => (string) (new UserRepository())->countByRole('OWNER')],
                    ['label' => 'Upcoming Bookings', 'value' => '0'],
                    ['label' => 'Past Bookings', 'value' => '0'],
                ],
                'panels' => [
                    ['eyebrow' => 'Moderation queue', 'title' => 'Venue profile approvals', 'body' => 'Review new owner-submitted venues, category selections, and image quality before they go live.'],
                    ['eyebrow' => 'Platform health', 'title' => 'Payments and OTP sign-ins', 'body' => 'Track deposit confirmations, failed capture callbacks, and login OTP completion across the marketplace.'],
                ],
                'manageableVenues' => $venueCatalog->allManageable(),
                'manageableSlots' => $venueRepository->slotsForManageableVenues(),
                'manageableBookings' => $bookingRepository->findManageableBookings(),
                'customerUsers' => $userRepository->allByRole('CUSTOMER'),
                'ownerUsers' => $userRepository->allByRole('OWNER'),
            ],
            'OWNER' => [
                'title' => 'Owner Dashboard | VITREON',
                'eyebrow' => 'Owner operations',
                'heading' => 'Manage listings, deposits, and guest demand',
                'description' => 'Owners can monitor venue performance, conversion-ready leads, and review follow-up priorities from one place.',
                'tabs' => ['My Venues', 'Upcoming', 'Past', 'Availability'],
                'metrics' => [
                    ['label' => 'Total Bookings', 'value' => '0'],
                    ['label' => 'Upcoming Bookings', 'value' => '0'],
                    ['label' => 'Past Bookings', 'value' => '0'],
                    ['label' => 'Pending Reviews', 'value' => '0'],
                ],
                'panels' => [
                    ['eyebrow' => 'Upcoming calendar', 'title' => 'Track confirmed and pending venue dates', 'body' => 'Owners can review every upcoming reservation, including who booked, the event date, and whether the booking is still waiting for approval.'],
                    ['eyebrow' => 'Past history', 'title' => 'Review completed booking activity', 'body' => 'Past bookings remain visible so owners can audit venue usage, completed events, and earlier approval decisions.'],
                ],
                'manageableVenues' => $venueCatalog->allManageable($ownerId),
                'manageableSlots' => $venueRepository->slotsForManageableVenues($ownerId),
                'manageableBookings' => $bookingRepository->findManageableBookings($ownerId),
                'customerUsers' => [],
                'ownerUsers' => [],
            ],
            default => [
                'title' => 'My Dashboard | VITREON',
                'eyebrow' => 'Customer workspace',
                'heading' => 'Discover venues, track bookings, and revisit plans',
                'description' => 'Customers get quick access to bookings, saved planning details, and upcoming venue decisions after OTP login.',
                'tabs' => ['Discover', 'My Bookings', 'Planning', 'Profile'],
                'metrics' => [
                    ['label' => 'Upcoming Booking Holds', 'value' => '2'],
                    ['label' => 'Saved Venue Shortlist', 'value' => '7'],
                    ['label' => 'Recent Deposits', 'value' => '1'],
                    ['label' => 'Preferred City', 'value' => 'Pune'],
                ],
                'panels' => [
                    ['eyebrow' => 'Planning snapshot', 'title' => 'Keep event details in one place', 'body' => 'Event date, guest count, and venue notes now travel from venue detail into checkout and confirmation views.'],
                    ['eyebrow' => 'Next step', 'title' => 'Continue from bookings', 'body' => 'Open your bookings page to review deposit status and the latest booking references tied to your account.'],
                ],
                'manageableVenues' => [],
                'manageableSlots' => [],
                'manageableBookings' => [],
                'customerUsers' => [],
                'ownerUsers' => [],
            ],
        };

        if (in_array($role, ['OWNER', 'ADMIN'], true)) {
            $manageableBookings = $dashboard['manageableBookings'];
            $upcomingBookings = array_values(array_filter($manageableBookings, static fn (array $booking): bool => strtotime((string) ($booking['slot_start'] ?? '')) >= strtotime('today')));
            $pastBookings = array_values(array_filter($manageableBookings, static fn (array $booking): bool => strtotime((string) ($booking['slot_end'] ?? '')) < strtotime('today')));
            $pendingBookings = array_values(array_filter($manageableBookings, static fn (array $booking): bool => strtoupper((string) ($booking['booking_status'] ?? '')) === 'PENDING_REVIEW'));

            if ($role === 'OWNER') {
                $dashboard['metrics'] = [
                    ['label' => 'Total Bookings', 'value' => (string) count($manageableBookings)],
                    ['label' => 'Upcoming Bookings', 'value' => (string) count($upcomingBookings)],
                    ['label' => 'Past Bookings', 'value' => (string) count($pastBookings)],
                    ['label' => 'Pending Reviews', 'value' => (string) count($pendingBookings)],
                ];
            }

            if ($role === 'ADMIN') {
                $dashboard['metrics'][2]['value'] = (string) count($upcomingBookings);
                $dashboard['metrics'][3]['value'] = (string) count($pastBookings);
            }

            $dashboard['upcomingBookings'] = $upcomingBookings;
            $dashboard['pastBookings'] = $pastBookings;
            $dashboard['pendingBookings'] = $pendingBookings;
        }

        $this->render('dashboard', [
            ...$dashboard,
            'role' => $role,
        ]);
    }

    public function updateVenue(): void
    {
        $currentUser = $this->requireAuth();
        $role = strtoupper((string) ($currentUser['role'] ?? 'CUSTOMER'));
        if (!in_array($role, ['OWNER', 'ADMIN'], true)) {
            $this->redirect('/dashboard');
        }

        $venueId = (int) ($_POST['venue_id'] ?? 0);
        if ($venueId <= 0) {
            $this->redirect('/dashboard');
        }

        $venue = (new VenueCatalogService())->findById($venueId);
        if ($venue === null || ($role === 'OWNER' && (int) ($venue['ownerId'] ?? 0) !== (int) ($currentUser['id'] ?? 0))) {
            $this->redirect('/dashboard');
        }

        (new VenueRepository())->updateVenue($venueId, [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'neighborhood' => trim((string) ($_POST['neighborhood'] ?? '')),
            'event_category' => trim((string) ($_POST['event_category'] ?? '')),
            'base_price' => (float) ($_POST['base_price'] ?? 0),
            'capacity_range' => trim((string) ($_POST['capacity_range'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
        ]);

        $this->redirect('/dashboard');
    }

    public function updateSlot(): void
    {
        $currentUser = $this->requireAuth();
        $role = strtoupper((string) ($currentUser['role'] ?? 'CUSTOMER'));
        if (!in_array($role, ['OWNER', 'ADMIN'], true)) {
            $this->redirect('/dashboard');
        }

        $slotId = (int) ($_POST['slot_id'] ?? 0);
        if ($slotId <= 0) {
            $this->redirect('/dashboard');
        }

        $slots = (new VenueRepository())->slotsForManageableVenues($role === 'OWNER' ? (int) ($currentUser['id'] ?? 0) : null);
        $match = null;
        foreach ($slots as $slot) {
            if ((int) ($slot['id'] ?? 0) === $slotId) {
                $match = $slot;
                break;
            }
        }

        if ($match === null && $role !== 'ADMIN') {
            $this->redirect('/dashboard');
        }

        (new VenueRepository())->updateSlot($slotId, [
            'slot_start' => trim((string) ($_POST['slot_start'] ?? '')),
            'slot_end' => trim((string) ($_POST['slot_end'] ?? '')),
            'status' => trim((string) ($_POST['status'] ?? 'AVAILABLE')),
        ]);

        $this->redirect('/dashboard');
    }

    public function reviewBooking(): void
    {
        $currentUser = $this->requireAuth();
        $role = strtoupper((string) ($currentUser['role'] ?? 'CUSTOMER'));
        if (!in_array($role, ['OWNER', 'ADMIN'], true)) {
            $this->redirect('/dashboard');
        }

        $bookingReference = trim((string) ($_POST['booking_reference'] ?? ''));
        $decision = strtoupper(trim((string) ($_POST['decision'] ?? '')));
        if ($bookingReference === '' || !in_array($decision, ['APPROVED', 'REJECTED'], true)) {
            $this->redirect('/dashboard');
        }

        $bookings = (new BookingRepository())->findManageableBookings($role === 'OWNER' ? (int) ($currentUser['id'] ?? 0) : null);
        $allowed = false;
        foreach ($bookings as $booking) {
            if (($booking['booking_reference'] ?? '') === $bookingReference) {
                $allowed = true;
                break;
            }
        }

        if ($allowed) {
            (new BookingRepository())->updateBookingStatus($bookingReference, $decision);
        }

        $this->redirect('/dashboard');
    }

    public function changeUserRole(): void
    {
        $currentUser = $this->requireAuth();
        if (strtoupper((string) ($currentUser['role'] ?? 'CUSTOMER')) !== 'ADMIN') {
            $this->redirect('/dashboard');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $role = strtoupper(trim((string) ($_POST['role'] ?? 'CUSTOMER')));
        if ($userId <= 0 || !in_array($role, ['CUSTOMER', 'OWNER', 'ADMIN'], true)) {
            $this->redirect('/dashboard');
        }

        if ($userId === (int) ($currentUser['id'] ?? 0) && $role !== 'ADMIN') {
            $this->redirect('/dashboard');
        }

        (new UserRepository())->updateRole($userId, $role);
        $this->redirect('/dashboard');
    }

    public function toggleUserStatus(): void
    {
        $currentUser = $this->requireAuth();
        if (strtoupper((string) ($currentUser['role'] ?? 'CUSTOMER')) !== 'ADMIN') {
            $this->redirect('/dashboard');
        }

        $userId = (int) ($_POST['user_id'] ?? 0);
        $isActive = (int) ($_POST['is_active'] ?? 1) === 1;
        if ($userId <= 0 || $userId === (int) ($currentUser['id'] ?? 0)) {
            $this->redirect('/dashboard');
        }

        (new UserRepository())->updateActiveStatus($userId, $isActive);
        $this->redirect('/dashboard');
    }
}
