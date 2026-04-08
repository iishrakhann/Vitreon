<?php
$form = is_array($form ?? null) ? $form : [];
$redirect = trim((string) ($redirect ?? ''));
?>
<section class="auth-shell">
    <article class="glass-panel auth-card animate-morph p-8">
        <span class="badge-chip">OTP registration</span>
        <h1 class="mt-4 text-4xl font-semibold">Create a customer or owner account</h1>
        <p class="mt-4 text-plum/75">
            Registration uses an email-based one-time password flow so the app can stay password-light while still separating customer and owner experiences.
        </p>

        <?php if (!empty($error)): ?>
            <div class="auth-message auth-message--error mt-6"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form class="planner-form mt-8" method="post" action="<?= htmlspecialchars($url('register/request-otp'), ENT_QUOTES, 'UTF-8') ?>">
            <label class="planner-field">
                <span>Full name</span>
                <input type="text" name="full_name" value="<?= htmlspecialchars((string) ($form['fullName'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label class="planner-field">
                <span>Email</span>
                <input type="email" name="email" value="<?= htmlspecialchars((string) ($form['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
            </label>
            <label class="planner-field">
                <span>Phone number</span>
                <input type="tel" name="phone_number" value="<?= htmlspecialchars((string) ($form['phoneNumber'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" minlength="10" placeholder="10-digit Indian mobile number" required>
            </label>
            <label class="planner-field">
                <span>Register as</span>
                <select name="role">
                    <option value="CUSTOMER" <?= (($form['role'] ?? 'CUSTOMER') === 'CUSTOMER') ? 'selected' : '' ?>>Customer</option>
                    <option value="OWNER" <?= (($form['role'] ?? '') === 'OWNER') ? 'selected' : '' ?>>Owner</option>
                </select>
            </label>
            <button type="submit" class="hero-link planner-submit">Generate OTP</button>
        </form>
        <a class="hero-link hero-link--secondary mt-6" href="<?= htmlspecialchars($url('login') . ($redirect !== '' ? '?redirect=' . urlencode($redirect) : ''), ENT_QUOTES, 'UTF-8') ?>">Already have an account?</a>
    </article>
</section>
