<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/franchise.php';
$pageTitle = 'Dashboard';

franchise_ensure_tables($pdo);

// Franchise Stats
$totalFranchisees = 0;
$activeFranchisees = 0;
$totalFranchiseSales = 0.0;
$todayFranchiseSales = 0.0;
$totalFranchiseWallet = 0.0;
$totalFranchiseStock = 0;
$totalProducts = 0;
$totalWarehouseStock = 0;
$alertLowStock = 0;
$stockThreshold = 5;

try {
    $totalFranchisees = (int) $pdo->query("SELECT COUNT(*) FROM franchisees")->fetchColumn();
    $activeFranchisees = (int) $pdo->query("SELECT COUNT(*) FROM franchisees WHERE status = 'active'")->fetchColumn();
    $totalFranchiseWallet = (float) $pdo->query("SELECT COALESCE(SUM(wallet_balance), 0) FROM franchisees")->fetchColumn();
    $totalFranchiseSales = (float) $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM franchisee_purchases WHERE status != 'cancelled'")->fetchColumn();
    $todayFranchiseSales = (float) $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM franchisee_purchases WHERE purchase_date = CURDATE() AND status != 'cancelled'")->fetchColumn();
} catch (Throwable $e) {}

try {
    $totalProducts = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    $totalWarehouseStock = (int) $pdo->query("SELECT COALESCE(SUM(stock_qty), 0) FROM products WHERE status = 'active'")->fetchColumn();
    $alertLowStock = (int) $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock_qty <= {$stockThreshold}")->fetchColumn();
    $totalFranchiseStock = (int) $pdo->query("SELECT COALESCE(SUM(qty), 0) FROM franchisee_stock")->fetchColumn();
} catch (Throwable $e) {}

