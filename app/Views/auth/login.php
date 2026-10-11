<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="auth-section shell">
    <div class="auth-intro">
        <span class="eyebrow">STAFF ACCESS / ZEN SUPPLY POS</span>
        <h1>Sign in to<br><em>record an order.</em></h1>
        <p>Log in to the Zen Supply POS to record customer purchases and manage products, inventory, customers, and sales.</p>
        <figure class="auth-visual">
            <img src="<?= base_url('assets/images/signin-dojo-editorial.png') ?>" alt="Oxblood training gloves and hand wraps on a bench in a softly lit martial arts dojo" width="2048" height="640">
        </figure>
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

    </div>
</section>
<?= $this->endSection() ?>
