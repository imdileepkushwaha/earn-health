<?php
/**
 * Direct Franchise Login from Admin Panel
 * Lists Franchisees only (No members), allowing 1-click admin login into any Franchise portal.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/franchise.php';
$pageTitle = 'Direct Franchise Login';

franchise_ensure_tables($pdo);

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$q = trim($_GET['q'] ?? '');

// Handle Login Action
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' || isset($_GET['id'])) {
    $frId = (int) ($_POST['franchisee_id'] ?? $_GET['id'] ?? 0);

    if ($frId > 0) {
        $stmt = $pdo->prepare("
            SELECT f.*, t.name AS type_name
            FROM franchisees f
            LEFT JOIN franchisee_types t ON t.id = f.type_id
            WHERE f.id = ? AND f.status = 'active'
            LIMIT 1
        ");
        $stmt->execute([$frId]);
        $franchise = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$franchise) {
            $error = 'Active franchise center not found.';
        } else {
            // Set franchise session keys for full terminal access
            $_SESSION['franchise_id'] = (int) $franchise['id'];
            $_SESSION['franchise_code'] = $franchise['franchisee_code'];
            $_SESSION['franchise_name'] = $franchise['name'];
            $_SESSION['franchise_type'] = $franchise['type_name'] ?? 'Franchise';
            $_SESSION['franchise_login_by_admin'] = true;
            $_SESSION['franchise_login_admin_id'] = (int) $_SESSION['admin_id'];
            session_touch('franchise');

            // Keep admin session active
            session_touch('admin');

            log_activity('direct_franchise_login', 'Admin logged in as Franchise ' . $franchise['franchisee_code'] . ' (' . $franchise['name'] . ')');
            header('Location: ../franchise/index.php');
            exit;
        }
    }
}

// Fetch Franchisees List
$where = ["f.status = 'active'"];
$params = [];

if ($q !== '') {
    $where[] = '(f.franchisee_code LIKE ? OR f.name LIKE ? OR f.contact_person LIKE ? OR f.phone LIKE ? OR f.city LIKE ? OR t.name LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like, $like, $like, $like];
}

$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT f.id, f.franchisee_code, f.name, f.contact_person, f.phone, f.email, f.city, f.state,
           f.wallet_balance, f.status, t.name AS type_name, t.code AS type_code
    FROM franchisees f
    LEFT JOIN franchisee_types t ON t.id = f.type_id
    WHERE $whereSql
    ORDER BY f.id DESC
    LIMIT 100
");
$stmt->execute($params);
$franchisees = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="panel">
    <div class="panel-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2>Direct Franchise Login</h2>
            <p class="members-sub" style="margin:0.2rem 0 0;color:var(--ink-muted);font-size:0.85rem">
                Log into any franchise portal in a <strong>new tab</strong> without needing their password. Your admin session stays safe in this tab.
            </p>
        </div>
        <a href="franchisee-add.php" class="btn btn-outline btn-sm">+ Add Franchisee</a>
    </div>

    <div class="panel-body">
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom:1rem"><?= e($error) ?></div>
        <?php endif; ?>

        <form class="filters" method="get" style="margin-bottom:1.25rem">
            <div class="form-group" style="min-width:280px">
                <label>Search Franchisee</label>
                <input type="text" name="q" value="<?= e($q) ?>" placeholder="Code, center name, phone, city, tier...">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="direct-franchise-login.php" class="btn btn-outline">Reset</a>
        </form>
    </div>

    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>Franchise Code</th>
                    <th>Center / Name</th>
                    <th>Type / Tier</th>
                    <th>Location</th>
                    <th>Contact</th>
                    <th>Wallet Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!$franchisees): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:2.5rem;color:var(--ink-muted)">
                        No active franchise centers found. <?php if ($q): ?>Try a different search term or <?php endif; ?><a href="franchisee-add.php">Add a new franchisee</a>.
                    </td>
                </tr>
            <?php else: foreach ($franchisees as $f): ?>
                <tr>
                    <td>
                        <strong style="color:var(--brand);font-size:0.95rem"><?= e($f['franchisee_code']) ?></strong>
                    </td>
                    <td>
                        <div style="line-height:1.25">
                            <strong><?= e($f['name']) ?></strong>
                            <?php if (!empty($f['contact_person'])): ?>
                            <small style="display:block;color:var(--ink-muted)">CP: <?= e($f['contact_person']) ?></small>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="background:#e0f2fe;color:#0284c7;font-weight:700;font-size:0.8rem">
                            <?= e($f['type_name'] ?? 'Franchise') ?>
                        </span>
                    </td>
                    <td>
                        <?= e($f['city'] ? $f['city'] . ($f['state'] ? ', ' . $f['state'] : '') : '—') ?>
                    </td>
                    <td>
                        <div><?= e($f['phone'] ?: '—') ?></div>
                        <?php if (!empty($f['email'])): ?>
                        <small style="color:var(--ink-muted)"><?= e($f['email']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color:#059669;font-size:0.95rem"><?= currency((float) $f['wallet_balance']) ?></strong>
                    </td>
                    <td>
                        <form method="post" target="_blank" rel="noopener" style="display:inline">
                            <input type="hidden" name="franchisee_id" value="<?= (int) $f['id'] ?>">
                            <button type="submit" class="btn btn-accent btn-sm" style="display:inline-flex;align-items:center;gap:0.35rem">
                                <span>Login as Franchise</span>
                                <span aria-hidden="true">→</span>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
