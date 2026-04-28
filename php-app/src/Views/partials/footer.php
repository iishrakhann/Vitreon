<footer class="site-footer px-4 pb-8 pt-8 sm:px-6 lg:px-10">
    <div class="site-footer__panel">
        <div class="site-footer__top">
            <div class="site-footer__brand-block">
                <img class="site-footer__logo" src="<?= htmlspecialchars($url('assets/vitreon-logo.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="VITREON logo">
                <div>
                    <div class="site-footer__brand">VITREON</div>
                    <p class="site-footer__copy">Premium venue discovery, booking management, and event coordination built for modern city events.</p>
                </div>
            </div>
            <div class="site-footer__columns">
                <div class="site-footer__column">
                    <div class="site-footer__title">Where To Buy</div>
                    <a href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>">See Authorized Venues</a>
                    <a href="<?= htmlspecialchars($url('bookings'), ENT_QUOTES, 'UTF-8') ?>">My Bookings</a>
                    <a href="<?= htmlspecialchars($url('dashboard'), ENT_QUOTES, 'UTF-8') ?>">My Account</a>
                </div>
                <div class="site-footer__column">
                    <div class="site-footer__title">Vitreon Access</div>
                    <a href="<?= htmlspecialchars($url('login'), ENT_QUOTES, 'UTF-8') ?>">Login</a>
                    <a href="<?= htmlspecialchars($url('register'), ENT_QUOTES, 'UTF-8') ?>">Create an Account</a>
                </div>
                <div class="site-footer__column">
                    <div class="site-footer__title">News &amp; Info</div>
                    <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>">About Vitreon</a>
                    <a href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>">Venue Updates</a>
                    <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>">Support</a>
                </div>
                <div class="site-footer__column">
                    <div class="site-footer__title">Other Sites</div>
                    <a href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>">Home</a>
                    <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>">Company Info</a>
                    <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>">Social Media</a>
                </div>
            </div>
        </div>
        <div class="site-footer__bottom">
            <div class="site-footer__region">India</div>
            <div class="site-footer__utility">
                <a href="#" aria-label="Facebook">f</a>
                <a href="#" aria-label="Twitter">t</a>
                <a href="#" aria-label="Instagram">ig</a>
                <a href="#" aria-label="Tumblr">t</a>
            </div>
        </div>
        <div class="site-footer__legal">
            <span>Copyright 2026 Vitreon</span>
        </div>
    </div>
</footer>
