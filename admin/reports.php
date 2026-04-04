<?php
// ============================================
// ADMIN - REPORTS
// Sales summary, payment breakdown, product totals
// ============================================

include '../includes/auth_check.php';
include '../config/db.php';

if ($current_role !== 'admin') {
    header('Location: ../pos/index.php');
    exit();
}

$fromDate = $_GET['from_date'] ?? date('Y-m-d', strtotime('-7 days'));
$toDate = $_GET['to_date'] ?? date('Y-m-d');
$userFilter = $_GET['user'] ?? 'all';
$sessionFilter = $_GET['session'] ?? 'all';

$params = [];
$where = "WHERE DATE(o.created_at) BETWEEN ? AND ?";
$params[] = $fromDate;
$params[] = $toDate;
$whereClause = "WHERE DATE(o.created_at) BETWEEN '" . $conn->real_escape_string($fromDate) . "' AND '" . $conn->real_escape_string($toDate) . "'";

if ($userFilter !== 'all' && is_numeric($userFilter)) {
    $where .= ' AND o.user_id = ?';
    $params[] = $userFilter;
    $whereClause .= ' AND o.user_id = ' . (int)$userFilter;
}

if ($sessionFilter !== 'all' && is_numeric($sessionFilter)) {
    $where .= ' AND o.session_id = ?';
    $params[] = $sessionFilter;
    $whereClause .= ' AND o.session_id = ' . (int)$sessionFilter;
}

