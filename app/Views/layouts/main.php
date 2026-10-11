<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zen Supply POS — a focused point-of-sale workspace for martial arts gear retailers.">
    <title><?= esc($pageTitle) ?> · Zen Supply POS</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=24">
    <script src="<?= base_url('assets/js/app.js') ?>" defer></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>

    <header class="site-header">
        <div class="shell nav-shell">
            <a class="brand" href="<?= site_url('/') ?>" aria-label="Zen Supply POS home">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 64 64" focusable="false"><path class="crest-outline" d="M32 3 54 11v18c0 14-9 25-22 32C19 54 10 43 10 29V11L32 3Z"/><path class="crest-stroke" d="m20 23 24 18M44 23 20 41"/><path class="crest-stroke" d="m32 26 6 6-6 6-6-6 6-6ZM23 16h18M23 48h18"/><circle cx="19" cy="22" r="1.5"/><circle cx="45" cy="22" r="1.5"/><circle cx="19" cy="42" r="1.5"/><circle cx="45" cy="42" r="1.5"/></svg></span>
                <span>
                    <strong>Zen Supply</strong>
                    <small>Martial arts · Point of sale</small>
                </span>
                <?php if (($activePage ?? '') === 'home' && session()->has('user_id') && isset($today)): ?><span class="brand-sales-mini"><small>Today’s sales</small><strong>₱<?= esc(number_format((float) ($today['revenue'] ?? 0), 2)) ?></strong><span><?= esc((string) ($today['transactions'] ?? 0)) ?> receipts</span></span><?php endif; ?>
            </a>

            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
                <span class="sr-only">Toggle navigation</span>
                <span></span><span></span><span></span>
            </button>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Primary navigation">
                <a class="<?= $activePage === 'home' ? 'active' : '' ?>" href="<?= site_url('/') ?>" <?= $activePage === 'home' ? 'aria-current="page"' : '' ?>>Home</a>
                <?php if (session()->has('user_id')): ?>
                <a class="<?= $activePage === 'products' ? 'active' : '' ?>" href="<?= site_url('products') ?>" <?= $activePage === 'products' ? 'aria-current="page"' : '' ?>>Products</a>
                <a class="<?= $activePage === 'sales' ? 'active' : '' ?>" href="<?= site_url('sales') ?>" <?= $activePage === 'sales' ? 'aria-current="page"' : '' ?>>Sales</a>
                <a class="<?= $activePage === 'customers' ? 'active' : '' ?>" href="<?= site_url('customers') ?>" <?= $activePage === 'customers' ? 'aria-current="page"' : '' ?>>Customers</a>
                <?php if (session()->get('role') === 'admin'): ?><a class="<?= $activePage === 'users' ? 'active' : '' ?>" href="<?= site_url('users') ?>" <?= $activePage === 'users' ? 'aria-current="page"' : '' ?>>Staff</a><?php endif; ?>
                <?php else: ?>
                <a class="<?= $activePage === 'login' ? 'active' : '' ?>" href="<?= site_url('login') ?>" <?= $activePage === 'login' ? 'aria-current="page"' : '' ?>>Staff sign in</a>
                <?php endif; ?>
                <a class="<?= $activePage === 'about' ? 'active' : '' ?>" href="<?= site_url('about') ?>" <?= $activePage === 'about' ? 'aria-current="page"' : '' ?>>About</a>
                <a class="tasks-link" href="<?= esc(rtrim((string) env('tasks.baseURL', 'http://localhost:8082/'), '/') . '/', 'attr') ?>" target="_blank" rel="noopener noreferrer">Tasks for Today <span aria-hidden="true">↗</span></a>
            </nav>
            <button class="theme-toggle" type="button" id="theme-toggle" aria-label="Switch to dark mode" aria-pressed="false"><span aria-hidden="true">◐</span><span class="theme-toggle-label">Dark mode</span></button>
            <?php if (session()->has('user_id')): ?>
                <div class="staff-session"><span>Signed in as <strong><?= esc(session()->get('full_name')) ?></strong></span><form action="<?= site_url('logout') ?>" method="post"><?= csrf_field() ?><button type="submit">Sign out</button></form></div>
            <?php endif; ?>

        </div>
    </header>

    <?php if ($message = session()->getFlashdata('success')): ?>
        <div class="shell flash-message success-message" role="status"><?= esc($message) ?></div>
    <?php elseif ($message = session()->getFlashdata('error')): ?>
        <div class="shell flash-message error-message" role="alert"><?= esc($message) ?></div>
    <?php endif; ?>

    <main id="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="site-footer" id="site-footer">
        <div class="shell footer-inner">
            <div class="footer-brand">
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 64 64" focusable="false"><path class="crest-outline" d="M32 3 54 11v18c0 14-9 25-22 32C19 54 10 43 10 29V11L32 3Z"/><path class="crest-stroke" d="m20 23 24 18M44 23 20 41"/><path class="crest-stroke" d="m32 26 6 6-6 6-6-6 6-6ZM23 16h18M23 48h18"/><circle cx="19" cy="22" r="1.5"/><circle cx="45" cy="22" r="1.5"/><circle cx="19" cy="42" r="1.5"/><circle cx="45" cy="42" r="1.5"/></svg></span>
                <div class="footer-brand-copy"><span class="footer-overline">ZEN SUPPLY / POINT OF SALE</span><strong>Made for the dojo floor.</strong><p>Martial arts gear, everyday inventory, and a smoother checkout—together in one workspace.</p><span class="footer-capabilities"><i>01</i> STOCK <i>02</i> SALES <i>03</i> CUSTOMERS</span></div>
            </div>
            <div class="footer-meta"><span class="footer-overline">STORE PROFILE</span><strong>Caloocan City</strong><p>Building 123<br>Caloocan City, Metro Manila</p><span class="footer-store-note"><i></i>Zen Supply · Martial arts retail</span></div>
            <nav class="footer-nav" aria-label="Social media links"><span class="footer-overline">FOLLOW ZEN SUPPLY</span>
                <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.4 21v-8.2h2.8l.4-3.2h-3.2V7.5c0-.9.3-1.5 1.6-1.5h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.2v3.2H10V21h3.4Z"/></svg><span>Facebook</span></a>
                <a href="https://x.com/" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23.2 22h-6.3L12 14.7 5.6 22H2.4l7.3-8.4L1.9 2h6.5l4.4 6.7L18.9 2Zm-1.1 18h1.7L7.4 3.9H5.6L17.8 20Z"/></svg><span>X / Twitter</span></a>
                <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle class="social-icon-dot" cx="17.6" cy="6.6" r="1.1"/></svg><span>Instagram</span></a>
            </nav>
        </div>
        <div class="shell footer-bottom"><span>ZEN SUPPLY POS <i>·</i> RETAIL WORKSPACE</span><span>CALOOCAN CITY <i>·</i> <?= date('Y') ?></span></div>
    </footer>
</body>
</html>
