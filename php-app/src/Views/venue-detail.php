<section class="glass-panel animate-morph p-6">
    <span class="badge-chip"><?= htmlspecialchars($venue['neighborhood'], ENT_QUOTES, 'UTF-8') ?></span>
    <?php $galleryImages = array_values(array_slice($venue['galleryImages'] ?? [], 0, 8)); ?>
    <div class="venue-gallery mb-6" data-venue-gallery data-venue-name="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>" tabindex="0" aria-label="Venue image gallery">
        <div class="venue-gallery__spotlight">
            <?php foreach ($galleryImages as $index => $image): ?>
                <img
                    class="venue-gallery__image<?= $index === 0 ? ' is-active' : '' ?>"
                    data-venue-slide
                    data-slide-index="<?= (int) $index ?>"
                    src="<?= htmlspecialchars((string) $image, ENT_QUOTES, 'UTF-8') ?>"
                    alt="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>"
                    loading="lazy"
                    referrerpolicy="no-referrer"
                    onerror="this.onerror=null;this.src='<?= htmlspecialchars($url('assets/venue-default.svg'), ENT_QUOTES, 'UTF-8') ?>';">
            <?php endforeach; ?>
            <div class="venue-gallery__overlay">
                <span class="venue-gallery__count"><?= count($galleryImages) ?> images</span>
                <span class="venue-gallery__label">Spotlight view</span>
            </div>
        </div>

        <?php if (count($galleryImages) > 1): ?>
            <div class="venue-gallery__thumbs" aria-label="Venue gallery thumbnails">
                <?php foreach ($galleryImages as $index => $image): ?>
                    <button
                        type="button"
                        class="venue-gallery__thumb<?= $index === 0 ? ' is-active' : '' ?>"
                        data-venue-thumb
                        data-target-index="<?= (int) $index ?>"
                        aria-label="Show image <?= (int) ($index + 1) ?>">
                        <img
                            src="<?= htmlspecialchars((string) $image, ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>"
                            loading="lazy"
                            referrerpolicy="no-referrer"
                            onerror="this.onerror=null;this.src='<?= htmlspecialchars($url('assets/venue-default.svg'), ENT_QUOTES, 'UTF-8') ?>';">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-4xl font-semibold"><?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="mt-3 text-plum/70"><?= htmlspecialchars($venue['eventType'], ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($venue['capacity'], ENT_QUOTES, 'UTF-8') ?></p>
            <p class="mt-4 max-w-3xl text-plum/75"><?= htmlspecialchars((string) ($venue['description'] ?? 'Premium venue information is available on request.'), ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="info-chip">
            <span class="text-plum/55">Venue category</span>
            <strong><?= htmlspecialchars($venue['eventType'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>

    <div class="mt-6 grid gap-3 md:grid-cols-3">
        <div class="metric-tile">
            <div class="metric-tile__label">Starting price</div>
            <div class="metric-tile__value"><?= htmlspecialchars($venue['price'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Confirmation fee</div>
            <div class="metric-tile__value">Minimal upfront</div>
        </div>
        <div class="metric-tile">
            <div class="metric-tile__label">Availability check</div>
            <div class="metric-tile__value"><?= htmlspecialchars($venue['holdWindow'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>

    <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div class="info-chip">
            <span class="text-plum/55">Location</span>
            <strong><?= htmlspecialchars((string) $venue['neighborhood'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="info-chip">
            <span class="text-plum/55">Capacity</span>
            <strong><?= htmlspecialchars((string) $venue['capacity'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="info-chip">
            <span class="text-plum/55">Venue host</span>
            <strong><?= htmlspecialchars((string) $venue['ownerName'], ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
        <div class="info-chip">
            <span class="text-plum/55">Owner email</span>
            <strong><?= htmlspecialchars((string) ($venue['ownerEmail'] ?: 'Not shared'), ENT_QUOTES, 'UTF-8') ?></strong>
        </div>
    </div>
</section>

<section class="mt-6 grid items-stretch gap-4 lg:grid-cols-[1.2fr_0.8fr]">
    <div id="guest-reviews" class="glass-panel p-6 venue-reviews-panel">
        <p class="text-sm uppercase tracking-[0.25em] text-plum/50">Guest reviews</p>
        <h2 class="mt-2 text-3xl font-semibold">What recent guests are saying</h2>
        <div class="mt-6 grid gap-4">
            <?php foreach (($venue['reviews'] ?? []) as $review): ?>
                <article class="review-card">
                    <div class="review-card__top">
                        <div>
                            <strong><?= htmlspecialchars((string) $review['reviewer'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <p class="text-sm text-plum/65"><?= htmlspecialchars((string) $review['date'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <span class="review-stars" aria-label="<?= htmlspecialchars((string) ((int) $review['rating'] . ' out of 5 stars'), ENT_QUOTES, 'UTF-8') ?>">
                            <?php for ($star = 1; $star <= 5; $star++): ?>
                                <span class="<?= $star <= (int) $review['rating'] ? 'is-filled' : '' ?>">&#9733;</span>
                            <?php endfor; ?>
                        </span>
                    </div>
                    <p class="mt-4 text-plum/75"><?= htmlspecialchars((string) $review['text'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="review-submit-card mt-5">
            <p class="text-sm uppercase tracking-[0.22em] text-plum/50">Add your review</p>
            <h3 class="mt-2 text-2xl font-semibold">Share your experience</h3>
            <p class="mt-2 text-plum/70">
                Help future guests understand the ambience, service, accessibility, and event experience at this venue.
            </p>

            <?php if (!empty($reviewFlash)): ?>
                <div class="auth-message<?= ($reviewFlash['type'] ?? '') === 'error' ? ' auth-message--error' : '' ?> mt-4">
                    <?= htmlspecialchars((string) ($reviewFlash['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($currentUser)): ?>
                <form class="review-form mt-4" method="post" action="<?= htmlspecialchars($url('venues/' . (string) $venue['slug'] . '/reviews'), ENT_QUOTES, 'UTF-8') ?>">
                    <label class="planner-field">
                        <span>Rating</span>
                        <select name="rating" required>
                            <option value="5">5 stars - Excellent</option>
                            <option value="4">4 stars - Very good</option>
                            <option value="3">3 stars - Good</option>
                            <option value="2">2 stars - Could improve</option>
                            <option value="1">1 star - Poor</option>
                        </select>
                    </label>
                    <label class="planner-field">
                        <span>Your review</span>
                        <textarea name="review_text" rows="4" minlength="10" required placeholder="Write about the venue, staff, ambience, parking, food, decor, or event setup."></textarea>
                    </label>
                    <button type="submit" class="hero-link review-submit-button">Submit review</button>
                </form>
            <?php else: ?>
                <div class="review-login-card mt-4">
                    <p class="text-plum/75">Please sign in before submitting a review. We will bring you back to this venue page after OTP verification.</p>
                    <a class="hero-link mt-4" href="<?= htmlspecialchars($url('login') . '?redirect=' . urlencode($url('venues/' . (string) $venue['slug']) . '#guest-reviews'), ENT_QUOTES, 'UTF-8') ?>">Sign in to review</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="glass-panel p-6">
        <p class="text-sm uppercase tracking-[0.25em] text-plum/50">User posts</p>
        <h2 class="mt-2 text-3xl font-semibold">Post slots from past guests</h2>
        <p class="mt-4 text-plum/75">
            These cards are kept ready for guest photo drops, celebration snippets, and short story-style posts instead of generic media placeholders.
        </p>
        <div class="mt-6 grid gap-4">
            <?php foreach (($venue['userPosts'] ?? []) as $slot): ?>
                <article class="video-slot-card">
                    <div class="video-slot-card__frame">User Post Slot</div>
                    <h3 class="mt-4 text-xl font-semibold"><?= htmlspecialchars((string) $slot['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mt-2 text-plum/70"><?= htmlspecialchars((string) $slot['description'], ENT_QUOTES, 'UTF-8') ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="booking-planner" class="mt-6 glass-panel p-6">
    <p class="text-sm uppercase tracking-[0.25em] text-plum/50">Booking planner</p>
    <h2 class="mt-2 text-3xl font-semibold">Capture your event details before checkout</h2>
    <p class="mt-4 max-w-3xl text-plum/75">
        Fill in your preferred event date and time. We will check availability only when you continue, and guests will be asked to sign in without losing the details they entered.
    </p>

    <form
        class="planner-form mt-8"
        method="post"
        action="<?= htmlspecialchars($url('bookings/deposit/initiate'), ENT_QUOTES, 'UTF-8') ?>"
        data-booking-form
        data-authenticated="<?= !empty($currentUser['id']) ? 'true' : 'false' ?>"
        data-draft-key="<?= htmlspecialchars('venue-booking-' . (string) $venue['slug'], ENT_QUOTES, 'UTF-8') ?>"
        data-login-url="<?= htmlspecialchars($url('login') . '?redirect=' . urlencode($url('venues/' . (string) $venue['slug']) . '#booking-planner'), ENT_QUOTES, 'UTF-8') ?>"
        data-register-url="<?= htmlspecialchars($url('register') . '?redirect=' . urlencode($url('venues/' . (string) $venue['slug']) . '#booking-planner'), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="venue_slug" value="<?= htmlspecialchars((string) $venue['slug'], ENT_QUOTES, 'UTF-8') ?>">

        <label class="planner-field">
            <span>Event date and time</span>
            <input
                type="datetime-local"
                name="event_datetime"
                required
                min="<?= htmlspecialchars(date('Y-m-d\TH:i'), ENT_QUOTES, 'UTF-8') ?>"
                data-booking-datetime>
            <small class="text-plum/55">Past date-times are blocked. Availability is checked after you continue.</small>
        </label>

        <label class="planner-field planner-field--wide">
            <span>Guest count</span>
            <input type="number" name="guest_count" min="10" step="1" placeholder="250 guests expected">
        </label>

        <label class="planner-field">
            <span>Occasion</span>
            <select name="occasion">
                <option value="">Select event type</option>
                <option value="Corporate event">Corporate event</option>
                <option value="Wedding celebration">Wedding celebration</option>
                <option value="Birthday party">Birthday party</option>
                <option value="Private dinner">Private dinner</option>
            </select>
        </label>

        <label class="planner-field planner-field--wide">
            <span>Special notes</span>
            <textarea name="notes" rows="4" placeholder="AV setup, decor timing, parking, food preference, or anything else the venue should know."></textarea>
        </label>

        <button type="submit" class="hero-link planner-submit">
            Check availability
        </button>
    </form>
</section>

<div class="auth-modal" data-auth-modal hidden>
    <div class="auth-modal__backdrop" data-auth-modal-close></div>
    <div class="auth-modal__panel glass-panel">
        <button type="button" class="auth-modal__close" data-auth-modal-close aria-label="Close sign in prompt">x</button>
        <span class="badge-chip">Sign in required</span>
        <h3 class="mt-4 text-3xl font-semibold">Save your details and continue booking</h3>
        <p class="mt-4 text-plum/75">
            Your event details are kept on this page. Sign in or create an account, and when you return the same booking form values will still be filled in.
        </p>
        <div class="hero-actions mt-6">
            <a class="hero-link" href="<?= htmlspecialchars($url('login') . '?redirect=' . urlencode($url('venues/' . (string) $venue['slug']) . '#booking-planner'), ENT_QUOTES, 'UTF-8') ?>" data-auth-login>Sign in</a>
            <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('register') . '?redirect=' . urlencode($url('venues/' . (string) $venue['slug']) . '#booking-planner'), ENT_QUOTES, 'UTF-8') ?>" data-auth-register>Register</a>
        </div>
    </div>
</div>
