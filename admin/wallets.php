<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/wallet.php';
require_once __DIR__ . '/../includes/franchise.php';

franchise_ensure_tables($pdo);

// Ensure franchisee_wallet_transactions table exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS franchisee_wallet_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            franchisee_id INT NOT NULL,
            direction ENUM('credit','debit') NOT NULL,
            amount DECIMAL(12,2) NOT NULL,
            balance_after DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            action_type VARCHAR(60) NOT NULL DEFAULT 'admin_adjustment',
            reference_id INT NULL,
            note VARCHAR(255) NULL,
            created_by_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY idx_fwt_franchisee (franchisee_id),
            KEY idx_fwt_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
} catch (Throwable $e) {}

$tab = trim((string) ($_GET['tab'] ?? 'franchise'));
if (!in_array($tab, ['franchise', 'member'], true)) {
    $tab = 'franchise';
}

$pageTitle = $tab === 'franchise' ? 'Franchise Wallets' : 'Member Wallets';
$errors = [];
$q = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Handle POST: Franchise Wallet Adjustment
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($tab === 'franchise' || ($_POST['action_target'] ?? '') === 'franchise')) {
    $franchiseeId = (int) ($_POST['franchisee_id'] ?? 0);
    $direction = (string) ($_POST['direction'] ?? 'credit');
    $amount = (float) ($_POST['amount'] ?? 0);
    $note = trim((string) ($_POST['note'] ?? ''));

    if ($franchiseeId <= 0) {
        $errors[] = 'Please select a valid Franchisee.';
    } elseif ($amount <= 0) {
        $errors[] = 'Enter a valid amount greater than 0.';
    } else {
        $stmtF = $pdo->prepare("SELECT id, name, franchisee_code, wallet_balance FROM franchisees WHERE id = ?");
        $stmtF->execute([$franchiseeId]);
        $fr = $stmtF->fetch();

        if (!$fr) {
            $errors[] = 'Franchisee not found.';
        } elseif ($direction === 'debit' && (float) $fr['wallet_balance'] < $amount) {
            $errors[] = 'Insufficient balance. Current balance is ' . strip_tags(currency((float) $fr['wallet_balance']));
        } else {
            $adminId = (int) ($_SESSION['admin_id'] ?? 0);
            $newBal = $direction === 'credit' ? ((float) $fr['wallet_balance'] + $amount) : ((float) $fr['wallet_balance'] - $amount);

            $pdo->beginTransaction();
            try {
                $upStmt = $pdo->prepare("UPDATE franchisees SET wallet_balance = ? WHERE id = ?");
                $upStmt->execute([$newBal, $franchiseeId]);

                $txStmt = $pdo->prepare("
                    INSERT INTO franchisee_wallet_transactions (franchisee_id, direction, amount, balance_after, action_type, note, created_by_id)
                    VALUES (?, ?, ?, ?, 'admin_adjustment', ?, ?)
                ");
                $txStmt->execute([$franchiseeId, $direction, $amount, $newBal, $note !== '' ? $note : 'Admin wallet adjustment', $adminId]);

                $pdo->commit();

                log_activity('franchise_wallet_' . $direction, ucfirst($direction) . " {$amount} for franchise {$fr['franchisee_code']} ({$fr['name']})");
                flash('success', 'Franchise Wallet ' . ($direction === 'credit' ? 'credited' : 'debited') . ' successfully. New balance: ' . strip_tags(currency($newBal)));
                header('Location: wallets.php?tab=franchise&q=' . urlencode($q));
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to update wallet: ' . $e->getMessage();
            }
        }
    }
}

// Handle POST: Member Wallet Adjustment
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $tab === 'member' && (($_POST['action_target'] ?? '') !== 'franchise')) {
    $memberId = (int) ($_POST['member_id'] ?? 0);
    $walletType = (string) ($_POST['wallet_type'] ?? 'income');
    $direction = (string) ($_POST['direction'] ?? 'credit');
    $amount = (float) ($_POST['amount'] ?? 0);
    $note = trim((string) ($_POST['note'] ?? ''));

    if ($memberId <= 0) {
        $errors[] = 'Select a valid member.';
    } elseif (!isset(wallet_types()[$walletType])) {
        $errors[] = 'Invalid wallet type.';
    } elseif ($amount <= 0) {
        $errors[] = 'Enter a valid amount.';
    } else {
        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        if ($direction === 'debit') {
            $res = wallet_debit($pdo, $memberId, $walletType, $amount, 'admin_debit', null, $note !== '' ? $note : 'Admin debit', $adminId);
        } else {
            $res = wallet_credit($pdo, $memberId, $walletType, $amount, 'admin_credit', null, $note !== '' ? $note : 'Admin credit', $adminId);
        }
        if ($res['ok']) {
            log_activity('wallet_' . $direction, ucfirst($direction) . " {$walletType} wallet #{$memberId} amount {$amount}");
            flash('success', wallet_label($walletType) . ' ' . $direction . 'd successfully. New balance: ' . strip_tags(currency($res['balance'])) . '.');
            header('Location: wallets.php?tab=member&q=' . urlencode($q));
            exit;
        }
        $errors[] = $res['error'] ?? 'Wallet update failed.';
    }
}

