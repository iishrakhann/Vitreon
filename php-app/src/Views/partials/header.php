<header class="site-header sticky top-0 z-30">
    <div class="site-header__inner flex w-full items-center justify-between px-4 py-4 sm:px-6 lg:px-10">
        <a class="site-brand" href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>">
            <img class="site-brand__logo" src="<?= htmlspecialchars($url('assets/vitreon-logo.jpg'), ENT_QUOTES, 'UTF-8') ?>" alt="VITREON logo">
            <span class="site-brand__copy">
                <span class="site-brand__name">VITREON</span>
                <span class="site-brand__tag">Clarity. Connection. Control.</span>
            </span>
        </a>
        <?php
        $role = strtoupper((string) ($currentUser['role'] ?? 'GUEST'));
        $path = $currentPath ?? '/';
        $isHome = $path === '/';
        $isVenues = $path === '/venues' || str_starts_with($path, '/venues/');
        $isAbout = $path === '/about';
        $isContact = $path === '/contact';
        $isBookings = $path === '/bookings';
        $isDashboard = $path === '/dashboard';
        ?>
        <nav class="site-nav hidden items-center gap-3 text-sm text-plum/80 md:flex">
            <a href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum<?= $isHome ? ' is-active' : '' ?>">Home</a>
            <a href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum<?= $isVenues ? ' is-active' : '' ?>">Venues</a>
            <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum<?= $isAbout ? ' is-active' : '' ?>">About</a>
            <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum<?= $isContact ? ' is-active' : '' ?>">Contact</a>
            <?php if (!empty($currentUser)): ?>
                <a href="<?= htmlspecialchars($url('bookings'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum<?= $isBookings ? ' is-active' : '' ?>">Bookings</a>
                <a href="<?= htmlspecialchars($url('dashboard'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum<?= $isDashboard ? ' is-active' : '' ?>">
                    <?= $role === 'ADMIN' ? 'Admin Suite' : ($role === 'OWNER' ? 'Owner Suite' : 'Dashboard') ?>
                </a>
                <span class="account-pill rounded-full px-4 py-2">
                    <?= htmlspecialchars((string) ($currentUser['name'] ?? 'Account'), ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <a href="<?= htmlspecialchars($url('logout'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum">Logout</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($url('login'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum">Login</a>
                <a href="<?= htmlspecialchars($url('register'), ENT_QUOTES, 'UTF-8') ?>" class="site-nav__link transition hover:text-plum">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
