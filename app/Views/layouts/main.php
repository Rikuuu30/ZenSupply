<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Zen Supply POS — a focused point-of-sale workspace for martial arts gear retailers.">
    <title><?= esc($pageTitle) ?> · Zen Supply POS</title>
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>?v=19">
    <script src="<?= base_url('assets/js/app.js') ?>" defer></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>

    <header class="site-header">
        <div class="shell nav-shell">
            <a class="brand" href="<?= site_url('/') ?>" aria-label="Zen Supply POS home">
                <span class="brand-mark" aria-hidden="true">Z</span>
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
                <span class="brand-mark" aria-hidden="true">Z</span>
                <div class="footer-brand-copy"><span class="footer-overline">ZEN SUPPLY / POINT OF SALE</span><strong>Made for the dojo floor.</strong><p>Martial arts gear, everyday inventory, and a smoother checkout—together in one workspace.</p><span class="footer-capabilities"><i>01</i> STOCK <i>02</i> SALES <i>03</i> CUSTOMERS</span></div>
            </div>
            <div class="footer-meta"><span class="footer-overline">STORE PROFILE</span><strong>Makati City</strong><p>Monday–Saturday<br>9:00 AM–8:00 PM</p><span class="footer-store-note"><i></i>Zen Supply flagship concept</span></div>
            <nav class="footer-nav" aria-label="Quick links"><span class="footer-overline">QUICK LINKS</span>
                <a href="<?= site_url('/') ?>">Dashboard</a>
                <?php if (session()->has('user_id')): ?>
                <a href="<?= site_url('products') ?>">Products</a>
                <a href="<?= site_url('sales') ?>">Sales</a>
                <a href="<?= site_url('customers') ?>">Customers</a>
                <?php if (session()->get('role') === 'admin'): ?><a href="<?= site_url('users') ?>">Staff</a><?php endif; ?>
                <?php else: ?>
                <a href="<?= site_url('login') ?>">Staff sign in</a>
                <?php endif; ?>
                <a href="<?= site_url('about') ?>">About</a>
                <a href="<?= esc(rtrim((string) env('tasks.baseURL', 'http://localhost:8082/'), '/') . '/', 'attr') ?>" target="_blank" rel="noopener noreferrer">Tasks for Today ↗</a>
            </nav>
        </div>
        <div class="shell footer-bottom"><span>ZEN SUPPLY POS <i>·</i> RETAIL WORKSPACE</span><span>MAKATI CITY <i>·</i> <?= date('Y') ?></span></div>
    </footer>
</body>
</html>
