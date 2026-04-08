<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Repositories\BookingRepository;
use App\Repositories\VenueRepository;
use App\Repositories\WebhookEventRepository;
use App\Services\RazorpayWebhookService;
use App\Services\TwilioService;
use App\Services\VenueCatalogService;
use App\Services\WebhookAuditService;

final class PaymentController extends Controller
{
    public function initiateDeposit(): void
    {
        $currentUser = $this->currentUser();
        if (!is_array($currentUser) || empty($currentUser['id'])) {
            $_SESSION['post_login_redirect'] = $_SERVER['HTTP_REFERER'] ?? '/';
            $this->redirect('/login');
        }

        $slug = (string) ($_POST['venue_slug'] ?? '');
        $venue = (new VenueCatalogService())->findBySlug($slug);

        if ($venue === null) {
            http_response_code(404);
            echo 'Venue not found.';
            return;
        }

        $selectedSlotStart = trim((string) ($_POST['event_datetime'] ?? ''));
        if ($selectedSlotStart === '') {
            $eventDate = trim((string) ($_POST['event_date'] ?? ''));
            $eventTime = trim((string) ($_POST['event_time'] ?? ''));
            if ($eventDate !== '' && $eventTime !== '') {
                $selectedSlotStart = $eventDate . 'T' . $eventTime;
            }
        }

        $normalizedSlotStart = $selectedSlotStart !== '' ? str_replace('T', ' ', $selectedSlotStart) . ':00' : '';
        $selectedSlot = $normalizedSlotStart !== ''
            ? (new VenueRepository())->resolveRequestedSlot((int) ($venue['venueId'] ?? 0), $normalizedSlotStart)
            : null;

        if ($selectedSlot === null) {
            http_response_code(409);
            $this->render('deposit-failed', [
                'title' => 'Date Unavailable | PuneEventHub',
                'message' => 'This venue is not available for the selected date and time. Please try another date.',
            ]);
            return;
        }

        $bookingPreferences = [
            'event_date' => date('Y-m-d', strtotime((string) $selectedSlot['slot_start'])),
            'guest_count' => trim((string) ($_POST['guest_count'] ?? '')),
            'occasion' => trim((string) ($_POST['occasion'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'slot_label' => date('d M Y | h:i A', strtotime((string) $selectedSlot['slot_start'])),
        ];

        $bookingReference = 'PEH-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $holdReference = 'HOLD-' . $bookingReference;
        $venueRepository = new VenueRepository();
        $localHoldPlaced = $venueRepository->placeLocalHold((int) $selectedSlot['id'], $holdReference);

        if (!$localHoldPlaced) {
            http_response_code(409);
            $this->render('deposit-failed', [
                'title' => 'Slot Unavailable | PuneEventHub',
                'message' => 'Unable to lock the slot right now. Please try another date and time.',
            ]);
            return;
        }

        $confirmationFee = min(
            (float) Config::get('services.payments.confirmation_fee', 5000),
            (float) $venue['totalAmount']
        );
        $remainingAmount = max(0, (float) $venue['totalAmount'] - $confirmationFee);
        $bookingRepository = new BookingRepository();
        $booking = $bookingRepository->createPending([
            'user_id' => $currentUser['id'],
            'venue_slot_id' => (int) $selectedSlot['id'],
            'booking_reference' => $bookingReference,
            'hold_reference' => $holdReference,
            'total_amount' => $venue['totalAmount'],
            'deposit_amount' => $confirmationFee,
            'venue_name' => $venue['name'],
            'owner_phone' => $venue['ownerPhone'],
        ]);

        $_SESSION['booking_preferences'][$bookingReference] = $bookingPreferences;
        $upiId = (string) Config::get('services.payments.upi_id', 'puneeventhub@upi');
        $upiName = (string) Config::get('services.payments.upi_name', 'PuneEventHub');
        $upiNote = sprintf('Booking %s for %s', $bookingReference, (string) $venue['name']);
        $upiIntentUrl = sprintf(
            'upi://pay?pa=%s&pn=%s&am=%s&cu=INR&tn=%s',
            rawurlencode($upiId),
            rawurlencode($upiName),
            rawurlencode(number_format($confirmationFee, 2, '.', '')),
            rawurlencode($upiNote)
        );
        $qrPayload = sprintf(
            'upi://pay?pa=%s&pn=%s&am=%s&cu=INR&tn=%s',
            $upiId,
            $upiName,
            number_format($confirmationFee, 2, '.', ''),
            $upiNote
        );
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . rawurlencode($qrPayload);

        $this->renderManualCheckout($venue, $booking, $bookingPreferences);
    }

    public function handleManualPaymentCallback(): void
    {
        $currentUser = $this->currentUser();
        if (!is_array($currentUser) || empty($currentUser['id'])) {
            $this->redirect('/login');
        }

        $bookingReference = trim((string) ($_POST['booking_reference'] ?? ''));
        $paymentReference = trim((string) ($_POST['payment_reference'] ?? ''));
        $bookingRepository = new BookingRepository();
        $booking = $bookingReference !== '' ? $bookingRepository->findDetailedByReference($bookingReference) : null;

        if (!is_array($booking) || (int) ($booking['user_id'] ?? 0) !== (int) ($currentUser['id'] ?? 0)) {
            http_response_code(404);
            $this->render('deposit-failed', [
                'title' => 'Payment Callback Failed | PuneEventHub',
                'message' => 'We could not match that booking reference to your account.',
            ]);
            return;
        }

        $venue = (new VenueCatalogService())->findBySlug((string) ($booking['venue_slug'] ?? ''));
        if ($venue === null) {
            http_response_code(404);
            $this->render('deposit-failed', [
                'title' => 'Payment Callback Failed | PuneEventHub',
                'message' => 'The related venue could not be found for this booking.',
            ]);
            return;
        }

        $bookingPreferences = $_SESSION['booking_preferences'][$bookingReference] ?? [];
        if ($paymentReference === '' || preg_match('/^[A-Za-z0-9._\\-\\/]{8,64}$/', $paymentReference) !== 1) {
            http_response_code(422);
            $this->renderManualCheckout(
                $venue,
                $booking,
                is_array($bookingPreferences) ? $bookingPreferences : [],
                [
                    'error' => 'Enter a valid UPI transaction reference or UTR number before continuing.',
                ]
            );
            return;
        }

        $bookingRepository->markManualPaymentSubmitted($bookingReference, $paymentReference);
        (new VenueRepository())->extendHoldForManualReview(
            (int) ($booking['venue_slot_id'] ?? 0),
            (string) ($booking['hold_reference'] ?? ''),
            12
        );
        $updatedBooking = $bookingRepository->findDetailedByReference($bookingReference);

        $this->renderManualCheckout(
            $venue,
            is_array($updatedBooking) ? $updatedBooking : $booking,
            is_array($bookingPreferences) ? $bookingPreferences : [],
            [
                'success' => 'Payment reference submitted. The booking stays on hold while the owner or admin verifies that the confirmation amount was received.',
            ]
        );
    }

    private function renderManualCheckout(array $venue, array $booking, array $bookingPreferences, array $messages = []): void
    {
        $confirmationFee = min(
            (float) Config::get('services.payments.confirmation_fee', 5000),
            (float) ($venue['totalAmount'] ?? 0)
        );
        $remainingAmount = max(0, (float) ($venue['totalAmount'] ?? 0) - $confirmationFee);
        $upiId = (string) Config::get('services.payments.upi_id', 'puneeventhub@upi');
        $upiName = (string) Config::get('services.payments.upi_name', 'PuneEventHub');
        $upiNote = sprintf('Booking %s for %s', (string) ($booking['booking_reference'] ?? ''), (string) ($venue['name'] ?? 'Venue'));
        $upiIntentUrl = sprintf(
            'upi://pay?pa=%s&pn=%s&am=%s&cu=INR&tn=%s',
            rawurlencode($upiId),
            rawurlencode($upiName),
            rawurlencode(number_format($confirmationFee, 2, '.', '')),
            rawurlencode($upiNote)
        );
        $qrPayload = sprintf(
            'upi://pay?pa=%s&pn=%s&am=%s&cu=INR&tn=%s',
            $upiId,
            $upiName,
            number_format($confirmationFee, 2, '.', ''),
            $upiNote
        );
        $qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=' . rawurlencode($qrPayload);

        $this->render('checkout', [
            'title' => 'Booking Confirmation Payment | PuneEventHub',
            'venue' => $venue,
            'booking' => $booking,
            'bookingPreferences' => $bookingPreferences,
            'confirmationFee' => $confirmationFee,
            'remainingAmount' => $remainingAmount,
            'upiId' => $upiId,
            'upiName' => $upiName,
            'upiIntentUrl' => $upiIntentUrl,
            'qrImageUrl' => $qrImageUrl,
            'error' => $messages['error'] ?? '',
            'success' => $messages['success'] ?? '',
        ]);
    }

    public function handleRazorpayWebhook(): void
    {
        $payload = file_get_contents('php://input') ?: '';
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        $razorpayWebhookService = new RazorpayWebhookService();

        if (!$razorpayWebhookService->verifySignature($payload, is_string($signature) ? $signature : '')) {
            $this->json(['status' => 'rejected', 'message' => 'Invalid Razorpay signature.'], 401);
            return;
        }

        $event = $razorpayWebhookService->parseEvent($payload);
        $eventName = (string) ($event['event'] ?? 'unknown');
        $paymentEntity = $event['payload']['payment']['entity'] ?? [];
        $notes = is_array($paymentEntity['notes'] ?? null) ? $paymentEntity['notes'] : [];
        $bookingReference = (string) ($notes['booking_reference'] ?? '');
        $orderId = (string) ($paymentEntity['order_id'] ?? '');
        $paymentId = (string) ($paymentEntity['id'] ?? '');

        (new WebhookAuditService())->record('razorpay', $event);
        (new WebhookEventRepository())->create([
            'provider' => 'razorpay',
            'event_name' => $eventName,
            'booking_reference' => $bookingReference !== '' ? $bookingReference : null,
            'razorpay_order_id' => $orderId !== '' ? $orderId : null,
            'razorpay_payment_id' => $paymentId !== '' ? $paymentId : null,
            'payload_json' => $payload,
        ]);

        $bookingRepository = new BookingRepository();
        $smsTriggered = false;

        if ($eventName === 'payment.captured' && $bookingReference !== '') {
            $bookingRepository->markDepositPaid($bookingReference, $paymentId);

            $ownerPhone = (string) ($notes['owner_phone'] ?? '');
            $venueName = (string) ($notes['venue_name'] ?? 'Venue');
            $amount = isset($paymentEntity['amount']) ? (float) $paymentEntity['amount'] / 100 : 0.0;

            $message = sprintf(
                'Deposit received for %s. Booking ref: %s. Amount: INR %.2f.',
                $venueName,
                $bookingReference,
                $amount
            );

            $smsTriggered = (new TwilioService())->sendDepositConfirmation($ownerPhone, $message);
        }

        if ($eventName === 'payment.failed' && $bookingReference !== '') {
            $bookingRepository->markFailed($bookingReference);
        }

        $this->json([
            'status' => 'accepted',
            'event' => $eventName,
            'booking_reference' => $bookingReference,
            'sms_triggered' => $smsTriggered,
        ]);
    }

    public function depositSuccess(): void
    {
        $bookingReference = (string) ($_GET['booking_reference'] ?? '');
        $simulate = (string) ($_GET['simulate'] ?? '') === '1';
        $bookingRepository = new BookingRepository();

        if ($simulate && $bookingReference !== '') {
            $bookingRepository->markDepositPaid($bookingReference, 'demo_payment_' . strtolower($bookingReference));
        }

        $booking = $bookingReference !== '' ? $bookingRepository->findByReference($bookingReference) : null;
        $bookingPreferences = $_SESSION['booking_preferences'][$bookingReference] ?? [];

        $this->render('deposit-success', [
            'title' => 'Deposit Confirmed | PuneEventHub',
            'booking' => $booking,
            'bookingPreferences' => is_array($bookingPreferences) ? $bookingPreferences : [],
        ]);
    }
}
