<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/utility.php';
$pageTitle = 'Product Details';
products_ensure_columns($pdo);

if (isset($_GET['toggle'])) {
    utility_toggle_status($pdo, 'products', (int) $_GET['toggle']);
    $redir = 'product-details.php';
    if (!empty($_GET['q'])) $redir .= '?q=' . urlencode($_GET['q']);
    header('Location: ' . $redir);
    exit;
}
if (isset($_GET['delete'])) {
    utility_delete($pdo, 'products', (int) $_GET['delete']);
    header('Location: product-details.php');
    exit;
}

$q = trim($_GET['q'] ?? '');
$params = [];
$where = '1=1';
if ($q !== '') {
    $where = '(p.name LIKE ? OR p.sku LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like];
}

$stmt = $pdo->prepare("
    SELECT p.*,
           c.name AS category_name,
           sc.name AS subcategory_name,
           sz.name AS size_name,
           cl.name AS color_name
    FROM products p
    LEFT JOIN product_categories c ON c.id = p.category_id
    LEFT JOIN product_subcategories sc ON sc.id = p.subcategory_id
    LEFT JOIN product_sizes sz ON sz.id = p.size_id
    LEFT JOIN product_colors cl ON cl.id = p.color_id
    WHERE $where
    ORDER BY p.id DESC
");
$stmt->execute($params);
$rows = $stmt->fetchAll();

// Calculate quick stats
$totalCount = count($rows);
$activeCount = 0;
$inactiveCount = 0;
$lowStockCount = 0;
foreach ($rows as $r) {
    if (($r['status'] ?? 'active') === 'active') {
        $activeCount++;
    } else {
        $inactiveCount++;
    }
    if ((int)($r['stock_qty'] ?? 0) <= 10) {
        $lowStockCount++;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <!-- Top Header & Search Bar -->
    <div class="panel-header prod-header-wrap">
        <div class="prod-title-group">
            <h2>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="prod-title-icon"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                Product Inventory
            </h2>
            <div class="prod-stats-strip">
                <span class="prod-stat-pill">Total: <strong><?= $totalCount ?></strong></span>
                <span class="prod-stat-pill pill-active">Active: <strong><?= $activeCount ?></strong></span>
                <?php if ($inactiveCount > 0): ?>
                    <span class="prod-stat-pill pill-inactive">Inactive: <strong><?= $inactiveCount ?></strong></span>
                <?php endif; ?>
                <?php if ($lowStockCount > 0): ?>
                    <span class="prod-stat-pill pill-lowstock">Low Stock (≤10): <strong><?= $lowStockCount ?></strong></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="prod-actions-bar">
            <form method="get" class="prod-search-form">
                <div class="prod-search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="q" placeholder="Search product name or SKU..." value="<?= e($q) ?>">
                </div>
                <button type="submit" class="btn btn-primary prod-btn-search">Search</button>
                <?php if ($q !== ''): ?>
                    <a href="product-details.php" class="btn btn-outline prod-btn-reset">Reset</a>
                <?php endif; ?>
            </form>
            <a href="product-form.php" class="btn-add-product">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Product
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="table-wrap">
        <table class="data prod-data-table">
            <thead>
                <tr>
                    <th class="th-prod-info">Product Details</th>
                    <th>Category</th>
                    <th>Price &amp; MRP</th>
                    <th>PV / BV</th>
                    <th>Stock Qty</th>
                    <th class="th-prod-status">Status</th>
                    <th class="th-prod-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="7" class="prod-empty-cell">
                        <div class="prod-empty-icon">📦</div>
                        <h4 class="prod-empty-title">No products found</h4>
                        <p class="prod-empty-desc">Try adjusting your search criteria or add your first product.</p>
                        <a href="product-form.php" class="btn-add-product prod-empty-btn">Add Product</a>
                    </td>
                </tr>
            <?php else: foreach ($rows as $r): ?>
                <?php
                $thumbUrl = !empty($r['thumbnail']) ? '../' . ltrim($r['thumbnail'], '/') : '';
                $stock = (int)($r['stock_qty'] ?? 0);
                $isActive = (($r['status'] ?? 'active') === 'active');
                $toggleHref = '?toggle=' . (int)$r['id'] . ($q !== '' ? '&q=' . urlencode($q) : '');
                ?>
                <tr>
                    <!-- Product Details & Thumbnail -->
                    <td>
                        <div class="prod-cell">
                            <div class="prod-img-wrap">
                                <?php if ($thumbUrl): ?>
                                    <img src="<?= e($thumbUrl) ?>" alt="<?= e($r['name']) ?>" onerror="this.onerror=null;this.parentElement.innerHTML='<span class=\'prod-img-fallback\'>🌿</span>';">
                                <?php else: ?>
                                    <span class="prod-img-fallback">🌿</span>
                                <?php endif; ?>
                            </div>
                            <div class="prod-info-block">
                                <a href="product-form.php?edit=<?= (int)$r['id'] ?>" class="prod-name" title="Click to edit product">
                                    <?= e($r['name']) ?>
                                </a>
                                <div class="prod-meta-tags">
                                    <span class="prod-sku-tag">SKU: <?= e($r['sku'] ?: 'EH-' . $r['id']) ?></span>
                                    <?php if (!empty($r['size_name'])): ?>
                                        <span class="prod-variant-tag"><?= e($r['size_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($r['color_name'])): ?>
                                        <span class="prod-variant-tag"><?= e($r['color_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </td>

                    <!-- Category -->
                    <td>
                        <span class="cat-badge">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                            <?= e($r['category_name'] ?: 'General Health') ?>
                        </span>
                    </td>

                    <!-- Price & MRP -->
                    <td>
                        <div class="prod-price-cell">
                            <span class="prod-price-val"><?= currency((float)$r['price']) ?></span>
                            <?php if (!empty($r['mrp']) && (float)$r['mrp'] > (float)$r['price']): ?>
                                <span class="prod-mrp-val">MRP <?= currency((float)$r['mrp']) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>

                    <!-- PV / BV -->
                    <td>
                        <strong class="prod-bv-val"><?= number_format((float) ($r['bv'] ?? 0), 2) ?></strong>
                        <small class="prod-bv-lbl">BV Points</small>
                    </td>

                    <!-- Stock Status -->
                    <td>
                        <?php if ($stock <= 0): ?>
                            <span class="stock-pill out-stock">
                                <span class="stock-dot"></span> Out of Stock
                            </span>
                        <?php elseif ($stock <= 10): ?>
                            <span class="stock-pill low-stock" title="Low Inventory">
                                <span class="stock-dot"></span> <?= $stock ?> Low Stock
                            </span>
                        <?php else: ?>
                            <span class="stock-pill in-stock">
                                <span class="stock-dot"></span> <?= $stock ?> in stock
                            </span>
                        <?php endif; ?>
                    </td>

                    <!-- Status Toggle (Interactive Pill) -->
                    <td class="td-center">
                        <a href="<?= e($toggleHref) ?>" class="status-pill-toggle <?= $isActive ? 'is-active' : 'is-inactive' ?>" title="<?= $isActive ? 'Status: Active — Click to de-activate' : 'Status: Inactive — Click to activate' ?>">
                            <span class="pulse-circle"></span>
                            <?= $isActive ? 'Active' : 'Inactive' ?>
                        </a>
                    </td>

                    <!-- Actions (Edit, Toggle Status, Delete) -->
                    <td class="td-center">
                        <div class="prod-actions-group">
                            <!-- 1. Edit Button -->
                            <a href="product-form.php?edit=<?= (int)$r['id'] ?>" class="btn-act-btn btn-act-edit" title="Edit this product" aria-label="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </a>

                            <!-- 2. Active/Inactive Toggle Button -->
                            <a href="<?= e($toggleHref) ?>" class="btn-act-btn btn-act-status <?= $isActive ? 'status-active' : 'status-inactive' ?>" title="<?= $isActive ? 'Click to deactivate' : 'Click to activate' ?>" aria-label="<?= $isActive ? 'Deactivate' : 'Activate' ?>">
                                <?php if ($isActive): ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="5" width="22" height="14" rx="7" fill="currentColor" fill-opacity="0.2"/>
                                        <circle cx="16" cy="12" r="3" fill="currentColor"/>
                                    </svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="5" width="22" height="14" rx="7"/>
                                        <circle cx="8" cy="12" r="3" fill="currentColor"/>
                                    </svg>
                                <?php endif; ?>
                            </a>

                            <!-- 3. Delete Button -->
                            <a href="?delete=<?= (int)$r['id'] ?>" class="btn-act-btn btn-act-delete" onclick="return confirm('Are you sure you want to permanently delete \'<?= addslashes(e($r['name'])) ?>\'?');" data-confirm="Are you sure you want to delete this product?" title="Delete this product" aria-label="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
