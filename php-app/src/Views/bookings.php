<section class="glass-panel animate-morph p-8">
    <span class="badge-chip">Account bookings</span>
    <h1 class="mt-4 text-4xl font-semibold">Track your venue holds and confirmed bookings</h1>
    <p class="mt-4 max-w-3xl text-plum/75">
        Review booking references, payment states, and venue names tied to your account. Owners can book venues too, and every successful hold appears here automatically after OTP sign-in.
    </p>
</section>

<?php if (empty($currentUser)): ?>
    <section class="mt-8 glass-panel p-8">
        <h2 class="text-2xl font-semibold">Sign in to view your bookings</h2>
        <p class="mt-3 text-plum/70">
            Your booking timeline is available after OTP sign-in. Once you log in, every deposit attempt appears here automatically.
        </p>
        <a class="hero-link mt-6" href="<?= htmlspecialchars($url('login'), ENT_QUOTES, 'UTF-8') ?>">Continue with OTP Login</a>
    </section>
<?php elseif (empty($bookings)): ?>
    <section class="mt-8 glass-panel p-8">
        <h2 class="text-2xl font-semibold">No bookings yet</h2>
        <p class="mt-3 text-plum/70">
            Start from discovery, open any venue, and use the booking planner to lock one of the future open date-time slots and create your first deposit record.
        </p>
        <a class="hero-link mt-6" href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>">Browse venues</a>
    </section>
<?php else: ?>
    <section class="mt-8 bookings-grid">
        <?php foreach ($bookings as $booking): ?>
            <article class="booking-card">
                <div class="venue-card__top">
                    <span class="badge-chip"><?= htmlspecialchars((string) ($booking['payment_status'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-sm text-plum/65"><?= htmlspecialchars((string) ($booking['created_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h2 class="mt-5 text-2xl font-semibold"><?= htmlspecialchars((string) ($booking['venue_name'] ?? 'Venue'), ENT_QUOTES, 'UTF-8') ?></h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="info-chip">
                        <span class="text-plum/55">Booking reference</span>
                        <strong><?= htmlspecialchars((string) ($booking['booking_reference'] ?? ''), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="info-chip">
                        <span class="text-plum/55">Deposit</span>
                        <strong>INR <?= htmlspecialchars(number_format((float) ($booking['deposit_amount'] ?? 0), 2), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="info-chip">
                        <span class="text-plum/55">Event date</span>
                        <strong><?= htmlspecialchars((string) (!empty($booking['slot_start']) ? date('d M Y | h:i A', strtotime((string) $booking['slot_start'])) : 'Awaiting slot'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="info-chip">
                        <span class="text-plum/55">Booking status</span>
                        <strong><?= htmlspecialchars((string) ($booking['booking_status'] ?? 'PENDING'), ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
