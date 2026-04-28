<?php $redirect = trim((string) ($redirect ?? '')); ?>
<section class="auth-shell">
    <article class="glass-panel auth-card animate-morph p-8">
        <span class="badge-chip">OTP login</span>
        <h1 class="mt-4 text-4xl font-semibold">Sign in with email or phone</h1>
        <p class="mt-4 text-plum/75">
            Enter the email address or phone number tied to your account. We send the one-time password to the account email address and verify it on the next step.
        </p>

        <?php if (!empty($error)): ?>
            <div class="auth-message auth-message--error mt-6"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form class="planner-form mt-8" method="post" action="<?= htmlspecialchars($url('login/request-otp'), ENT_QUOTES, 'UTF-8') ?>">
            <label class="planner-field planner-field--wide">
                <span>Email or phone</span>
                <input type="text" name="identity" value="<?= htmlspecialchars((string) ($identity ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="owner@vitreon.local or +919900000001" required>
            </label>
            <button type="submit" class="hero-link planner-submit">Send OTP</button>
        </form>

        <div class="auth-hint mt-8">
            <strong>Demo accounts:</strong> `admin@puneeventhub.local`, `owner1@puneeventhub.local`, `owner2@puneeventhub.local`, or `aarav@puneeventhub.local`.
        </div>
        <a class="hero-link hero-link--secondary mt-6" href="<?= htmlspecialchars($url('register') . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''), ENT_QUOTES, 'UTF-8') ?>">Create account</a>
    </article>
</section>
