<?php
include '../includes/auth_check.php';

// Fetch orders from database
$orders_result = $conn->query("
    SELECT o.id, o.id as order_no, o.user_id, o.total_amount, o.status, o.created_at, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 20
");

$orders = [];
while ($row = $orders_result->fetch_assoc()) {
    $orders[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>📋 Orders - Cafe POS</title>
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
.orders-container {
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
.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid rgba(255, 102, 204, 0.3);
}

.orders-header h1 {
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

/* Actions Bar */
.actions-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 15px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
}

.selected-count {
  color: #d8b4fe;
  font-weight: 600;
}

.action-dropdown {
  position: relative;
}

.action-dropdown button {
  background: linear-gradient(135deg, #ff66cc 0%, #ff99dd 100%);
  color: #1a1a1a;
  border: none;
  padding: 10px 15px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.action-dropdown button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255, 102, 204, 0.3);
}

.dropdown {
  display: none;
  position: absolute;
  right: 0;
  top: 100%;
  background: linear-gradient(145deg, rgba(40, 40, 40, 0.98), rgba(50, 50, 50, 0.98));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  width: 150px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
  z-index: 100;
  overflow: hidden;
}

.dropdown.show {
  display: block;
}

.dropdown div {
  padding: 12px 15px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.dropdown div:last-child {
  border-bottom: none;
}

.dropdown div:hover {
  background: rgba(255, 102, 204, 0.2);
}

.dropdown div.delete {
  color: #ff6b6b;
}

.dropdown div.delete:hover {
  background: rgba(255, 107, 107, 0.2);
}

/* Table */
.orders-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.orders-table thead {
  background: linear-gradient(90deg, rgba(216, 180, 254, 0.1), rgba(192, 132, 252, 0.1));
}

.orders-table th {
  text-align: left;
  padding: 15px;
  color: #d8b4fe;
  font-weight: 600;
  border-bottom: 2px solid rgba(216, 180, 254, 0.3);
}

.orders-table tbody tr {
  transition: background 0.2s ease;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.orders-table tbody tr:hover {
  background: rgba(255, 102, 204, 0.1);
}

.orders-table td {
  padding: 15px;
  color: #ccc;
}

.orders-table input[type="checkbox"] {
  accent-color: #ff66cc;
  cursor: pointer;
  width: 18px;
  height: 18px;
}

/* Status Badge */
.status {
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  display: inline-block;
}

.status.draft {
  background: rgba(30, 58, 138, 0.6);
  color: #60a5fa;
}

.status.pending {
  background: rgba(245, 158, 11, 0.6);
  color: #fbbf24;
}

.status.paid {
  background: rgba(22, 163, 74, 0.6);
  color: #86efac;
}

.status.completed {
  background: rgba(99, 102, 241, 0.6);
  color: #a5b4fc;
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
  .orders-container {
    margin: 15px;
    padding: 15px;
  }

  .orders-table {
    font-size: 0.9rem;
  }

  .orders-table th,
  .orders-table td {
    padding: 10px;
  }

  .top-menu {
    gap: 15px;
  }

  .orders-header h1 {
    font-size: 1.5rem;
  }
}
  </style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
  <a href="index.php" class="back-link">⬅️ Back</a>
  <h2 style="margin: 0; color: #fff;">📋 Orders Management</h2>
</div>

<!-- Orders Container -->
<div class="orders-container">

  <!-- Header -->
  <div class="orders-header">
    <h1>Orders</h1>
    <div style="color: #aaa; font-size: 0.9rem;">Total Orders: <span style="color: #d8b4fe; font-weight: 600;"><?php echo count($orders); ?></span></div>
  </div>

  <!-- Top Menu -->
  <div class="top-menu">
    <span class="active">📋 Orders</span>
    <span onclick="goToProducts()" style="cursor: pointer;">🍽️ Products</span>
    <span onclick="goToReporting()" style="cursor: pointer;">📊 Reporting</span>
  </div>

  <!-- Header Actions -->
  <div class="actions-bar">
    <span class="selected-count" id="selectedCount">0️⃣ Selected</span>

    <div class="action-dropdown">
      <button onclick="toggleActionMenu()">⚙️ Action ▾</button>
      <div class="dropdown" id="actionMenu">
        <div onclick="archiveSelected()">📦 Archived</div>
        <div class="delete" onclick="deleteSelected()">🗑️ Delete</div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <?php if (count($orders) > 0): ?>
  <table class="orders-table">
    <thead>
      <tr>
        <th style="width: 40px;"><input type="checkbox" id="selectAll" onchange="selectAllCheckboxes()"></th>
        <th>📌 Order No</th>
        <th>👤 User</th>
        <th>📅 Date</th>
        <th>💰 Total</th>
        <th>✅ Status</th>
      </tr>
    </thead>

    <tbody id="orderBody">
      <?php foreach ($orders as $order): ?>
      <tr>
        <td><input type="checkbox" onchange="updateSelection()"></td>
        <td>#<?php echo str_pad($order['id'], 3, '0', STR_PAD_LEFT); ?></td>
        <td><?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></td>
        <td><?php echo date('d M Y', strtotime($order['created_at'])); ?></td>
        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
        <td>
          <span class="status <?php echo strtolower($order['status']); ?>">
            <?php 
              $statusEmoji = [
                'draft' => '📝 Draft',
                'pending' => '⏳ Pending',
                'paid' => '✅ Paid',
                'completed' => '✔️ Completed'
              ];
              echo $statusEmoji[strtolower($order['status'])] ?? ucfirst($order['status']);
            ?>
          </span>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <div class="empty-state">
    <i class="bi bi-inbox"></i>
    <p>No orders found</p>
    <small>Orders will appear here once created</small>
  </div>
  <?php endif; ?>

</div>

<script>
function toggleActionMenu() {
  const menu = document.getElementById("actionMenu");
  menu.classList.toggle("show");
}

function updateSelection() {
  const checkboxes = document.querySelectorAll("tbody input[type='checkbox']");
  const selectAllCheckbox = document.getElementById("selectAll");
  let count = 0;

  checkboxes.forEach(cb => {
    if (cb.checked) count++;
  });

  const selected = document.getElementById("selectedCount");
  if (count === 0) {
    selected.innerText = "0️⃣ Selected";
    selectAllCheckbox.checked = false;
  } else {
    selected.innerText = count + (count === 1 ? " ✔️ Selected" : " ✅ Selected");
    selectAllCheckbox.checked = count === checkboxes.length;
  }
}

function selectAllCheckboxes() {
  const selectAll = document.getElementById("selectAll").checked;
  const checkboxes = document.querySelectorAll("tbody input[type='checkbox']");
  checkboxes.forEach(cb => cb.checked = selectAll);
  updateSelection();
}

function archiveSelected() {
  const count = document.querySelectorAll("tbody input[type='checkbox']:checked").length;
  if (count === 0) {
    alert("❌ Please select orders to archive");
    return;
  }
  alert("📦 " + count + " order(s) archived successfully");
  location.reload();
}

function deleteSelected() {
  const count = document.querySelectorAll("tbody input[type='checkbox']:checked").length;
  if (count === 0) {
    alert("❌ Please select orders to delete");
    return;
  }
  if (confirm("🗑️ Are you sure you want to delete " + count + " order(s)?")) {
    alert("✅ " + count + " order(s) deleted successfully");
    location.reload();
  }
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