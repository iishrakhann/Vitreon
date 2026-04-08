<section class="glass-panel animate-morph p-8">
    <span class="badge-chip">Booking confirmed</span>
    <h1 class="mt-4 text-4xl font-semibold">Your event date is now secured.</h1>
    <p class="mt-4 max-w-2xl text-plum/75">
        VITREON has recorded your confirmation payment and marked this venue date unavailable. The remaining amount can be paid directly to the venue owner using the arrangement you both prefer.
    </p>
    <?php if (!empty($booking)): ?>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="metric-tile">
                <div class="metric-tile__label">Booking reference</div>
                <div class="metric-tile__value"><?= htmlspecialchars((string) ($booking['booking_reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="metric-tile">
                <div class="metric-tile__label">Payment status</div>
                <div class="metric-tile__value"><?= htmlspecialchars((string) ($booking['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <div class="metric-tile">
                <div class="metric-tile__label">Payment reference</div>
                <div class="metric-tile__value"><?= htmlspecialchars((string) ($booking['razorpay_payment_id'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        </div>
    <?php endif; ?>
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
            </div>
        </div>
    <?php endif; ?>
    <div class="mt-8 grid gap-4 md:grid-cols-3">
        <div class="metric-tile">
            <div class="metric-tile__label">Payment provider</div>
            <div class="metric-tile__value">Manual UPI / QR</div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Booking hold</div>
            <div class="metric-tile__value">Until event completes</div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Balance payment</div>
            <div class="metric-tile__value">Paid directly to owner</div>
        </div>
    </div>
</section>
