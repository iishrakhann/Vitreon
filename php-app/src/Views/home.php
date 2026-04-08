<section class="glass-panel animate-morph p-8">
    <span class="badge-chip">Venue catalogue</span>
    <h1 class="mt-4 text-4xl font-semibold">Discover Pune venues with live availability and cleaner booking flow</h1>
    <p class="mt-4 max-w-3xl text-plum/75">
        Browse weddings, receptions, corporate events, concerts, exhibitions, and birthday venues with gallery previews and cleaner booking details.
    </p>
    <div class="hero-actions mt-8">
        <a class="hero-link" href="#featured-venues">Explore featured venues</a>
        <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>">Open full venue directory</a>
    </div>
</section>

<section class="mt-8 grid gap-5 lg:grid-cols-3">
    <article class="venue-card">
        <div class="venue-card__top">
            <span class="badge-chip">Booking flow</span>
            <span class="text-sm text-plum/65">Confirmation fee</span>
        </div>
        <h2 class="mt-5 text-2xl font-semibold">Reservation flow built for real event planning</h2>
        <p class="mt-3 text-plum/70">
            Capture event date, guest count, and special notes before checkout so each booking request reaches owners with proper context.
        </p>
        <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
            <div class="info-chip">
                <span class="text-plum/55">Hold window</span>
                <strong>Manual verification</strong>
            </div>
            <div class="info-chip">
                <span class="text-plum/55">Checkout</span>
                <strong>UPI / QR Payment</strong>
            </div>
        </div>
    </article>

    <article class="venue-card">
        <div class="venue-card__top">
            <span class="badge-chip">Discovery map</span>
            <span class="text-sm text-plum/65">Pune focus</span>
        </div>
        <h2 class="mt-5 text-2xl font-semibold">Search by neighborhood and celebration type</h2>
        <p class="mt-3 text-plum/70">
            Baner, Koregaon Park, Kalyani Nagar, Hinjewadi, Magarpatta, and more are mapped into a single venue browser.
        </p>
        <div class="mt-6 flex flex-wrap gap-3 text-sm text-plum/75">
            <div class="location-pill">Baner</div>
            <div class="location-pill">Koregaon Park</div>
            <div class="location-pill">Kalyani Nagar</div>
            <div class="location-pill">Hinjewadi</div>
        </div>
    </article>

    <article class="venue-card">
        <div class="venue-card__top">
            <span class="badge-chip">Quick booking</span>
            <span class="text-sm text-plum/65">Guest friendly</span>
        </div>
        <h2 class="mt-5 text-2xl font-semibold">Simple event-first booking journey</h2>
        <p class="mt-3 text-plum/70">
            Pick your date, add event details, sign in when needed, and continue without losing the form you already filled.
        </p>
        <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
            <div class="info-chip">
                <span class="text-plum/55">Sign-in flow</span>
                <strong>Same-page popup</strong>
            </div>
            <div class="info-chip">
                <span class="text-plum/55">Confirmation</span>
                <strong>Manual UPI / QR</strong>
            </div>
        </div>
    </article>
</section>

<section class="mt-8 glass-panel p-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-plum/50">Pune map</p>
            <h2 class="mt-2 text-3xl font-semibold">Neighborhood coverage for venue discovery</h2>
        </div>
        <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('bookings'), ENT_QUOTES, 'UTF-8') ?>">My bookings</a>
    </div>
    <div class="mt-6 map-card">
        <div class="grid gap-3 text-sm text-plum/75 sm:grid-cols-4">
            <div class="location-pill">Baner</div>
            <div class="location-pill">Koregaon Park</div>
            <div class="location-pill">Kalyani Nagar</div>
            <div class="location-pill">Hinjewadi</div>
        </div>
        <div class="map-placeholder">
            <iframe
                class="map-embed"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                src="<?= htmlspecialchars((string) $googleMapsEmbedUrl, ENT_QUOTES, 'UTF-8') ?>"
                title="Pune Event Discovery Map">
            </iframe>
        </div>
    </div>
</section>

<section id="featured-venues" class="mt-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="text-sm uppercase tracking-[0.25em] text-plum/50">Featured venues</p>
            <h2 class="mt-2 text-3xl font-semibold">Featured spaces for Pune events</h2>
        </div>
        <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>">See all venues</a>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-3">
        <?php foreach ($venues as $venue): ?>
            <article class="venue-card" data-event-type="<?= htmlspecialchars($venue['eventType'], ENT_QUOTES, 'UTF-8') ?>">
                <img class="venue-card__image" src="<?= htmlspecialchars((string) $venue['cardImage'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>">
                <div class="venue-card__top">
                    <span class="badge-chip"><?= htmlspecialchars($venue['neighborhood'], ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-sm text-plum/65"><?= htmlspecialchars($venue['eventType'], ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <h3 class="mt-5 text-2xl font-semibold"><?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mt-2 text-plum/70"><?= htmlspecialchars($venue['capacity'], ENT_QUOTES, 'UTF-8') ?></p>
                <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                    <div class="info-chip">
                        <span class="text-plum/55">Starting</span>
                        <strong><?= htmlspecialchars($venue['price'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                    <div class="info-chip">
                        <span class="text-plum/55">Category</span>
                        <strong><?= htmlspecialchars($venue['eventType'], ENT_QUOTES, 'UTF-8') ?></strong>
                    </div>
                </div>
                <div class="card-actions mt-6">
                    <a class="hero-link hero-link--secondary" href="<?= htmlspecialchars($url('venues/' . $venue['slug']), ENT_QUOTES, 'UTF-8') ?>">View details</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
