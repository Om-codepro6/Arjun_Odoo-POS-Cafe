<?php
require_once __DIR__ . '/../auth/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/dashboard.php');
    exit();
}

$totalSales = 0.0;
if ($res = $conn->query('SELECT COALESCE(SUM(amount), 0) AS s FROM payments')) {
    $row = $res->fetch_assoc();
    $totalSales = (float) ($row['s'] ?? 0);
    $res->free();
}

$activeSessions = 0;
if ($res = $conn->query('SELECT COUNT(*) AS c FROM pos_sessions WHERE session_end IS NULL')) {
    $row = $res->fetch_assoc();
    $activeSessions = (int) ($row['c'] ?? 0);
    $res->free();
}

$ordersToday = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE DATE(created_at) = CURDATE()")) {
    $row = $res->fetch_assoc();
    $ordersToday = (int) ($row['c'] ?? 0);
    $res->free();
}

$currentNav = 'dashboard';
$pageTitle = 'Dashboard';
$pageSubtitle = 'Today at a glance';
$pageHeading = 'Dashboard';
require __DIR__ . '/components/header.php';
?>

<section class="admin-welcome">
    <h2>Welcome back</h2>
    <p><?php echo htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?> — here is your restaurant snapshot.</p>
</section>

<div class="admin-stats">
    <article class="stat-card stat-card--sales">
        <span class="stat-card__icon" aria-hidden="true">◆</span>
        <span class="stat-card__label">Total sales</span>
        <span class="stat-card__value">₹<?php echo number_format($totalSales, 2); ?></span>
        <span class="stat-card__hint">All recorded payments</span>
    </article>
    <article class="stat-card stat-card--sessions">
        <span class="stat-card__icon" aria-hidden="true">◎</span>
        <span class="stat-card__label">Active sessions</span>
        <span class="stat-card__value"><?php echo number_format($activeSessions); ?></span>
        <span class="stat-card__hint">POS sessions not yet closed</span>
    </article>
    <article class="stat-card stat-card--orders">
        <span class="stat-card__icon" aria-hidden="true">▸</span>
        <span class="stat-card__label">Orders today</span>
        <span class="stat-card__value"><?php echo number_format($ordersToday); ?></span>
        <span class="stat-card__hint">Orders created today</span>
    </article>
</div>

<div class="admin-panel-card">
    <h3>Quick actions</h3>
    <p>Configure menus, tables, and payment methods from the sidebar. Staff open the POS terminal from their own login.</p>
    <p style="margin-top:16px;"><a class="admin-inline-link" href="../auth/admin.php">User accounts →</a></p>
</div>

<?php require __DIR__ . '/components/footer.php'; ?>
