<?php
include '../includes/auth_check.php';

// Check if user is admin
if ($current_role !== 'admin') {
    header('Location: ../pos/index.php');
    exit();
}

// Get dashboard statistics
$today = date('Y-m-d');

// Total sales today
$sales_result = $conn->query("
    SELECT SUM(total_amount) as total_sales 
    FROM orders 
    WHERE DATE(created_at) = '$today' AND status = 'completed'
");
$sales_row = $sales_result->fetch_assoc();
$total_sales = $sales_row['total_sales'] ?? 0;

// Active sessions
$sessions_result = $conn->query("
    SELECT COUNT(*) as active_sessions 
    FROM pos_sessions 
    WHERE session_end IS NULL
");
$sessions_row = $sessions_result->fetch_assoc();
$active_sessions = $sessions_row['active_sessions'] ?? 0;

// Orders today
$orders_result = $conn->query("
    SELECT COUNT(*) as orders_today 
    FROM orders 
    WHERE DATE(created_at) = '$today'
");
$orders_row = $orders_result->fetch_assoc();
$orders_today = $orders_row['orders_today'] ?? 0;

// Recent orders
$recent_orders = $conn->query("
    SELECT o.id, o.table_id, o.total_amount, o.status, o.created_at, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE DATE(o.created_at) = '$today'
    ORDER BY o.created_at DESC
    LIMIT 10
");

$page_title = 'Admin Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Cafe POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="admin-container">
        <!-- SIDEBAR -->
        <?php include '../includes/sidebar.php'; ?>

        <div class="admin-main">
            <!-- HEADER -->
            <div class="admin-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="bi bi-speedometer2"></i> Dashboard</h1>
                        <p>Welcome back, <strong><?php echo htmlspecialchars($current_username); ?></strong>!</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Today: <?php echo date('l, M d, Y'); ?></small>
                    </div>
                </div>
            </div>

            <!-- CONTENT -->
            <div class="admin-content">
                <!-- STAT CARDS -->
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="stat-icon">💰</div>
                        <div class="stat-label">Total Sales</div>
                        <div class="stat-value">₹<?php echo number_format($total_sales, 2); ?></div>
                        <small class="text-muted">Today</small>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🔄</div>
                        <div class="stat-label">Active Sessions</div>
                        <div class="stat-value"><?php echo (int)$active_sessions; ?></div>
                        <small class="text-muted">POS Terminals</small>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-label">Orders Today</div>
                        <div class="stat-value"><?php echo (int)$orders_today; ?></div>
                        <small class="text-muted">Completed</small>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">🍴</div>
                        <div class="stat-label">Average Order</div>
                        <div class="stat-value">₹<?php echo $orders_today > 0 ? number_format($total_sales / $orders_today, 2) : '0.00'; ?></div>
                        <small class="text-muted">Value</small>
                    </div>
                </div>

                <!-- QUICK ACTIONS -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-lightning-fill"></i> Quick Actions
                    </div>
                    <div class="card-body">
                        <div class="row gap-2">
                            <div class="col-auto">
                                <a href="products.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Add Product
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="floors.php" class="btn btn-dark">
                                    <i class="bi bi-door-open"></i> Manage Tables
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="payments.php" class="btn btn-success">
                                    <i class="bi bi-credit-card"></i> Payment Settings
                                </a>
                            </div>
                            <div class="col-auto">
                                <a href="reports.php" class="btn btn-info">
                                    <i class="bi bi-graph-up"></i> View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RECENT ORDERS -->
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-clock-history"></i> Recent Orders Today
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_orders->num_rows > 0): ?>
                                    <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong>#<?php echo (int)$order['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                                            <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <i class="bi bi-check-circle"></i> <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('H:i:s', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No orders today
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