$stmt = $conn->prepare("SELECT
    COALESCE(SUM(o.total_amount), 0) AS total_revenue,
    COUNT(o.id) AS total_orders,
    COALESCE(AVG(o.total_amount), 0) AS avg_order
FROM orders o
$where");

$types = str_repeat('s', count($params));
if ($stmt === false) {
    die('Prepare error: ' . $conn->error);
}

$stmt->bind_param($types, ...$params);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalRevenue = (float) ($summary['total_revenue'] ?? 0);
$totalOrders = (int) ($summary['total_orders'] ?? 0);
$avgOrder = (float) ($summary['avg_order'] ?? 0);

$paymentBreakdown = [];
$result = $conn->query(
    "SELECT payment_method AS method, COUNT(*) AS orders_count, COALESCE(SUM(amount),0) AS amount
     FROM payments
     WHERE DATE(created_at) BETWEEN '" . $conn->real_escape_string($fromDate) . "' AND '" . $conn->real_escape_string($toDate) . "'
     GROUP BY payment_method"
);
while ($row = $result->fetch_assoc()) {
    $paymentBreakdown[$row['method']] = $row;
}

$topProducts = [];
$result = $conn->query(
    "SELECT p.name, SUM(oi.quantity) AS qty_sold, COALESCE(SUM(oi.quantity * oi.price), 0) AS revenue
     FROM order_items oi
     JOIN products p ON p.id = oi.product_id
     JOIN orders o ON o.id = oi.order_id
     WHERE DATE(o.created_at) BETWEEN '" . $conn->real_escape_string($fromDate) . "' AND '" . $conn->real_escape_string($toDate) . "'
     GROUP BY oi.product_id
     ORDER BY qty_sold DESC
     LIMIT 6"
);
while ($row = $result->fetch_assoc()) {
    $topProducts[] = $row;
}

$salesHistory = [];
$result = $conn->query(
    "SELECT DATE(o.created_at) AS sales_date,
            COALESCE(o.session_id, 0) AS session_id,
            COUNT(o.id) AS orders_count,
            COALESCE(SUM(o.total_amount), 0) AS revenue,
            COALESCE(AVG(o.total_amount), 0) AS avg_order
     FROM orders o
     $whereClause
     GROUP BY sales_date, o.session_id
     ORDER BY sales_date DESC, session_id DESC"
);
while ($row = $result->fetch_assoc()) {
    $salesHistory[] = $row;
}

$users = [];
$result = $conn->query('SELECT id, username FROM users ORDER BY username');
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$sessions = [];
$result = $conn->query('SELECT id FROM pos_sessions ORDER BY id DESC LIMIT 20');
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Odoo Cafe Admin</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

    <!-- Top Nav -->
    <nav class="admin-top-nav">
        <a href="dashboard.php" class="nav-brand">Odoo Cafe</a>
        <div class="nav-right">
            <span class="nav-user">👤 <?php echo htmlspecialchars($current_username); ?></span>
            <a href="../auth/logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-main">

        <div class="admin-page-header">
            <h1 class="admin-page-title">Reports</h1>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-outline btn-sm" onclick="window.print()">📄 Print</button>
                <button class="btn btn-outline btn-sm">📊 Export XLS</button>
            </div>
        </div>

        <div class="admin-card">
            <form method="GET" class="report-filters">
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" name="from_date" value="<?php echo htmlspecialchars($fromDate); ?>">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" name="to_date" value="<?php echo htmlspecialchars($toDate); ?>">
                </div>
                <div class="filter-group">
                    <label>Session</label>
                    <select name="session">
                        <option value="all">All Sessions</option>
                        <?php foreach ($sessions as $session): ?>
                            <option value="<?php echo (int)$session['id']; ?>" <?php echo $sessionFilter == $session['id'] ? 'selected' : ''; ?>>Session #<?php echo (int)$session['id']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>User</label>
                    <select name="user">
                        <option value="all">All Users</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo (int)$user['id']; ?>" <?php echo $userFilter == $user['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($user['username']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary btn-sm" style="height: 40px;">Apply</button>
            </form>
        </div>

        <div class="stats-grid">
            <div class="stat-card green">
                <div class="stat-card-icon">💰</div>
                <div class="stat-card-value">₹<?php echo number_format($totalRevenue, 2); ?></div>
                <div class="stat-card-label">Total Revenue</div>
            </div>
            <div class="stat-card">
                <div class="stat-card-icon">📋</div>
                <div class="stat-card-value"><?php echo number_format($totalOrders); ?></div>
                <div class="stat-card-label">Total Orders</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-card-icon">📊</div>
                <div class="stat-card-value">₹<?php echo number_format($avgOrder, 2); ?></div>
                <div class="stat-card-label">Avg Order Value</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="admin-card">
                <h3 class="admin-card-title">Payment Breakdown</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Orders</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (['cash' => '💵 Cash', 'digital' => '💳 Digital', 'upi' => '📱 UPI'] as $method => $label): ?>
                            <tr>
                                <td><?php echo $label; ?></td>
                                <td><?php echo isset($paymentBreakdown[$method]) ? (int)$paymentBreakdown[$method]['orders_count'] : 0; ?></td>
                                <td><strong>₹<?php echo number_format((float)($paymentBreakdown[$method]['amount'] ?? 0), 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-card">
                <h3 class="admin-card-title">Top Products</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty Sold</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($topProducts)): ?>
                            <tr><td colspan="3" class="text-muted">No product sales found.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($topProducts as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo (int)$product['qty_sold']; ?></td>
                                <td><strong>₹<?php echo number_format($product['revenue'], 2); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="data-table-container">
            <div class="data-table-header">
                <h3 class="data-table-title">Sales History</h3>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Session</th>
                        <th>Orders</th>
                        <th>Revenue</th>
                        <th>Avg Order</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($salesHistory)): ?>
                        <tr><td colspan="5" class="text-muted">No sales history for the selected period.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($salesHistory as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['sales_date']); ?></td>
                            <td><?php echo $row['session_id'] ? 'Session #' . (int)$row['session_id'] : 'No session'; ?></td>
                            <td><?php echo (int)$row['orders_count']; ?></td>
                            <td>₹<?php echo number_format($row['revenue'], 2); ?></td>
                            <td>₹<?php echo number_format($row['avg_order'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
