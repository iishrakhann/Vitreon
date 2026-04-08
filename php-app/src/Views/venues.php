<section class="glass-panel animate-morph p-8">
    <span class="badge-chip">Venue catalogue</span>
    <h1 class="mt-4 text-4xl font-semibold">Browse Pune venues by celebration type</h1>
    <p class="mt-4 max-w-3xl text-plum/75">
        Explore the full venue directory across weddings, corporate events, receptions, concerts, exhibitions, cocktail nights, and more.
    </p>
</section>

<section class="mt-8 grid gap-5 lg:grid-cols-3">
    <?php foreach ($venues as $venue): ?>
        <article class="venue-card">
            <img class="venue-card__image" src="<?= htmlspecialchars((string) $venue['cardImage'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="venue-card__top">
                <span class="badge-chip"><?= htmlspecialchars($venue['neighborhood'], ENT_QUOTES, 'UTF-8') ?></span>
                <span class="text-sm text-plum/65"><?= htmlspecialchars($venue['eventType'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <h2 class="mt-5 text-2xl font-semibold"><?= htmlspecialchars($venue['name'], ENT_QUOTES, 'UTF-8') ?></h2>
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
</section>
