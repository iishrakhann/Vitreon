<footer class="site-footer px-4 pb-24 pt-8 sm:px-6 lg:px-10">
    <div class="site-footer__inner">
        <div class="site-footer__identity">
            <img class="site-footer__logo" src="<?= htmlspecialchars($url('assets/vitreon-logo.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="VITREON logo">
            <div>
                <div class="site-footer__brand">VITREON</div>
                <p class="site-footer__copy">Clarity. Connection. Control. Premium venue discovery, booking confirmation, and direct owner coordination for city events.</p>
            </div>
        </div>
        <div class="site-footer__links">
            <a href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>">Home</a>
            <a href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>">Venues</a>
            <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>">About</a>
            <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>">Contact</a>
        </div>
    </div>
</footer>
