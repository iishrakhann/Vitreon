<?php
$role = strtoupper((string) ($currentUser['role'] ?? 'GUEST'));
$path = $currentPath ?? '/';
$isHome = $path === '/';
$isVenues = $path === '/venues' || str_starts_with($path, '/venues/');
$isAbout = $path === '/about';
$isContact = $path === '/contact';
?>
<nav class="bottom-nav md:hidden">
    <a href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isHome ? ' is-active' : '' ?>">
        <span>Home</span>
    </a>
    <a href="<?= htmlspecialchars($url('venues'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isVenues ? ' is-active' : '' ?>">
        <span>Venues</span>
    </a>
    <?php if ($role === 'CUSTOMER'): ?>
        <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isAbout ? ' is-active' : '' ?>">
            <span>About</span>
        </a>
        <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isContact ? ' is-active' : '' ?>">
            <span>Contact</span>
        </a>
    <?php elseif ($role === 'OWNER'): ?>
        <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isAbout ? ' is-active' : '' ?>">
            <span>About</span>
        </a>
        <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isContact ? ' is-active' : '' ?>">
            <span>Contact</span>
        </a>
    <?php elseif ($role === 'ADMIN'): ?>
        <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isAbout ? ' is-active' : '' ?>">
            <span>About</span>
        </a>
        <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isContact ? ' is-active' : '' ?>">
            <span>Contact</span>
        </a>
    <?php else: ?>
        <a href="<?= htmlspecialchars($url('about'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isAbout ? ' is-active' : '' ?>">
            <span>About</span>
        </a>
        <a href="<?= htmlspecialchars($url('contact'), ENT_QUOTES, 'UTF-8') ?>" class="bottom-nav__item<?= $isContact ? ' is-active' : '' ?>">
            <span>Contact</span>
        </a>
    <?php endif; ?>
</nav>
