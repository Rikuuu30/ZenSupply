<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="directory-header shell pos-header">
    <div><span class="eyebrow">01 / Inventory control</span><h1><?= $showArchived ? 'Product archive' : 'Product inventory' ?></h1><p><?= $showArchived ? 'Review retired items and restore products when they return to the active catalog.' : 'A clearer view of every product, with quick access to pricing, stock levels, and catalog details.' ?></p></div>
    <div class="directory-actions"><div class="record-count"><span><?= $showArchived ? 'Archived products' : 'Active catalog' ?></span><strong><?= esc((string) $total) ?> products</strong></div><?php if ($canManage && ! $showArchived): ?><a class="button primary" href="<?= site_url('products/new') ?>">Add product <span aria-hidden="true">+</span></a><?php endif; ?></div>
</section>

<section class="shell directory-panel pos-directory">
    <div class="catalog-intro"><div><span class="catalog-kicker">ZEN SUPPLY / STORE CATALOG</span><h2><?= $showArchived ? 'Archived pieces' : 'Find your next essential' ?></h2><p>Martial arts equipment, organized for a faster counter experience.</p></div><span class="catalog-live"><i></i><?= $showArchived ? 'ARCHIVE VIEW' : 'INVENTORY LIVE' ?></span></div>
    <form class="table-toolbar pos-filterbar" method="get" action="<?= site_url('products') ?>">
        <label class="search-field"><span class="sr-only">Search products</span><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= esc($search, 'attr') ?>" placeholder="Search by product, SKU, or description…"></label>
        <label class="filter-select"><span class="sr-only">Stock filter</span><select name="stock"><option value="" <?= $stockFilter === '' ? 'selected' : '' ?>>All stock levels</option><option value="low" <?= $stockFilter === 'low' ? 'selected' : '' ?>>Low stock</option><option value="out" <?= $stockFilter === 'out' ? 'selected' : '' ?>>Out of stock</option></select></label>
        <?php if ($showArchived): ?><input type="hidden" name="archived" value="1"><?php endif; ?>
        <button class="button secondary" type="submit">Apply filters</button>
        <a class="text-link" href="<?= site_url('products' . ($showArchived ? '?archived=1' : '')) ?>">Clear</a>
        <p><?= esc((string) $total) ?> matching products</p>
    </form>
    <div class="product-card-grid">
        <?php foreach ($products as $product): ?>
            <?php
                $quantity = (int) $product['stock_quantity'];
                $threshold = max(1, (int) $product['low_stock_threshold']);
                $low = $quantity <= $threshold;
                $stockClass = ! $product['is_active'] ? 'archived' : ($quantity === 0 ? 'out' : ($low ? 'low' : 'active'));
                $stockLabel = ! $product['is_active'] ? 'Archived' : ($quantity === 0 ? 'Out of stock' : ($low ? 'Low stock' : 'In stock'));
                $stockPercent = min(100, (int) round($quantity / max($threshold * 3, 1) * 100));
                $imageUrl = ! empty($product['image']) ? (str_starts_with((string) $product['image'], 'assets/') ? base_url($product['image']) : base_url('uploads/' . $product['image'])) : base_url('assets/images/product-placeholder.svg');
            ?>
            <article class="catalog-card <?= $stockClass ?>">
                <a class="catalog-photo" href="<?= site_url('products/' . $product['id']) ?>" aria-label="View <?= esc($product['name'], 'attr') ?>">
                    <img src="<?= esc($imageUrl, 'attr') ?>" alt="<?= esc($product['name'], 'attr') ?>" loading="lazy">
                    <span class="catalog-stock <?= $stockClass ?>"><i></i><?= esc($stockLabel) ?></span>
                    <span class="catalog-photo-arrow" aria-hidden="true">↗</span>
                </a>
                <div class="catalog-card-body">
                    <div class="catalog-product-meta"><span><?= esc($product['sku'] ?: 'ZEN / EQUIPMENT') ?></span><span>NO. <?= esc(str_pad((string) $product['id'], 3, '0', STR_PAD_LEFT)) ?></span></div>
                    <h3><a href="<?= site_url('products/' . $product['id']) ?>"><?= esc($product['name']) ?></a></h3>
                    <p class="catalog-description"><?= esc(mb_strimwidth((string) ($product['description'] ?? 'Essential training equipment for the dojo.'), 0, 115, '…')) ?></p>
                    <div class="catalog-price-row"><strong>₱<?= esc(number_format((float) $product['price'], 2)) ?></strong><span><?= esc((string) $quantity) ?> <small>ON HAND</small></span></div>
                    <div class="catalog-stock-meter" role="img" aria-label="<?= esc($stockLabel) ?>, <?= esc((string) $quantity) ?> units on hand"><span style="width:<?= esc((string) $stockPercent) ?>%"></span></div>
                    <div class="catalog-card-actions"><a class="catalog-view" href="<?= site_url('products/' . $product['id']) ?>">View details <span aria-hidden="true">→</span></a><div class="row-actions"><?php if ($canManage && $product['is_active']): ?><a class="table-action" href="<?= site_url('products/' . $product['id'] . '/edit') ?>">Edit</a><?php endif; ?><?php if ($canManage && ! $product['is_active']): ?><form method="post" action="<?= site_url('products/' . $product['id'] . '/restore') ?>"><?= csrf_field() ?><button class="table-action" type="submit">Restore</button></form><?php endif; ?></div></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($products === []): ?><div class="empty-state"><strong><?= $showArchived ? 'No archived products' : 'Your catalog is ready for its first product' ?></strong><p><?= $showArchived ? 'Archived records will appear here and can be restored.' : 'Add products to track stock, record sales, and see inventory totals here.' ?></p><?php if ($canManage && ! $showArchived): ?><a class="button primary" href="<?= site_url('products/new') ?>">Add your first product</a><?php endif; ?></div><?php endif; ?>
    <div class="directory-bottom"><a class="text-link" href="<?= site_url('products' . ($showArchived ? '' : '?archived=1')) ?>"><?= $showArchived ? '← Back to active catalog' : 'View archived products' ?></a><?= $pager->links() ?></div>
</section>
<?= $this->endSection() ?>
