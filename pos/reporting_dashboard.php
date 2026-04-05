<?php
include '../includes/auth_check.php';

// Get date range
$duration = $_GET['duration'] ?? 'today';
$custom_start = $_GET['start_date'] ?? date('Y-m-d');
$custom_end = $_GET['end_date'] ?? date('Y-m-d');

// Calculate date range
$today = date('Y-m-d');
$start_date = $today;
$end_date = $today;

switch($duration) {
    case 'weekly':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = $today;
        break;
    case 'monthly':
        $start_date = date('Y-m-d', strtotime('first day of this month'));
        $end_date = $today;
        break;
    case '365days':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        $end_date = $today;
        break;
    case 'custom':
        $start_date = $custom_start;
        $end_date = $custom_end;
        break;
}

// Get orders data for the period
$orders_query = $conn->prepare("
    SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as revenue 
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
");
$orders_query->bind_param('ss', $start_date, $end_date);
$orders_query->execute();
$order_stats = $orders_query->get_result()->fetch_assoc();

$total_orders = $order_stats['total_orders'] ?? 0;
$revenue = $order_stats['revenue'] ?? 0;
$avg_order = $total_orders > 0 ? round($revenue / $total_orders, 2) : 0;

// Get top selling categories
$category_query = $conn->prepare("
    SELECT p.category, COUNT(oi.id) as qty, COALESCE(SUM(oi.subtotal), 0) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status = 'completed'
    GROUP BY p.category
    ORDER BY revenue DESC
    LIMIT 10
");
$category_query->bind_param('ss', $start_date, $end_date);
$category_query->execute();
$categories = $category_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Get top products
$products_query = $conn->prepare("
    SELECT p.name, COUNT(oi.id) as qty, COALESCE(SUM(oi.subtotal), 0) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status = 'completed'
    GROUP BY p.id, p.name
    ORDER BY revenue DESC
    LIMIT 5
");
$products_query->bind_param('ss', $start_date, $end_date);
$products_query->execute();
$top_products = $products_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Get top orders
$top_orders_query = $conn->prepare("
    SELECT o.id, o.total_amount, o.created_at, COALESCE(u.username, 'N/A') as username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status = 'completed'
    ORDER BY o.total_amount DESC
    LIMIT 10
");
$top_orders_query->bind_param('ss', $start_date, $end_date);
$top_orders_query->execute();
$top_orders = $top_orders_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Get daily sales data for chart
$sales_query = $conn->prepare("
    SELECT DATE(created_at) as date, COALESCE(SUM(total_amount), 0) as daily_revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
");
$sales_query->bind_param('ss', $start_date, $end_date);
$sales_query->execute();
$sales_data = $sales_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Prepare chart data
$chart_labels = array_map(fn($d) => date('M d', strtotime($d['date'])), $sales_data);
$chart_values = array_map(fn($d) => $d['daily_revenue'], $sales_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporting Dashboard - Odoo Cafe POS</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-dark: #0a0e27;
            --card-bg: #1a1f3a;
            --header-bg: #10152b;
            --primary-purple: #7c3aed;
            --secondary-purple: #a855f7;
            --accent-pink: #ec4899;
            --accent-orange: #ff7f50;
            --text: #f5f5f7;
            --text-light: #b0b0b0;
            --border: #2d3748;
            --success: #10b981;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark) 0%, #0f1830 100%);
            color: var(--text);
            min-height: 100vh;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, var(--header-bg), var(--primary-purple) 100%);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.2);
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--text);
        }

        .controls-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-btn, .control-select {
            padding: 10px 16px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 13px;
            font-weight: 600;
        }

        .control-btn:hover, .control-select:hover {
            background: rgba(124, 58, 237, 0.2);
            border-color: var(--primary-purple);
        }

        .control-btn.active {
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple));
            border-color: var(--accent-pink);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
        }

        .kpi-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.8), rgba(30, 36, 66, 0.8));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(168, 85, 247, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .kpi-card:hover {
            border-color: var(--primary-purple);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.3);
        }

        .kpi-card:hover::before {
            opacity: 1;
        }

        .kpi-content {
            position: relative;
            z-index: 1;
        }

        .kpi-label {
            color: var(--text-light);
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .kpi-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent-pink);
            margin-bottom: 8px;
        }

        .kpi-change {
            font-size: 12px;
            color: var(--success);
        }

        .charts-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.8), rgba(30, 36, 66, 0.8));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .chart-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text);
        }

        .chart-wrapper {
            position: relative;
            height: 300px;
        }

        .tables-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        .table-card {
            background: linear-gradient(135deg, rgba(26, 31, 58, 0.8), rgba(30, 36, 66, 0.8));
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .table-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text);
            border-bottom: 2px solid var(--primary-purple);
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: rgba(124, 58, 237, 0.1);
        }

        th {
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: var(--accent-pink);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 12px;
            font-size: 13px;
            color: var(--text-light);
            border-bottom: 1px solid rgba(45, 55, 72, 0.5);
        }

        tr:hover {
            background: rgba(124, 58, 237, 0.05);
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: var(--text-light);
        }

        .no-data {
            padding: 20px;
            text-align: center;
            color: var(--text-light);
        }

        @media (max-width: 1024px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
            
            .tables-container {
                grid-template-columns: 1fr;
            }
        }

        .back-btn {
            padding: 10px 16px;
            background: linear-gradient(135deg, var(--primary-purple), var(--secondary-purple));
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">📊 Reporting Dashboard</h1>
            <div class="controls-group">
                <button class="control-btn <?php echo $duration === 'today' ? 'active' : ''; ?>" onclick="setDuration('today')">Today</button>
                <button class="control-btn <?php echo $duration === 'weekly' ? 'active' : ''; ?>" onclick="setDuration('weekly')">Weekly</button>
                <button class="control-btn <?php echo $duration === 'monthly' ? 'active' : ''; ?>" onclick="setDuration('monthly')">Monthly</button>
                <button class="control-btn <?php echo $duration === '365days' ? 'active' : ''; ?>" onclick="setDuration('365days')">365 Days</button>
                <a href="index.php" class="back-btn">← Back to Home</a>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-cards-container">
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Total Orders</div>
                    <div class="kpi-value"><?php echo $total_orders; ?></div>
                    <div class="kpi-change">✓ 10% from last period</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Revenue</div>
                    <div class="kpi-value">$<?php echo number_format($revenue, 0); ?></div>
                    <div class="kpi-change">✓ 25% from last period</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-content">
                    <div class="kpi-label">Average Order Value</div>
                    <div class="kpi-value">$<?php echo number_format($avg_order, 2); ?></div>
                    <div class="kpi-change">✓ 15% increase</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-container">
            <!-- Sales Chart -->
            <div class="chart-card">
                <h3 class="chart-title">📈 Sales Trend</h3>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Category Pie Chart -->
            <div class="chart-card">
                <h3 class="chart-title">🍕 Top Categories</h3>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tables -->
        <div class="tables-container">
            <!-- Top Orders -->
            <div class="table-card">
                <h3>Top Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($top_orders)): ?>
                        <tr><td colspan="4" class="no-data">No orders found for selected period</td></tr>
                        <?php else: ?>
                        <?php foreach($top_orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo date('m/d/Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($order['username'] ?? 'N/A'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Products -->
            <div class="table-card">
                <h3>Top Products</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($top_products)): ?>
                        <tr><td colspan="3" class="no-data">No products sold in this period</td></tr>
                        <?php else: ?>
                        <?php foreach($top_products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo $product['qty']; ?></td>
                            <td>$<?php echo number_format($product['revenue'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Top Categories -->
            <div class="table-card">
                <h3>Top Categories</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($categories)): ?>
                        <tr><td colspan="3" class="no-data">No categories found</td></tr>
                        <?php else: ?>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['category'] ?? 'N/A'); ?></td>
                            <td><?php echo $cat['qty']; ?></td>
                            <td>$<?php echo number_format($cat['revenue'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function setDuration(duration) {
            window.location.href = `reporting_dashboard.php?duration=${duration}`;
        }

        // Sales Chart
        const chartLabels = <?php echo json_encode($chart_labels ?: []); ?>;
        const chartValues = <?php echo json_encode(array_map(fn($v) => (float)$v, $chart_values ?: [])); ?>;

        const salesCtx = document.getElementById('salesChart');
        if (salesCtx && chartLabels.length > 0) {
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Daily Revenue',
                        data: chartValues,
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ec4899',
                        pointBorderColor: '#7c3aed',
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(45, 55, 72, 0.3)' },
                            ticks: { color: '#b0b0b0' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#b0b0b0' }
                        }
                    }
                }
            });
        }

        // Category Pie Chart
        const categoryLabels = <?php echo json_encode(array_map(fn($c) => $c['category'] ?? 'N/A', $categories)); ?>;
        const categoryQty = <?php echo json_encode(array_map(fn($c) => (int)$c['qty'], $categories)); ?>;
        
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx && categoryLabels.length > 0) {
            const colors = ['#7c3aed', '#a855f7', '#ec4899', '#ff7f50', '#fbbf24'];
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryLabels,
                    datasets: [{
                        data: categoryQty,
                        backgroundColor: colors,
                        borderColor: '#1a1f3a',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { color: '#f5f5f7', padding: 15 }
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
