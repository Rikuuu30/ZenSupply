<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="auth-section shell">
    <div class="auth-intro">
        <span class="eyebrow">STAFF ACCESS / ZEN SUPPLY POS</span>
        <h1>Welcome to<br><em>your counter.</em></h1>
        <p>Sign in to manage inventory, customers, staff access, and sales. Your account session is kept on the server and ends when you sign out.</p>
        <div class="auth-points">
            <div><span>01</span><p><strong>Verified staff access</strong><br>Every store management page checks your active staff session.</p></div>
            <div><span>02</span><p><strong>Protected credentials</strong><br>Your password is checked against a stored hash and is never saved in your session.</p></div>
        </div>
    </div>

    <div class="auth-card">
        <span class="eyebrow">ZEN SUPPLY / TEAM PORTAL</span>
        <h2>Sign in</h2>
        <p class="auth-subtitle">Enter your staff username and password to continue.</p>

        <?php if (($formErrors ?? []) !== []): ?>
            <div class="auth-error" role="alert">Enter a username and password to continue.</div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="post" class="auth-form">
            <?= csrf_field() ?>
            <div class="form-field">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" maxlength="50" autocomplete="username" value="<?= esc(old('username', ''), 'attr') ?>" required autofocus aria-invalid="<?= isset($formErrors['username']) ? 'true' : 'false' ?>">
                <?php if (isset($formErrors['username'])): ?><small class="field-error"><?= esc($formErrors['username']) ?></small><?php endif; ?>
            </div>
            <div class="form-field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required aria-invalid="<?= isset($formErrors['password']) ? 'true' : 'false' ?>">
                <?php if (isset($formErrors['password'])): ?><small class="field-error"><?= esc($formErrors['password']) ?></small><?php endif; ?>
            </div>
            <button class="button primary auth-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
        </form>

        <div class="auth-demo"><strong>Local coursework demo account</strong><span>Username: <code>admin.rikuuu</code></span><span>Password: <code>ZSD123</code></span></div>
        <p class="auth-disclaimer">Sample credentials are for local coursework only. Replace them before publishing or using real staff accounts.</p>
    </div>
</section>
<?= $this->endSection() ?>
