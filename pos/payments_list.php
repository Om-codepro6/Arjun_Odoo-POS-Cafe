<?php
include '../includes/auth_check.php';

// Fetch payments grouped by payment method
$payments_result = $conn->query("
    SELECT payment_method, SUM(amount) as total_amount, COUNT(*) as count, MAX(created_at) as last_date
    FROM payments
    GROUP BY payment_method
    ORDER BY total_amount DESC
");

$payment_groups = [];
while ($row = $payments_result->fetch_assoc()) {
    $payment_groups[$row['payment_method']] = $row;
}

// Fetch individual payment records
$individual_payments = $conn->query("
    SELECT payment_method, amount, created_at, order_id
    FROM payments
    ORDER BY created_at DESC
    LIMIT 50
");

$payments_by_method = [];
while ($row = $individual_payments->fetch_assoc()) {
    $method = $row['payment_method'];
    if (!isset($payments_by_method[$method])) {
        $payments_by_method[$method] = [];
    }
    $payments_by_method[$method][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>💳 Payments - Cafe POS</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
  <style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  background: url('https://images.unsplash.com/photo-1559496417-e7f25cb247f3?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
  color: #fff;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  pointer-events: none;
  z-index: -1;
}

/* Top Navigation */
.top-nav {
  background: rgba(26, 26, 26, 0.95);
  backdrop-filter: blur(10px);
  padding: 15px 30px;
  display: flex;
  align-items: center;
  gap: 20px;
  border-bottom: 1px solid #333;
  position: relative;
  z-index: 10;
}

.back-link {
  color: #fff;
  text-decoration: none;
  font-size: 1.2rem;
  transition: color 0.3s ease;
}

.back-link:hover {
  color: #ff66cc;
}

/* Container */
.payments-container {
  max-width: 1000px;
  margin: 30px auto;
  background: linear-gradient(145deg, rgba(26, 26, 26, 0.95), rgba(40, 40, 40, 0.95));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 25px;
  backdrop-filter: blur(10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

/* Header */
.payments-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid rgba(255, 102, 204, 0.3);
}

.payments-header h1 {
  font-size: 2rem;
  background: linear-gradient(90deg, #d8b4fe, #c084fc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
}

/* Top Menu */
.top-menu {
  display: flex;
  gap: 25px;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #333;
}

.top-menu span {
  color: #aaa;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.top-menu span:hover {
  color: #fff;
}

.top-menu .active {
  color: #fff;
}

.top-menu .active::after {
  content: '';
  position: absolute;
  bottom: -11px;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #d8b4fe, #c084fc);
  border-radius: 1px;
}

/* Title */
.title {
  font-size: 1.5rem;
  margin: 15px 0 20px;
  color: #d8b4fe;
  font-weight: 600;
}

/* Table Header */
.table-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  background: linear-gradient(90deg, rgba(216, 180, 254, 0.1), rgba(192, 132, 252, 0.1));
  padding: 15px;
  font-size: 14px;
  color: #d8b4fe;
  border-radius: 8px;
  font-weight: 600;
  border: 1px solid rgba(216, 180, 254, 0.2);
  margin-bottom: 15px;
}

/* Summary Stats */
.summary-stats {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 15px;
  margin-bottom: 25px;
}

.stat-card {
  background: rgba(255, 102, 204, 0.1);
  border: 1px solid rgba(255, 102, 204, 0.3);
  border-radius: 8px;
  padding: 15px;
  text-align: center;
}

.stat-card .label {
  color: #aaa;
  font-size: 0.9rem;
  margin-bottom: 8px;
}

.stat-card .value {
  font-size: 1.8rem;
  color: #ff66cc;
  font-weight: 600;
}

/* Group */
.group {
  margin-bottom: 12px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.group-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  padding: 15px;
  background: linear-gradient(90deg, rgba(216, 180, 254, 0.15), rgba(192, 132, 252, 0.1));
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 600;
  align-items: center;
  user-select: none;
}

.group-header:hover {
  background: linear-gradient(90deg, rgba(216, 180, 254, 0.25), rgba(192, 132, 252, 0.2));
  transform: translateX(2px);
}

.group-header span:last-child {
  text-align: right;
}

/* Body */
.group-body {
  display: none;
  background: rgba(0, 0, 0, 0.2);
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
}

.group.active .group-body {
  display: block;
  max-height: 1000px;
}

/* Rows */
.row {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  padding: 12px 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  font-size: 14px;
  transition: background 0.2s ease;
  align-items: center;
}

.row:last-child {
  border-bottom: none;
}

.row:hover {
  background: rgba(255, 102, 204, 0.08);
}

.row span:last-child {
  text-align: right;
  color: #86efac;
  font-weight: 500;
}

.row span:first-child {
  color: #aaa;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #aaa;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 15px;
  opacity: 0.5;
}

.empty-state p {
  font-size: 1.1rem;
  margin-bottom: 10px;
}

/* Responsive */
@media (max-width: 768px) {
  .payments-container {
    margin: 15px;
    padding: 15px;
  }

  .table-header,
  .group-header,
  .row {
    grid-template-columns: 1fr 1fr;
  }

  .summary-stats {
    grid-template-columns: 1fr;
  }

  .row span:last-child,
  .group-header span:last-child {
    text-align: right;
    grid-column: 2;
  }
}
  </style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
  <a href="index.php" class="back-link">⬅️ Back</a>
  <h2 style="margin: 0; color: #fff;">💳 Payment Management</h2>
</div>

<!-- Payments Container -->
<div class="payments-container">

  <!-- Header -->
  <div class="payments-header">
    <h1>💳 Payments</h1>
    <div style="color: #aaa; font-size: 0.9rem;">Total Transactions: <span style="color: #d8b4fe; font-weight: 600;"><?php echo array_sum(array_map(function($m) { return count($m); }, $payments_by_method)); ?></span></div>
  </div>

  <!-- Top Menu -->
  <div class="top-menu">
    <span class="active" onclick="goToOrders()" style="cursor: pointer;">📋 Orders</span>
    <span onclick="goToProducts()" style="cursor: pointer;">🍽️ Products</span>
    <span onclick="goToReporting()" style="cursor: pointer;">📊 Reporting</span>
  </div>

  <!-- Summary Stats -->
  <div class="summary-stats">
    <?php
      $payment_methods = ['Cash', 'Card', 'UPI', 'Digital'];
      $colors = ['#86efac', '#60a5fa', '#fbbf24', '#f87171'];
      $emojis = ['💵', '💳', '📱', '💰'];
      
      foreach ($payment_methods as $i => $method) {
        $total = isset($payment_groups[$method]) ? $payment_groups[$method]['total_amount'] : 0;
        echo "<div class='stat-card' style='background: rgba(" . implode(',', array_slice(str_split(substr(str_pad(hexdec(str_replace('#','',$colors[$i])), 6, '0', STR_PAD_LEFT), 0, 6), 2), 0, 3)) . ", 0.1);'>
          <div class='label'>{$emojis[$i]} {$method}</div>
          <div class='value'>₹" . number_format($total, 2) . "</div>
        </div>";
      }
    ?>
  </div>

  <!-- Table Header -->
  <div class="table-header">
    <span>💳 Payment Method</span>
    <span>📅 Date</span>
    <span style="text-align: right;">💰 Amount</span>
  </div>

  <!-- Payment Groups -->
  <?php if (count($payment_groups) > 0): ?>
    <?php foreach ($payment_groups as $method => $group): ?>
    <div class="group <?php echo $method == 'Cash' ? 'active' : ''; ?>">
      <div class="group-header" onclick="toggleGroup(this)">
        <span><?php echo $method === 'Cash' ? '▼' : '▶'; ?> <?php 
          $emoji_map = ['Cash' => '💵', 'Card' => '💳', 'UPI' => '📱', 'Digital' => '💰'];
          echo ($emoji_map[$method] ?? '💰') . ' ' . $method;
        ?></span>
        <span><?php echo $group['count']; ?> tx</span>
        <span>₹<?php echo number_format($group['total_amount'], 2); ?></span>
      </div>

      <div class="group-body">
        <?php if (isset($payments_by_method[$method])): ?>
          <?php foreach ($payments_by_method[$method] as $payment): ?>
          <div class="row">
            <span><?php echo htmlspecialchars($method); ?></span>
            <span><?php echo date('d M Y', strtotime($payment['created_at'])); ?></span>
            <span>₹<?php echo number_format($payment['amount'], 2); ?></span>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php else: ?>
  <div class="empty-state">
    <i class="bi bi-wallet2"></i>
    <p>No payment records found</p>
    <small>Payment transactions will appear here</small>
  </div>
  <?php endif; ?>

</div>

<script>
function toggleGroup(el) {
  const group = el.parentElement;
  group.classList.toggle("active");

  const arrow = el.querySelector("span");
  const text = arrow.innerText;
  
  if (group.classList.contains("active")) {
    arrow.innerText = text.replace("▶", "▼");
  } else {
    arrow.innerText = text.replace("▼", "▶");
  }
}

function goToOrders() {
  window.location.href = 'orders.php';
}

function goToProducts() {
  window.location.href = 'order.php';
}

function goToReporting() {
  window.location.href = '../admin/reports.php';
}
</script>

</body>
</html>