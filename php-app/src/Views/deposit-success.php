<section class="glass-panel animate-morph p-8 receipt-sheet">
    <div class="receipt-header">
        <div>
            <span class="badge-chip">Payment receipt</span>
            <h1 class="mt-4 text-4xl font-semibold">Initial payment received</h1>
            <p class="mt-4 max-w-3xl text-plum/75">
                Keep this receipt for your records. Our team will get in touch for further processing, or you may visit the venue with this receipt to continue the next step in person.
            </p>
        </div>
        <div class="receipt-brand">
            <div class="receipt-brand__label">VITREON</div>
            <div class="receipt-brand__sub">Venue booking receipt</div>
        </div>
    </div>

    <?php if (!empty($flashMessage)): ?>
        <div class="auth-message mt-6"><?= htmlspecialchars((string) $flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="receipt-card mt-8">
        <div class="receipt-card__row">
            <span>Receipt number</span>
            <strong><?= htmlspecialchars((string) ($receipt['payment_reference'] ?? $booking['booking_reference'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Booking reference</span>
            <strong><?= htmlspecialchars((string) ($receipt['booking_reference'] ?? $booking['booking_reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Venue</span>
            <strong><?= htmlspecialchars((string) ($booking['venue_name_full'] ?? $booking['venue_name'] ?? $receipt['venue_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Amount paid</span>
            <strong>INR <?= htmlspecialchars(number_format((float) ($receipt['amount'] ?? $booking['deposit_amount'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Payment reference</span>
            <strong><?= htmlspecialchars((string) ($receipt['payment_reference'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Payment time</span>
            <strong><?= htmlspecialchars((string) ($receipt['paid_at'] ?? date('Y-m-d H:i:s')), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Booking status</span>
            <strong><?= htmlspecialchars((string) ($booking['booking_status'] ?? 'PENDING_REVIEW'), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="receipt-card__row">
            <span>Payment status</span>
            <strong><?= htmlspecialchars((string) ($booking['payment_status'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <?php if (!empty($bookingPreferences)): ?>
        <div class="planner-summary mt-8">
            <div class="planner-summary__title">Event snapshot</div>
            <div class="planner-summary__grid">
                <div class="info-chip">
                    <span class="text-plum/55">Event date</span>
                    <strong><?= htmlspecialchars((string) ($bookingPreferences['event_date'] ?? 'Not shared'), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="info-chip">
                    <span class="text-plum/55">Guest count</span>
                    <strong><?= htmlspecialchars((string) ($bookingPreferences['guest_count'] ?? 'Not shared'), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="info-chip">
                    <span class="text-plum/55">Occasion</span>
                    <strong><?= htmlspecialchars((string) ($bookingPreferences['occasion'] ?? 'Not shared'), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="info-chip">
                    <span class="text-plum/55">Notes</span>
                    <strong><?= htmlspecialchars((string) ($bookingPreferences['notes'] ?? 'No special notes'), ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="receipt-note mt-8">
        <p class="text-sm uppercase tracking-[0.24em] text-plum/50">Next step</p>
        <p class="mt-3 text-plum/75">
            Our team will contact you for further processing, or you can visit the venue with this receipt and the initial payment record from our website.
        </p>
    </div>

    <div class="hero-actions mt-8 receipt-actions">
        <a class="hero-link" href="<?= htmlspecialchars($url('bookings'), ENT_QUOTES, 'UTF-8') ?>">View my bookings</a>
        <button type="button" class="hero-link hero-link--secondary" onclick="window.print()">Print receipt</button>
        <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>">Back to venues</a>
    </div>
</section>
