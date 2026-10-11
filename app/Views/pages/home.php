<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="shell dashboard-home">
    <header class="dashboard-heading">
        <div>
            <span class="dashboard-kicker">ZEN SUPPLY <i>/</i> TODAY'S FLOOR</span>
            <h1>Store overview</h1>
            <p>Sales, training gear, and the details that keep the shop moving.</p>
        </div>
        <div class="dashboard-heading-actions"><span class="dashboard-date"><?= esc(date('l, F j, Y')) ?></span></div>
    </header>

    <?php $weekRevenue = array_sum(array_column($weeklySales, 'revenue')); $weekReceipts = array_sum(array_column($weeklySales, 'transactions')); $weekPeak = max(1, ...array_column($weeklySales, 'revenue')); ?>
    <section class="shopfront" aria-label="Zen Supply equipment and store actions">
        <header class="shopfront-intro"><div class="shopfront-index"><span>ZEN SUPPLY</span><b>01</b><small>TRAINING GOODS<br>STORE FLOOR</small></div><div class="shopfront-title"><span>FIGHT GEAR / DAILY ESSENTIALS / ZEN SUPPLY</span><h2>Gear for the<br><em>work you put in.</em></h2></div><div class="shopfront-welcome"><p>Find equipment made for practice, progress, and the next round. Browse today's live stock or head straight to checkout.</p><div><a class="shopfront-browse" href="<?= site_url('products') ?>">Browse equipment <b aria-hidden="true">↗</b></a><a class="shopfront-sale" href="<?= site_url('sales/new') ?>">Record a sale <span aria-hidden="true">→</span></a></div><small>STORE OPEN <i>·</i> <?= esc(date('l, F j')) ?></small></div></header>
        <div class="shopfront-rack"><div class="shopfront-rack-head"><div><span>ON THE RACK <i>·</i> LIVE CATALOG</span><h3>Selected for the floor</h3></div><a href="<?= site_url('products') ?>">View all <?= esc((string) ($inventory['products'] ?? 0)) ?> products <b aria-hidden="true">↗</b></a></div>
            <?php if ($showcaseProducts === []): ?><div class="shopfront-empty"><strong>The rack is ready for its first product.</strong><a href="<?= site_url('products') ?>">Open inventory ↗</a></div><?php else: ?>
                <div class="shopfront-products"><?php foreach ($showcaseProducts as $index => $product): ?><?php $wallImage = ! empty($product['image']) ? (str_starts_with((string) $product['image'], 'assets/') ? base_url($product['image']) : base_url('uploads/' . $product['image'])) : base_url('assets/images/product-placeholder.svg'); ?><a class="shopfront-product" href="<?= site_url('products/' . $product['id']) ?>"><div class="shopfront-product-photo"><img src="<?= esc($wallImage, 'attr') ?>" alt="<?= esc($product['name'], 'attr') ?>" loading="lazy"><span>ZEN / 0<?= esc((string) ($index + 1)) ?></span><b aria-hidden="true">↗</b></div><div class="shopfront-product-info"><small><?= esc($product['sku'] ?: 'TRAINING ESSENTIAL') ?></small><strong><?= esc($product['name']) ?></strong><div><b>₱<?= esc(number_format((float) $product['price'], 2)) ?></b><span class="<?= (int) $product['stock_quantity'] <= (int) $product['low_stock_threshold'] ? 'stock-alert' : '' ?>"><?= esc((string) $product['stock_quantity']) ?> available</span></div></div></a><?php endforeach; ?></div>
            <?php endif; ?>
        </div>
    </section>

    <section class="register-overview" aria-label="Sales performance for the last seven days">
        <header class="register-overview-head"><div class="register-overview-title"><span>THE COUNTER <i>·</i> LAST 7 DAYS</span><h2>Weekly sales</h2><p>Daily revenue from completed receipts.</p></div><div class="register-overview-total"><small>GROSS SALES</small><strong>₱<?= esc(number_format($weekRevenue, 2)) ?></strong></div><div class="register-overview-count"><small>RECEIPTS</small><strong><?= esc((string) $weekReceipts) ?></strong></div><a class="register-overview-cta" href="<?= site_url('sales') ?>">Sales history <b aria-hidden="true">↗</b></a></header>
        <div class="register-chart"><div class="register-bars" role="img" aria-label="Daily sales for the last seven days"><?php foreach ($weeklySales as $day): ?><?php $barHeight = $weekPeak > 0 ? max(5, (int) round($day['revenue'] / $weekPeak * 100)) : 5; ?><div class="register-bar-day" title="<?= esc(date('F j', strtotime($day['date']))) ?>: ₱<?= esc(number_format($day['revenue'], 2)) ?> from <?= esc((string) $day['transactions']) ?> receipts"><div class="register-bar-value">₱<?= esc(number_format($day['revenue'], 0)) ?></div><div class="register-bar-track"><span style="height:<?= esc((string) $barHeight) ?>%"></span></div><small><?= esc($day['label']) ?></small></div><?php endforeach; ?></div><div class="register-chart-foot"><span><i></i> REVENUE BY DAY</span><span><?= esc(date('M j', strtotime($weeklySales[0]['date']))) ?>—<?= esc(date('M j', strtotime($weeklySales[6]['date']))) ?></span></div></div>
    </section>

    <section class="dashboard-kpis" aria-label="Store performance summary">
        <article class="dashboard-kpi kpi-revenue"><div><span>Sales today</span><i>PHP</i></div><strong>₱<?= esc(number_format((float) ($today['revenue'] ?? 0), 2)) ?></strong><small><?= esc((string) ($today['transactions'] ?? 0)) ?> receipts <b>·</b> <?= esc((string) ($today['units'] ?? 0)) ?> units sold</small></article>
        <article class="dashboard-kpi"><div><span>Active products</span><i>CATALOG</i></div><strong><?= esc((string) ($inventory['products'] ?? 0)) ?></strong><small><?= esc((string) ($inventory['units'] ?? 0)) ?> units in inventory</small><a href="<?= site_url('products') ?>" aria-label="View product inventory">View inventory <span aria-hidden="true">↗</span></a></article>
        <article class="dashboard-kpi"><div><span>Customers</span><i>ACCOUNTS</i></div><strong><?= esc((string) $customerCount) ?></strong><small>Active customer profiles</small><a href="<?= site_url('customers') ?>" aria-label="View customer accounts">View customers <span aria-hidden="true">↗</span></a></article>
        <article class="dashboard-kpi <?= (int) ($inventory['low_stock'] ?? 0) > 0 ? 'kpi-attention' : 'kpi-clear' ?>"><div><span>Restock attention</span><i><?= (int) ($inventory['low_stock'] ?? 0) > 0 ? 'REVIEW' : 'ON TRACK' ?></i></div><strong><?= esc((string) ($inventory['low_stock'] ?? 0)) ?></strong><small>Products at or below threshold</small><a href="<?= site_url('products?stock=low') ?>" aria-label="Review low stock products">Review stock <span aria-hidden="true">↗</span></a></article>
    </section>

    <div class="dashboard-workspace">
        <div class="dashboard-main-column">
            <section class="panel dashboard-receipts">
                <header class="dashboard-panel-heading"><div><span class="dashboard-kicker">LATEST ACTIVITY</span><h2>Recent receipts</h2></div><a class="dashboard-text-link" href="<?= site_url('sales') ?>">Sales history <span aria-hidden="true">↗</span></a></header>
                <?php if ($recentSales === []): ?>
                    <div class="dashboard-empty"><span class="dashboard-empty-mark">＋</span><div><strong>No receipts yet today</strong><p>New sales will appear here as soon as they are recorded.</p></div><a class="button primary" href="<?= site_url('sales/new') ?>">Create first sale</a></div>
                <?php else: ?>
                    <div class="table-scroll"><table class="transaction-table dashboard-transaction-table"><thead><tr><th>Receipt</th><th>Customer</th><th>Product</th><th>Staff</th><th>Total</th></tr></thead><tbody><?php foreach ($recentSales as $sale): ?><tr><td data-label="Receipt"><a class="receipt-number" href="<?= site_url('sales/' . $sale['id']) ?>">#<?= esc((string) $sale['id']) ?></a><small><?= esc(date('M j · g:i A', strtotime($sale['created_at']))) ?></small></td><td data-label="Customer"><?= esc($sale['customer_name'] ?: 'Walk-in') ?></td><td data-label="Product"><?= esc($sale['product_name']) ?><small>Qty <?= esc((string) $sale['quantity']) ?></small></td><td data-label="Staff"><?= esc($sale['sold_by_name']) ?></td><td data-label="Total"><strong>₱<?= esc(number_format((float) $sale['total_price'], 2)) ?></strong></td></tr><?php endforeach; ?></tbody></table></div>
                <?php endif; ?>
            </section>

            <section class="panel dashboard-restock">
                <header class="dashboard-panel-heading"><div><span class="dashboard-kicker">INVENTORY CONTROL</span><h2>Restock watch</h2></div><a class="dashboard-text-link" href="<?= site_url('products?stock=low') ?>">All alerts <span aria-hidden="true">↗</span></a></header>
                <?php if ($lowStock === []): ?><div class="dashboard-all-clear"><span aria-hidden="true">✓</span><div><strong>Stock levels look good</strong><p>No active products are at or below their restock threshold.</p></div></div><?php else: ?><div class="dashboard-restock-list"><?php foreach ($lowStock as $product): ?><a class="dashboard-restock-item" href="<?= site_url('products/' . $product['id']) ?>"><span class="dashboard-stock-indicator <?= (int) $product['stock_quantity'] === 0 ? 'is-empty' : '' ?>"></span><span class="dashboard-restock-name"><strong><?= esc($product['name']) ?></strong><small><?= esc($product['sku'] ?: 'No SKU assigned') ?></small></span><span class="dashboard-restock-count"><strong><?= esc((string) $product['stock_quantity']) ?></strong><small>on hand</small></span><span class="dashboard-restock-arrow" aria-hidden="true">↗</span></a><?php endforeach; ?></div><?php endif; ?>
            </section>
        </div>

        <aside class="dashboard-side-column">
            <section class="panel dashboard-quick-panel"><header class="dashboard-panel-heading"><div><span class="dashboard-kicker">SHORTCUTS</span><h2>Quick actions</h2></div></header><nav class="dashboard-action-list" aria-label="Quick actions">
                <a href="<?= site_url('sales/new') ?>"><span class="dashboard-action-icon">＋</span><span><strong>New sale</strong><small>Start a checkout</small></span><b aria-hidden="true">↗</b></a>
                <?php if (in_array(session()->get('role'), ['admin', 'manager', 'inventory'], true)): ?><a href="<?= site_url('products/new') ?>"><span class="dashboard-action-icon">▦</span><span><strong>Add product</strong><small>Update the catalog</small></span><b aria-hidden="true">↗</b></a><?php endif; ?>
                <a href="<?= site_url('customers/new') ?>"><span class="dashboard-action-icon">＋</span><span><strong>Add customer</strong><small>Create a profile</small></span><b aria-hidden="true">↗</b></a>
            </nav></section>

            <section class="panel dashboard-sellers"><header class="dashboard-panel-heading"><div><span class="dashboard-kicker">TODAY'S MIX</span><h2>Top sellers</h2></div></header>
                <?php if ($topProducts === []): ?><div class="dashboard-side-empty"><strong>Waiting for the first sale</strong><p>Top products will appear here throughout the day.</p></div><?php else: ?><ol class="dashboard-seller-list"><?php foreach ($topProducts as $index => $product): ?><li><span class="dashboard-seller-rank"><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><span><strong><?= esc($product['product_name']) ?></strong><small><?= esc((string) $product['units_sold']) ?> units</small></span><b>₱<?= esc(number_format((float) $product['revenue'], 2)) ?></b></li><?php endforeach; ?></ol><?php endif; ?>
                <div class="dashboard-staff-count"><span>Active staff accounts</span><strong><?= esc((string) $staffCount) ?></strong></div>
            </section>
        </aside>
    </div>
</div>
<?= $this->endSection() ?>
