<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/report_helpers.php';
require_once __DIR__ . '/../includes/franchise.php';
$pageTitle = 'Top Earners (Franchise)';

franchise_ensure_tables($pdo);

[$from, $to] = report_parse_dates();
$q = trim((string) ($_GET['q'] ?? ''));
$typeId = (int) ($_GET['type_id'] ?? 0);
$status = trim((string) ($_GET['status'] ?? ''));
$minEarn = trim((string) ($_GET['min_earn'] ?? ''));
$minEarnVal = is_numeric($minEarn) ? (float) $minEarn : null;

// Franchisee types for dropdown
$types = [];
try {
    $types = $pdo->query('SELECT id, name, code, commission_percent FROM franchisee_types WHERE status = "active" ORDER BY hierarchy_level ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

$where = ['1=1'];
$params = [$from, $to, $from, $to]; // for period commissions and period sales subqueries

if ($typeId > 0) {
    $where[] = 'f.type_id = ?';
    $params[] = $typeId;
}
if ($status !== '' && in_array($status, ['active', 'inactive'], true)) {
    $where[] = 'f.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where[] = '(f.franchisee_code LIKE ? OR f.name LIKE ? OR f.contact_person LIKE ? OR f.city LIKE ? OR f.phone LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
}

$having = [];
if ($minEarnVal !== null) {
    $having[] = '(total_commission >= ' . (float) $minEarnVal . ' OR period_commission >= ' . (float) $minEarnVal . ')';
}
$havingSql = $having ? ('HAVING ' . implode(' AND ', $having)) : '';

$sql = "
    SELECT f.id, f.franchisee_code, f.name, f.contact_person, f.phone, f.city, f.state,
           f.wallet_balance, f.status, f.type_id,
           t.name AS type_name, t.code AS type_code, t.commission_percent,
           COALESCE(pc.period_commission, 0) AS period_commission,
           COALESCE(tc.total_commission, 0) AS total_commission,
           COALESCE(ps.period_sales, 0) AS period_sales,
           COALESCE(ts.total_sales, 0) AS total_sales
    FROM franchisees f
    LEFT JOIN franchisee_types t ON t.id = f.type_id
    LEFT JOIN (
        SELECT franchisee_id, COALESCE(SUM(commission_amount), 0) AS period_commission
        FROM franchisee_commissions
        WHERE DATE(created_at) BETWEEN ? AND ?
        GROUP BY franchisee_id
    ) pc ON pc.franchisee_id = f.id
    LEFT JOIN (
        SELECT franchisee_id, COALESCE(SUM(commission_amount), 0) AS total_commission
        FROM franchisee_commissions
        GROUP BY franchisee_id
    ) tc ON tc.franchisee_id = f.id
    LEFT JOIN (
        SELECT franchisee_id, COALESCE(SUM(total_amount), 0) AS period_sales
        FROM franchisee_purchases
        WHERE status != 'cancelled' AND purchase_date BETWEEN ? AND ?
        GROUP BY franchisee_id
    ) ps ON ps.franchisee_id = f.id
    LEFT JOIN (
        SELECT franchisee_id, COALESCE(SUM(total_amount), 0) AS total_sales
        FROM franchisee_purchases
        WHERE status != 'cancelled'
        GROUP BY franchisee_id
    ) ts ON ts.franchisee_id = f.id
    WHERE " . implode(' AND ', $where) . "
    $havingSql
    ORDER BY period_commission DESC, total_commission DESC, period_sales DESC, total_sales DESC, f.wallet_balance DESC
    LIMIT 200
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary Stats for the top of report
$totalPeriodComm = 0.0;
$totalPeriodSales = 0.0;
$topEarnerName = '—';
if ($rows) {
    $topEarnerName = $rows[0]['name'] . ' (' . $rows[0]['franchisee_code'] . ')';
    foreach ($rows as $r) {
        $totalPeriodComm += (float) $r['period_commission'];
        $totalPeriodSales += (float) $r['period_sales'];
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel tpin-panel">
    <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2>Top Earning Franchisees</h2>
            <p class="tpin-panel-sub">Performance leaderboard tracking franchise margins, sales volume &amp; commission earnings</p>
        </div>
        <div style="font-size:0.85rem;color:var(--ink-muted)">
            Period: <strong><?= e(report_period_label($from, $to)) ?></strong>
        </div>
    </div>

    <div class="panel-body">
        <?php
        $typeOpts = ['' => 'All Franchise Types'];
        foreach ($types as $t) {
            $typeOpts[(string) $t['id']] = $t['name'] . ($t['commission_percent'] > 0 ? " ({$t['commission_percent']}%)" : '');
        }

        report_filter_form('report-top-earners.php', $from, $to, [
            ['name' => 'q', 'label' => 'Search', 'type' => 'text', 'value' => $q, 'placeholder' => 'Code, center name, phone, city', 'wide' => true],
            ['name' => 'type_id', 'label' => 'Franchise Type', 'type' => 'select', 'value' => $typeId > 0 ? (string) $typeId : '', 'options' => $typeOpts],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'value' => $status, 'options' => [
                '' => 'All status', 'active' => 'Active', 'inactive' => 'Inactive',
            ]],
            ['name' => 'min_earn', 'label' => 'Min Margin (₹)', 'type' => 'number', 'value' => $minEarn, 'placeholder' => '0'],
        ]);
        ?>

        <!-- Top KPIs for the Selected Period -->
        <div class="stats-grid tpin-stats" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;margin-top:1rem">
            <div class="stat-card g-green">
                <div class="label">Period Margin / Comm.</div>
                <div class="value"><?= currency($totalPeriodComm) ?></div>
                <small style="color:rgba(255,255,255,0.85);font-size:0.75rem">Earned in selected dates</small>
            </div>
            <div class="stat-card g-blue">
                <div class="label">Period Stock Billed</div>
                <div class="value"><?= currency($totalPeriodSales) ?></div>
                <small style="color:rgba(255,255,255,0.85);font-size:0.75rem">Stock purchases in period</small>
            </div>
            <div class="stat-card g-mint">
                <div class="label">#1 Top Performer</div>
                <div class="value" style="font-size:1.15rem;text-overflow:ellipsis;overflow:hidden;white-space:nowrap" title="<?= e($topEarnerName) ?>">
                    <?= e($topEarnerName) ?>
                </div>
                <small style="color:rgba(255,255,255,0.85);font-size:0.75rem">Highest margin earner</small>
            </div>
            <div class="stat-card accent">
                <div class="label">Centers Ranked</div>
                <div class="value"><?= count($rows) ?></div>
                <small style="color:rgba(255,255,255,0.85);font-size:0.75rem">Franchisees in leaderboard</small>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table class="data tpin-table">
            <thead>
            <tr>
                <th style="width:48px">Rank</th>
                <th>Franchise Center</th>
                <th>Type / Tier</th>
                <th>Location</th>
                <th>Period Margin</th>
                <th>Total Margin</th>
                <th>Period Sales</th>
                <th>Total Sales</th>
                <th>Wallet Balance</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr>
                    <td colspan="11" style="text-align:center;padding:2.5rem;color:var(--ink-muted)">
                        No franchise centers found matching criteria. <a href="franchisee-add.php">Add a franchisee</a> or adjust filters.
                    </td>
                </tr>
            <?php else: foreach ($rows as $i => $e): 
                $rank = $i + 1;
                $rankBadge = '';
                if ($rank === 1) {
                    $rankBadge = '<span style="background:#f59e0b;color:#fff;border-radius:50%;width:26px;height:26px;display:inline-grid;place-items:center;font-weight:800;font-size:0.8rem">1</span>';
                } elseif ($rank === 2) {
                    $rankBadge = '<span style="background:#94a3b8;color:#fff;border-radius:50%;width:26px;height:26px;display:inline-grid;place-items:center;font-weight:800;font-size:0.8rem">2</span>';
                } elseif ($rank === 3) {
                    $rankBadge = '<span style="background:#d97706;color:#fff;border-radius:50%;width:26px;height:26px;display:inline-grid;place-items:center;font-weight:800;font-size:0.8rem">3</span>';
                } else {
                    $rankBadge = '<span style="color:var(--ink-muted);font-weight:700">#' . $rank . '</span>';
                }
            ?>
                <tr>
                    <td style="text-align:center"><?= $rankBadge ?></td>
                    <td>
                        <div class="member-cell">
                            <strong><a href="direct-franchise-login.php?id=<?= (int) $e['id'] ?>" target="_blank" rel="noopener" title="Direct Login"><?= e($e['name']) ?></a></strong>
                            <span style="color:var(--brand);font-weight:700"><?= e($e['franchisee_code']) ?></span>
                            <?php if (!empty($e['contact_person'])): ?>
                            <small class="muted">CP: <?= e($e['contact_person']) ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background:#e0f2fe;color:#0284c7;font-weight:700;font-size:0.75rem">
                            <?= e($e['type_name'] ?? 'Franchise') ?>
                            <?php if ((float) ($e['commission_percent'] ?? 0) > 0): ?>
                            (<?= (float) $e['commission_percent'] ?>%)
                            <?php endif; ?>
                        </span>
                    </td>
                    <td>
                        <?= e($e['city'] ? $e['city'] . ($e['state'] ? ', ' . $e['state'] : '') : '—') ?>
                    </td>
                    <td>
                        <strong style="color:#059669;font-size:0.95rem"><?= currency((float) ($e['period_commission'] ?? 0)) ?></strong>
                    </td>
                    <td>
                        <strong style="color:#0f172a"><?= currency((float) ($e['total_commission'] ?? 0)) ?></strong>
                    </td>
                    <td>
                        <span><?= currency((float) ($e['period_sales'] ?? 0)) ?></span>
                    </td>
                    <td>
                        <span><?= currency((float) ($e['total_sales'] ?? 0)) ?></span>
                    </td>
                    <td>
                        <strong style="color:var(--brand)"><?= currency((float) $e['wallet_balance']) ?></strong>
                    </td>
                    <td><?= status_badge((string) $e['status']) ?></td>
                    <td>
                        <div style="display:flex;gap:0.35rem">
                            <a href="direct-franchise-login.php?id=<?= (int) $e['id'] ?>" target="_blank" rel="noopener" class="btn btn-accent btn-sm" title="Login as Franchise in new tab">
                                Login →
                            </a>
                            <a href="wallets.php?tab=franchise&q=<?= urlencode((string) $e['franchisee_code']) ?>" class="btn btn-outline btn-sm" title="Adjust Wallet">
                                Wallet
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
