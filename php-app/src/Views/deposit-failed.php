<section class="glass-panel animate-morph p-8">
    <span class="badge-chip">Unable to lock slot</span>
    <h1 class="mt-4 text-4xl font-semibold">This venue is temporarily unavailable.</h1>
    <p class="mt-4 max-w-2xl text-plum/75">
        <?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?>
    </p>
    <a class="mt-8 inline-flex rounded-full bg-white/55 px-6 py-3 text-sm font-medium text-plum shadow-glow transition hover:-translate-y-1" href="<?= htmlspecialchars($url('/'), ENT_QUOTES, 'UTF-8') ?>">
        Return to discovery
    </a>
</section>
