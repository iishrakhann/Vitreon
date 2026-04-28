<section class="auth-shell">
    <article class="glass-panel auth-card animate-morph p-8">
        <span class="badge-chip">Verify OTP</span>
        <h1 class="mt-4 text-4xl font-semibold">Enter the six-digit code</h1>
        <p class="mt-4 text-plum/75">
            We generated a one-time password for your email: <?= htmlspecialchars((string) ($identity ?? 'your account'), ENT_QUOTES, 'UTF-8') ?>.
        </p>

        <?php if (!empty($message)): ?>
            <div class="auth-message mt-6"><?= htmlspecialchars((string) $message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="auth-message auth-message--error mt-6"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form class="mt-8" method="post" action="<?= htmlspecialchars($url('otp/verify'), ENT_QUOTES, 'UTF-8') ?>" data-otp-form>
            <input type="hidden" name="otp" value="" data-otp-hidden required>
            <label class="planner-field planner-field--wide">
                <span>One-time password</span>
                <div class="otp-inputs" data-otp-inputs>
                    <?php for ($index = 0; $index < 6; $index++): ?>
                        <input
                            class="otp-input"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="<?= $index === 0 ? 'one-time-code' : 'off' ?>"
                            maxlength="1"
                            data-otp-digit
                            aria-label="OTP digit <?= $index + 1 ?>"
                            <?= $index === 0 ? 'autofocus' : '' ?>>
                    <?php endfor; ?>
                </div>
            </label>
            <button type="submit" class="hero-link planner-submit" data-otp-submit>
                <span data-otp-submit-label>Verify and continue</span>
            </button>
        </form>
        <form class="mt-6" method="post" action="<?= htmlspecialchars($url('otp/resend'), ENT_QUOTES, 'UTF-8') ?>" data-otp-resend-form>
            <button type="submit" class="hero-link hero-link--secondary planner-submit" data-otp-resend-button disabled>
                Resend OTP
            </button>
            <p class="mt-3 text-sm text-plum/65" data-otp-resend-note data-resend-seconds="<?= htmlspecialchars((string) ($resendAvailableIn ?? 60), ENT_QUOTES, 'UTF-8') ?>">
                Didn't receive OTP? You can resend it in 60 seconds.
            </p>
        </form>

        <?php if (!empty($demoOtp)): ?>
            <div class="auth-hint mt-8">
                Demo account OTP: <strong><?= htmlspecialchars((string) $demoOtp, ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        <?php endif; ?>
    </article>
</section>