// Tier-wise breakdown (BHEO, Super Distributor, Distributor, Retailer)
$tierStats = [];
try {
    $tierStats = $pdo->query("
        SELECT t.id, t.name, t.code, t.commission_percent, COUNT(f.id) AS total_count
        FROM franchisee_types t
        LEFT JOIN franchisees f ON f.type_id = t.id
        WHERE t.status = 'active'
        GROUP BY t.id, t.name, t.code, t.commission_percent
        ORDER BY t.hierarchy_level ASC, t.id ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

// Recent Franchise Invoices / Purchases
$recentPurchases = [];
try {
    $recentPurchases = $pdo->query("
        SELECT p.*, f.name AS franchisee_name, f.franchisee_code, t.name AS type_name
        FROM franchisee_purchases p
        JOIN franchisees f ON f.id = p.franchisee_id
        LEFT JOIN franchisee_types t ON t.id = f.type_id
        ORDER BY p.purchase_date DESC, p.id DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

// Recent Franchisees
$recentFranchisees = [];
try {
    $recentFranchisees = $pdo->query("
        SELECT f.*, t.name AS type_name, t.code AS type_code
        FROM franchisees f
        LEFT JOIN franchisee_types t ON t.id = f.type_id
        ORDER BY f.id DESC
        LIMIT 8
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

// Alerts
$dashAlerts = [];
if ($alertLowStock > 0) {
    $dashAlerts[] = [
        'tone' => 'stock',
        'label' => 'Low Stock Warning',
        'count' => $alertLowStock,
        'href' => 'stock-report.php',
        'hint' => 'Products with stock ≤ ' . $stockThreshold,
    ];
}

$pendingPurchasesCount = 0;
try {
    $pendingPurchasesCount = (int) $pdo->query("SELECT COUNT(*) FROM franchisee_purchases WHERE status = 'pending'")->fetchColumn();
} catch (Throwable $e) {}

if ($pendingPurchasesCount > 0) {
    $dashAlerts[] = [
        'tone' => 'warn',
        'label' => 'Pending Invoices',
        'count' => $pendingPurchasesCount,
        'href' => 'franchisee-purchase-report.php',
        'hint' => 'Orders awaiting completion',
    ];
}

$iconUsers = '<svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>';
$iconCheck = '<svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
$iconCalendar = '<svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
$iconMoney = '<svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>';
$iconWallet = '<svg viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>';
$iconPackage = '<svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>';
$iconStore = '<svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>';
$iconBox = '<svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>';

require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($dashAlerts): ?>
<div class="dash-alerts">
    <div class="dash-alerts-head">
        <strong>Needs attention</strong>
        <span class="muted"><?= count($dashAlerts) ?> alert<?= count($dashAlerts) === 1 ? '' : 's' ?></span>
    </div>
    <div class="dash-alerts-grid">
        <?php foreach ($dashAlerts as $al): ?>
        <a class="dash-alert tone-<?= e($al['tone']) ?>" href="<?= e($al['href']) ?>">
            <span class="dash-alert-count"><?= (int) $al['count'] ?></span>
            <span class="dash-alert-copy">
                <strong><?= e($al['label']) ?></strong>
                <small><?= e($al['hint']) ?></small>
            </span>
            <span class="dash-alert-go" aria-hidden="true">→</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Quick Action Shortcuts -->
<div style="display:flex;flex-wrap:wrap;gap:0.75rem;margin-bottom:1.25rem;align-items:center;">
    <a href="franchisee-add.php" class="btn btn-primary btn-sm" style="display:inline-flex;align-items:center;gap:0.35rem">
        <span>+ Add Franchisee</span>
    </a>
    <a href="franchisee-purchase.php" class="btn btn-accent btn-sm" style="display:inline-flex;align-items:center;gap:0.35rem">
        <span>+ New Stock Billing</span>
    </a>
    <a href="franchisee-stock.php" class="btn btn-outline btn-sm">Franchise Stock</a>
    <a href="stock-report.php" class="btn btn-outline btn-sm">Warehouse Stock</a>
    <a href="wallets.php" class="btn btn-outline btn-sm">Franchise Wallets</a>
    <a href="direct-franchise-login.php" class="btn btn-outline btn-sm">Direct Franchise Login →</a>
</div>

<!-- Primary Stats Grid -->
<div class="stats-grid">
    <div class="stat-card g-blue">
        <div class="bg-icon"><?= $iconUsers ?></div>
        <div class="value"><?= $totalFranchisees ?></div>
        <div class="label">Total Franchisees</div>
        <a class="more" href="franchisee-report.php">View all →</a>
    </div>
    <div class="stat-card g-cyan">
        <div class="bg-icon"><?= $iconCheck ?></div>
        <div class="value"><?= $activeFranchisees ?></div>
        <div class="label">Active Centers</div>
        <a class="more" href="franchisee-report.php?status=active">Active list →</a>
    </div>
    <div class="stat-card g-green">
        <div class="bg-icon"><?= $iconMoney ?></div>
        <div class="value"><?= currency($totalFranchiseSales) ?></div>
        <div class="label">Total Franchise Billing</div>
        <a class="more" href="franchisee-purchase-report.php">Billing report →</a>
    </div>
    <div class="stat-card g-mint">
        <div class="bg-icon"><?= $iconCalendar ?></div>
        <div class="value"><?= currency($todayFranchiseSales) ?></div>
        <div class="label">Today's Billing</div>
        <a class="more" href="franchisee-purchase-report.php">Invoices →</a>
    </div>
    <div class="stat-card g-purple">
        <div class="bg-icon"><?= $iconPackage ?></div>
        <div class="value"><?= $totalProducts ?></div>
        <div class="label">Active Products</div>
        <a class="more" href="product-details.php">Catalog →</a>
    </div>
    <div class="stat-card g-orange">
        <div class="bg-icon"><?= $iconBox ?></div>
        <div class="value"><?= number_format($totalWarehouseStock) ?></div>
        <div class="label">Warehouse Inventory</div>
        <a class="more" href="stock-report.php">Stock details →</a>
    </div>
    <div class="stat-card g-red">
        <div class="bg-icon"><?= $iconStore ?></div>
        <div class="value"><?= number_format($totalFranchiseStock) ?></div>
        <div class="label">Franchise Stock Held</div>
        <a class="more" href="franchisee-stock.php">Inspect stock →</a>
    </div>
    <div class="stat-card g-pink">
        <div class="bg-icon"><?= $iconWallet ?></div>
        <div class="value"><?= currency($totalFranchiseWallet) ?></div>
        <div class="label">Franchise Wallets</div>
        <a class="more" href="wallets.php">Manage wallets →</a>
    </div>
</div>

<!-- Franchise Hierarchy / Tier Distribution -->
<?php if ($tierStats): ?>
<div class="panel" style="margin-bottom:1.5rem">
    <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2>Franchise Tier Hierarchy</h2>
            <p class="members-sub" style="margin:0.2rem 0 0;color:var(--ink-muted);font-size:0.85rem">Distribution centers categorized by tier &amp; margin structure</p>
        </div>
        <a href="franchisee-types.php" class="btn btn-outline btn-sm">Configure Tiers</a>
    </div>
    <div class="panel-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
            <?php 
            $tierTones = ['theme-blue', 'theme-green', 'theme-orange', 'theme-red'];
            $idx = 0;
            foreach ($tierStats as $t): 
                $theme = $tierTones[$idx % count($tierTones)];
                $idx++;
            ?>
            <div class="summary-card <?= $theme ?>" style="margin-bottom:0">
                <div class="top">
                    <span class="summary-icon <?= str_replace('theme-', '', $theme) ?>"><?= $iconStore ?></span>
                    <span class="chip <?= str_replace('theme-', '', $theme) ?>"><?= (float) $t['commission_percent'] ?>% Margin</span>
                </div>
                <h3><?= e($t['name']) ?></h3>
                <div class="summary-stats">
                    <div><span>Count</span><strong><?= (int) $t['total_count'] ?></strong></div>
                    <div><span>Code</span><strong><?= e($t['code'] ?: '—') ?></strong></div>
                </div>
                <div class="summary-foot">
                    <a href="franchisee-report.php?type=<?= (int) $t['id'] ?>">View <?= e($t['name']) ?>s →</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Activity Tables: Purchases and Franchisees -->
<div class="dash-bottom" style="display:grid;grid-template-columns:1.2fr 0.8fr;gap:1.5rem;">
    <!-- Recent Franchise Purchases / Invoices -->
    <div class="panel" style="margin-bottom:0">
        <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2>Recent Stock Invoices</h2>
                <p class="members-sub" style="margin:0.2rem 0 0;color:var(--ink-muted);font-size:0.85rem">Latest stock purchases billed to franchisees</p>
            </div>
            <a href="franchisee-purchase-report.php" class="btn btn-outline btn-sm">All Invoices</a>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Franchisee</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$recentPurchases): ?>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--ink-muted)">No stock purchases yet. <a href="franchisee-purchase.php">Create first billing</a></td></tr>
                <?php else: foreach ($recentPurchases as $p): ?>
                    <tr>
                        <td>
                            <strong><a href="franchisee-purchase-report.php?q=<?= urlencode((string) $p['invoice_no']) ?>"><?= e($p['invoice_no'] ?: '#' . $p['id']) ?></a></strong>
                        </td>
                        <td>
                            <div style="line-height:1.2">
                                <strong><?= e($p['franchisee_name']) ?></strong>
                                <small style="display:block;color:var(--ink-muted)"><?= e($p['franchisee_code']) ?> · <?= e($p['type_name'] ?? 'Franchise') ?></small>
                            </div>
                        </td>
                        <td><strong><?= currency((float) $p['total_amount']) ?></strong></td>
                        <td><small><?= e(date('d M Y', strtotime((string) $p['purchase_date']))) ?></small></td>
                        <td><?= status_badge($p['status']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Newly Added Franchisees -->
    <div class="panel" style="margin-bottom:0">
        <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h2>New Franchisees</h2>
                <p class="members-sub" style="margin:0.2rem 0 0;color:var(--ink-muted);font-size:0.85rem">Recently registered centers</p>
            </div>
            <a href="franchisee-report.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr>
                        <th>Center</th>
                        <th>Type</th>
                        <th>Wallet</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$recentFranchisees): ?>
                    <tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--ink-muted)">No franchisees yet. <a href="franchisee-add.php">Add franchisee</a></td></tr>
                <?php else: foreach ($recentFranchisees as $f): ?>
                    <tr>
                        <td>
                            <div style="line-height:1.2">
                                <strong><?= e($f['name']) ?></strong>
                                <small style="display:block;color:var(--ink-muted)"><?= e($f['franchisee_code']) ?> · <?= e($f['city'] ?? $f['phone']) ?></small>
                            </div>
                        </td>
                        <td><span class="badge" style="background:#e0f2fe;color:#0284c7;font-weight:600;font-size:0.75rem"><?= e($f['type_name'] ?? 'Franchise') ?></span></td>
                        <td><strong><?= currency((float) $f['wallet_balance']) ?></strong></td>
                        <td><?= status_badge($f['status']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