// Data Queries
if ($tab === 'franchise') {
    // Franchise Stats
    $sumFranchiseWallet = (float) $pdo->query('SELECT COALESCE(SUM(wallet_balance),0) FROM franchisees')->fetchColumn();
    $totalFranchisees = (int) $pdo->query('SELECT COUNT(*) FROM franchisees')->fetchColumn();
    $activeFranchisees = (int) $pdo->query("SELECT COUNT(*) FROM franchisees WHERE status = 'active'")->fetchColumn();

    $where = ['1=1'];
    $params = [];
    if ($q !== '') {
        $where[] = '(f.franchisee_code LIKE ? OR f.name LIKE ? OR f.contact_person LIKE ? OR f.phone LIKE ? OR f.city LIKE ?)';
        $like = '%' . $q . '%';
        $params = [$like, $like, $like, $like, $like];
    }
    $whereSql = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM franchisees f WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = $pdo->prepare("
        SELECT f.id, f.franchisee_code, f.name, f.contact_person, f.phone, f.city, f.state, f.status, f.wallet_balance,
               t.name AS type_name, t.code AS type_code
        FROM franchisees f
        LEFT JOIN franchisee_types t ON t.id = f.type_id
        WHERE $whereSql
        ORDER BY f.id DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    // Dropdown list for adjust form
    $allFranchisees = $pdo->query("
        SELECT f.id, f.franchisee_code, f.name, f.wallet_balance, t.name AS type_name
        FROM franchisees f
        LEFT JOIN franchisee_types t ON t.id = f.type_id
        ORDER BY f.name ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Member Wallets Tab
    wallet_ensure_schema($pdo);
    $sumIncome = (float) $pdo->query('SELECT COALESCE(SUM(wallet_balance),0) FROM members')->fetchColumn();
    $sumTopup = (float) $pdo->query('SELECT COALESCE(SUM(topup_wallet_balance),0) FROM members')->fetchColumn();
    $sumShop = (float) $pdo->query('SELECT COALESCE(SUM(shopping_wallet_balance),0) FROM members')->fetchColumn();

    $where = ['1=1'];
    $params = [];
    if ($q !== '') {
        $where[] = '(m.member_id LIKE ? OR m.username LIKE ? OR m.full_name LIKE ? OR m.email LIKE ? OR m.phone LIKE ?)';
        $like = '%' . $q . '%';
        $params = [$like, $like, $like, $like, $like];
    }
    $whereSql = implode(' AND ', $where);

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM members m WHERE $whereSql");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));

    $stmt = $pdo->prepare("
        SELECT m.id, m.member_id, m.username, m.full_name, m.email, m.phone, m.status,
               m.wallet_balance, m.topup_wallet_balance, m.shopping_wallet_balance
        FROM members m
        WHERE $whereSql
        ORDER BY m.id DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $types = wallet_types();
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Tab Switcher -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.25rem;">
    <a href="wallets.php?tab=franchise" class="btn <?= $tab === 'franchise' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight:600">
        Franchise Wallets
    </a>
    <a href="wallets.php?tab=member" class="btn <?= $tab === 'member' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight:600">
        Member Wallets
    </a>
</div>

<?php if ($tab === 'franchise'): ?>
<!-- Franchise Wallets View -->
<div class="stats-grid">
    <div class="stat-card g-pink">
        <div class="label">Total Franchise Balance</div>
        <div class="value"><?= currency($sumFranchiseWallet) ?></div>
        <span class="muted" style="color:rgba(255,255,255,0.85);font-size:0.75rem">Across all centers</span>
    </div>
    <div class="stat-card g-blue">
        <div class="label">Total Franchisees</div>
        <div class="value"><?= $totalFranchisees ?></div>
        <span class="muted" style="color:rgba(255,255,255,0.85);font-size:0.75rem"><?= $activeFranchisees ?> active centers</span>
    </div>
    <div class="stat-card g-green">
        <div class="label">Franchise Purchases</div>
        <div class="value">
            <?php 
            $totSales = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM franchisee_purchases WHERE status != 'cancelled'")->fetchColumn();
            echo currency($totSales);
            ?>
        </div>
        <span class="muted" style="color:rgba(255,255,255,0.85);font-size:0.75rem">Total stock billed</span>
    </div>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<!-- Adjust Franchise Wallet -->
<div class="panel" id="adjustPanel">
    <div class="panel-header">
        <div>
            <h2>Adjust Franchise Wallet</h2>
            <p class="members-sub">Credit or debit wallet balance for stock purchase, security deposit or settlement</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" class="filters" style="align-items:end;flex-wrap:wrap">
            <input type="hidden" name="action_target" value="franchise">
            <div class="form-group" style="min-width:240px;flex:1">
                <label>Select Franchisee</label>
                <select name="franchisee_id" id="frSelect" required>
                    <option value="">— Select Franchisee —</option>
                    <?php foreach ($allFranchisees as $f): ?>
                        <option value="<?= (int) $f['id'] ?>" <?= ((int) ($_POST['franchisee_id'] ?? 0) === (int) $f['id']) ? 'selected' : '' ?>>
                            <?= e($f['name']) ?> (<?= e($f['franchisee_code']) ?>) [<?= e($f['type_name'] ?? 'Franchise') ?>] — Bal: <?= strip_tags(currency((float) $f['wallet_balance'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="min-width:140px">
                <label>Action</label>
                <select name="direction" required>
                    <option value="credit" <?= (($_POST['direction'] ?? '') === 'debit') ? '' : 'selected' ?>>Credit (+)</option>
                    <option value="debit" <?= (($_POST['direction'] ?? '') === 'debit') ? 'selected' : '' ?>>Debit (−)</option>
                </select>
            </div>
            <div class="form-group" style="min-width:140px">
                <label>Amount (₹)</label>
                <input type="number" name="amount" min="0.01" step="0.01" required value="<?= e($_POST['amount'] ?? '') ?>" placeholder="0.00">
            </div>
            <div class="form-group" style="min-width:200px;flex:1">
                <label>Remark / Note</label>
                <input type="text" name="note" maxlength="200" value="<?= e($_POST['note'] ?? '') ?>" placeholder="e.g. Stock advance, settlement">
            </div>
            <button type="submit" class="btn btn-primary" style="height:42px">Update Wallet</button>
        </form>
    </div>
</div>

<!-- Franchise Wallet Balances Table -->
<div class="panel members-panel">
    <div class="panel-header members-toolbar" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2>Franchise Wallet Balances</h2>
            <p class="members-sub" style="margin:0.2rem 0 0;color:var(--ink-muted);font-size:0.85rem">Real-time ledger balances of all distribution partners</p>
        </div>
    </div>
    <div class="panel-body members-filters">
        <form class="members-filter-form" method="get">
            <input type="hidden" name="tab" value="franchise">
            <div class="form-group">
                <label>Search Franchisee</label>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Code, name, city, phone…">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="wallets.php?tab=franchise" class="btn btn-outline">Reset</a>
        </form>
    </div>
    <div class="table-wrap">
        <table class="data members-table">
            <thead>
                <tr>
                    <th>Franchisee</th>
                    <th>Type / Tier</th>
                    <th>Contact</th>
                    <th>Location</th>
                    <th>Wallet Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="7"><div class="empty-state"><strong>No franchisees found</strong></div></td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <div class="member-cell">
                            <strong><?= e($r['name']) ?></strong>
                            <span style="color:var(--brand);font-weight:700"><?= e($r['franchisee_code']) ?></span>
                            <?php if (!empty($r['contact_person'])): ?>
                            <small class="muted">CP: <?= e($r['contact_person']) ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background:#e0f2fe;color:#0284c7;font-weight:700"><?= e($r['type_name'] ?? 'Franchise') ?></span>
                    </td>
                    <td><?= e($r['phone'] ?: '—') ?></td>
                    <td><?= e($r['city'] ? $r['city'] . ($r['state'] ? ', ' . $r['state'] : '') : '—') ?></td>
                    <td><strong style="font-size:1.05rem;color:#059669"><?= currency((float) $r['wallet_balance']) ?></strong></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td>
                        <button type="button" class="btn btn-outline btn-sm" onclick="selectFranchisee('<?= (int) $r['id'] ?>')">Adjust</button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="pagination members-pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= $i === $page ? 'active' : '' ?>" href="?tab=franchise&page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function selectFranchisee(id) {
    const sel = document.getElementById('frSelect');
    if (sel) {
        sel.value = id;
    }
    const panel = document.getElementById('adjustPanel');
    if (panel) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}
</script>

<?php else: ?>
<!-- Member Wallets View -->
<div class="stats-grid">
    <div class="stat-card"><div class="label">Income Wallets</div><div class="value"><?= currency($sumIncome) ?></div></div>
    <div class="stat-card"><div class="label">Topup Wallets</div><div class="value"><?= currency($sumTopup) ?></div></div>
    <div class="stat-card"><div class="label">Shopping Wallets</div><div class="value"><?= currency($sumShop) ?></div></div>
</div>

<?php foreach ($errors as $err): ?>
    <div class="alert alert-error"><?= e($err) ?></div>
<?php endforeach; ?>

<div class="panel">
    <div class="panel-header">
        <div>
            <h2>Adjust Member Wallet</h2>
            <p class="members-sub">Credit or debit Income / Topup / Shopping wallet for a member</p>
        </div>
    </div>
    <div class="panel-body">
        <form method="post" class="filters" style="align-items:end">
            <input type="hidden" name="action_target" value="member">
            <div class="form-group">
                <label>Member ID (internal)</label>
                <input type="number" name="member_id" min="1" required placeholder="members.id" value="<?= e($_POST['member_id'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Wallet</label>
                <select name="wallet_type" required>
                    <?php foreach ($types as $key => $meta): ?>
                        <option value="<?= e($key) ?>" <?= (($_POST['wallet_type'] ?? 'income') === $key) ? 'selected' : '' ?>><?= e($meta['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Action</label>
                <select name="direction" required>
                    <option value="credit" <?= (($_POST['direction'] ?? '') === 'debit') ? '' : 'selected' ?>>Credit (+)</option>
                    <option value="debit" <?= (($_POST['direction'] ?? '') === 'debit') ? 'selected' : '' ?>>Debit (−)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Amount</label>
                <input type="number" name="amount" min="0.01" step="0.01" required value="<?= e($_POST['amount'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Note</label>
                <input type="text" name="note" maxlength="200" value="<?= e($_POST['note'] ?? '') ?>" placeholder="Optional note">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>

<div class="panel members-panel">
    <div class="panel-header members-toolbar">
        <div>
            <h2>Member Wallet Balances</h2>
        </div>
    </div>
    <div class="panel-body members-filters">
        <form class="members-filter-form" method="get">
            <input type="hidden" name="tab" value="member">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Member ID, name, phone…">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="wallets.php?tab=member" class="btn btn-outline">Reset</a>
        </form>
    </div>
    <div class="table-wrap">
        <table class="data members-table">
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Income</th>
                    <th>Topup</th>
                    <th>Shopping</th>
                    <th>Status</th>
                    <th>Quick</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="6"><div class="empty-state"><strong>No members</strong></div></td></tr>
            <?php else: foreach ($rows as $r): ?>
                <tr>
                    <td>
                        <div class="member-cell">
                            <strong><a href="member-view.php?id=<?= (int) $r['id'] ?>"><?= e($r['full_name']) ?></a></strong>
                            <span><?= e($r['member_id']) ?> · <?= e($r['username']) ?></span>
                            <span>ID #<?= (int) $r['id'] ?></span>
                        </div>
                    </td>
                    <td><?= currency((float) $r['wallet_balance']) ?></td>
                    <td><?= currency((float) ($r['topup_wallet_balance'] ?? 0)) ?></td>
                    <td><?= currency((float) ($r['shopping_wallet_balance'] ?? 0)) ?></td>
                    <td><?= status_badge($r['status']) ?></td>
                    <td>
                        <button type="button" class="btn btn-outline btn-sm" onclick="document.querySelector('[name=member_id]').value='<?= (int) $r['id'] ?>'; window.scrollTo({top:0,behavior:'smooth'})">Adjust</button>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="pagination members-pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="<?= $i === $page ? 'active' : '' ?>" href="?tab=member&page=<?= $i ?>&q=<?= urlencode($q) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
