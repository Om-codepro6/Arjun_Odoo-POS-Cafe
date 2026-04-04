<?php
include '../includes/auth_check.php';

// Get last session data
$last_session_query = $conn->prepare("SELECT session_start, closing_amount FROM pos_sessions WHERE user_id = ? ORDER BY session_start DESC LIMIT 1");
$last_session_query->bind_param('i', $current_user_id);
$last_session_query->execute();
$last_session = $last_session_query->get_result()->fetch_assoc();

$last_open = $last_session ? date('m/d/Y', strtotime($last_session['session_start'])) : 'N/A';
$last_sell = $last_session ? '$' . number_format($last_session['closing_amount'], 2) : '$0.00';

// Check if session is open
$open_session_query = $conn->prepare("SELECT id FROM pos_sessions WHERE user_id = ? AND session_end IS NULL");
$open_session_query->bind_param('i', $current_user_id);
$open_session_query->execute();
$has_open_session = $open_session_query->get_result()->num_rows > 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS Dashboard - Cafe POS</title>
  <style>
body {
  margin: 0;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: url('https://images.unsplash.com/photo-1559054664-1365ee7b4b1c?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
  color: #fff;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
}

body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  pointer-events: none;
  z-index: -1;
}

/* Top Menu */
.top-menu {
  padding: 20px 40px;
  border-bottom: 1px solid #333;
  background: rgba(26, 26, 26, 0.9);
  backdrop-filter: blur(10px);
  position: relative;
  z-index: 10;
}

.menu-items {
  display: flex;
  gap: 30px;
}

.menu-items span {
  color: #aaa;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  position: relative;
}

.menu-items span:hover {
  color: #fff;
}

.menu-items span.active {
  color: #fff;
}

.menu-items span.active::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #d8b4fe, #c084fc);
  border-radius: 1px;
}

/* Submenu */
.submenu {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: linear-gradient(145deg, #1a1a1a 0%, #2a2a2a 100%);
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.1);
  display: none;
  z-index: 1000;
  min-width: 300px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.submenu.show {
  display: block;
}

.submenu h3 {
  margin: 0 0 20px 0;
  color: #d8b4fe;
  font-size: 1.5rem;
  text-align: center;
}

.submenu .options {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.submenu .option {
  background: rgba(255, 255, 255, 0.1);
  padding: 12px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  color: #fff;
  text-align: center;
  font-weight: 500;
}

.submenu .option:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

/* Overlay */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: none;
  z-index: 999;
}

.overlay.show {
  display: block;
}

/* Container */
.container {
  flex: 1;
  padding: 60px 40px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Card */
.pos-card {
  background: linear-gradient(145deg, #1a1a1a 0%, #2a2a2a 100%);
  padding: 30px;
  width: 450px;
  border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
  position: relative;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Header */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.card-header h2 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 600;
  background: linear-gradient(90deg, #d8b4fe, #c084fc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.menu-icon {
  cursor: pointer;
  font-size: 24px;
  color: #aaa;
  transition: color 0.3s ease;
}

.menu-icon:hover {
  color: #fff;
}

/* Dropdown */
.dropdown {
  position: absolute;
  top: 60px;
  right: 30px;
  background: linear-gradient(145deg, #2a2a2a 0%, #3a3a3a 100%);
  border-radius: 8px;
  display: flex;
  flex-direction: column;
  width: 180px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  overflow: hidden;
}

.dropdown a {
  padding: 12px 16px;
  cursor: pointer;
  color: #ccc;
  text-decoration: none;
  transition: all 0.3s ease;
}

.dropdown a:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.show {
  display: flex;
}

/* Body */
.card-body {
  margin-top: 20px;
}

.card-body p {
  color: #bbb;
  margin: 8px 0;
  font-size: 0.95rem;
}

/* Button */
.open-btn {
  margin-top: 20px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #d8b4fe 0%, #c084fc 100%);
  border: none;
  border-radius: 8px;
  color: #1a1a1a;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(216, 180, 254, 0.3);
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.open-btn:hover {
  background: linear-gradient(135deg, #c084fc 0%, #a855f7 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(216, 180, 254, 0.4);
}

.open-btn:active {
  transform: translateY(0);
}
  </style>
</head>
<body>

<!-- Top Menu -->
<div class="top-menu">
  <div class="menu-items">
    <span class="active" onclick="showSubmenu('orders')">📋 Orders</span>
    <span onclick="showSubmenu('products')">🍽️ Products</span>
    <span onclick="showSubmenu('reporting')">📊 Reporting</span>
  </div>
</div>

<!-- Overlay -->
<div class="overlay" id="overlay" onclick="hideSubmenu()"></div>

<!-- Submenu -->
<div class="submenu" id="submenu">
  <h3 id="submenu-title">Orders</h3>
  <div class="options" id="submenu-options">
    <!-- Options will be populated by JS -->
  </div>
</div>

<!-- Main Container -->
<div class="container">

  <!-- POS Card -->
  <div class="pos-card">
    
    <div class="card-header">
      <h2>☕ Odoo Cafe</h2>

      <!-- 3 dots menu -->
      <div class="menu-icon" onclick="toggleMenu()">⋮</div>

      <!-- Dropdown -->
      <div class="dropdown" id="dropdownMenu">
        <a href="settings.php">⚙️ Setting</a>
        <a href="kitchen.php">👨‍🍳 Kitchen Display</a>
        <a href="customer_display.php">👥 Customer Display</a>
      </div>
    </div>

    <div class="card-body">
      <p>🕒 Last open: <?php echo htmlspecialchars($last_open); ?></p>
      <p>💰 Last Sell: <?php echo htmlspecialchars($last_sell); ?></p>

      <a href="<?php echo $has_open_session ? '../auth/end_session.php' : '../auth/pos.php'; ?>" class="open-btn">
        <?php echo $has_open_session ? '🔒 Close Session' : '🔓 Open Session'; ?>
      </a>
    </div>

  </div>

</div>

<script>
function toggleMenu() {
  const menu = document.getElementById("dropdownMenu");
  menu.classList.toggle("show");
}

function showSubmenu(type) {
  const submenu = document.getElementById("submenu");
  const title = document.getElementById("submenu-title");
  const options = document.getElementById("submenu-options");
  const overlay = document.getElementById("overlay");

  // Set title
  title.textContent = type.charAt(0).toUpperCase() + type.slice(1);

  // Clear previous options
  options.innerHTML = '';

  // Populate options based on type
  if (type === 'orders') {
    options.innerHTML = `
      <a href="orders.php" class="option">📝 Order</a>
      <a href="payments_list.php" class="option">💳 Payment</a>
      <a href="customers_management.php" class="option">👥 Customer</a>
    `;
  } else if (type === 'products') {
    options.innerHTML = `
      <a href="order.php" class="option">➕ Add to Order</a>
      <a href="products_management.php" class="option">🍔 Add Product</a>
      <a href="../admin/products.php" class="option">📂 Category</a>
    `;
  } else if (type === 'reporting') {
    options.innerHTML = `
      <a href="../admin/" class="option">📈 Dashboard</a>
    `;
  }

  // Show submenu and overlay
  submenu.classList.add("show");
  overlay.classList.add("show");
}

function hideSubmenu() {
  const submenu = document.getElementById("submenu");
  const overlay = document.getElementById("overlay");
  submenu.classList.remove("show");
  overlay.classList.remove("show");
}
</script>

</body>
</html>
