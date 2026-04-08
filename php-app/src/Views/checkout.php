<section class="glass-panel animate-morph p-8">
    <span class="badge-chip">Confirmation fee payment</span>
    <h1 class="mt-4 text-4xl font-semibold">Pay a small confirmation amount to secure this venue.</h1>
    <p class="mt-4 max-w-2xl text-plum/75">
        Your chosen date is on hold for this booking. Pay the confirmation fee through the UPI intent link or the QR code below. The remaining amount can be settled directly with the venue owner later.
    </p>

    <?php if (!empty($error)): ?>
        <div class="auth-message auth-message--error mt-6"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="auth-message mt-6"><?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="mt-8 grid gap-4 lg:grid-cols-2">
        <div class="metric-tile">
            <div class="metric-tile__label">Venue</div>
            <div class="metric-tile__value"><?= htmlspecialchars((string) $venue['name'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Booking reference</div>
            <div class="metric-tile__value"><?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Confirmation fee</div>
            <div class="metric-tile__value">INR <?= htmlspecialchars(number_format((float) $confirmationFee, 2), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Pay later to owner</div>
            <div class="metric-tile__value">INR <?= htmlspecialchars(number_format((float) $remainingAmount, 2), ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

    <?php if (!empty($bookingPreferences)): ?>
        <div class="planner-summary mt-8">
            <div class="planner-summary__title">Booking details</div>
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

    <div class="mt-8 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
        <article class="glass-panel p-6">
            <p class="text-sm uppercase tracking-[0.24em] text-plum/50">Manual QR payment</p>
            <img
                class="mt-4 w-full max-w-[260px] rounded-[24px] border border-plum/10 bg-white p-4 shadow-glow"
                src="<?= htmlspecialchars((string) $qrImageUrl, ENT_QUOTES, 'UTF-8') ?>"
                alt="UPI QR code for booking confirmation fee">
            <p class="mt-4 text-sm text-plum/70">
                Scan this QR in any UPI app to pay the confirmation fee for booking reference
                <strong><?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?></strong>.
            </p>
        </article>

        <article class="glass-panel p-6">
            <p class="text-sm uppercase tracking-[0.24em] text-plum/50">UPI intent</p>
            <p class="mt-3 text-plum/75">
                Payee: <strong><?= htmlspecialchars((string) $upiName, ENT_QUOTES, 'UTF-8') ?></strong><br>
                UPI ID: <strong><?= htmlspecialchars((string) $upiId, ENT_QUOTES, 'UTF-8') ?></strong>
            </p>
            <a class="hero-link mt-6" href="<?= htmlspecialchars((string) $upiIntentUrl, ENT_QUOTES, 'UTF-8') ?>">
                Open UPI app
            </a>

            <form class="planner-form mt-8" method="post" action="<?= htmlspecialchars($url('bookings/payment/manual/callback'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="booking_reference" value="<?= htmlspecialchars((string) $booking['booking_reference'], ENT_QUOTES, 'UTF-8') ?>">
                <label class="planner-field planner-field--wide">
                    <span>Payment reference / UTR number</span>
                    <input type="text" name="payment_reference" placeholder="Enter the UPI transaction reference" required minlength="8" pattern="[A-Za-z0-9._\\-/]{8,64}">
                </label>
                <div class="planner-field planner-field--wide">
                    <span>What happens next</span>
                    <div class="planner-summary">
                        <div class="planner-summary__grid">
                            <div class="info-chip">
                                <span class="text-plum/55">Now</span>
                                <strong>Pay only the confirmation fee</strong>
                            </div>
                            <div class="info-chip">
                                <span class="text-plum/55">After submission</span>
                                <strong>Booking stays on hold until verification</strong>
                            </div>
                            <div class="info-chip">
                                <span class="text-plum/55">After owner/admin check</span>
                                <strong>Date stays unavailable until the event is over</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="hero-link planner-submit">Submit payment reference</button>
            </form>
        </article>
    </div>
</section>
