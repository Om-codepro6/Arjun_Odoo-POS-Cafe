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

// Get floor and table data
$floor_query = $conn->query("
    SELECT f.id, f.name,
    (SELECT COUNT(*) FROM restaurant_tables WHERE floor_id = f.id) as table_count,
    (SELECT COUNT(*) FROM restaurant_tables WHERE floor_id = f.id AND status = 'available') as available_count
    FROM floors f
    LIMIT 1
");
$current_floor = $floor_query->fetch_assoc();
if (!$current_floor) {
    $current_floor = ['id' => 1, 'name' => 'Ground Floor', 'table_count' => 0, 'available_count' => 0];
}

// Get all tables for current floor
$tables_query = $conn->prepare("
    SELECT id, table_number, seats, status 
    FROM restaurant_tables 
    WHERE floor_id = ? 
    ORDER BY id ASC
");
$floor_id = $current_floor['id'];
$tables_query->bind_param('i', $floor_id);
$tables_query->execute();
$all_tables = $tables_query->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - Odoo Cafe POS</title>
  <style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

:root {
  --primary: #714B67;
  --primary-light: #d8b4fe;
  --primary-gradient: linear-gradient(135deg, #d8b4fe 0%, #c084fc 100%);
  --dark-bg: #0a0a0a;
  --card-bg: #0f0f0f;
  --header-bg: #1a1a1a;
  --border-color: #222;
  --text-primary: #ffffff;
  --text-secondary: #aaaaaa;
  --text-tertiary: #666666;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
  background: var(--dark-bg);
  color: var(--text-primary);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ===== HEADER ===== */
.header {
  background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
  border-bottom: 1px solid var(--border-color);
  padding: 25px 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.logo {
  font-size: 28px;
  font-weight: 900;
  background: var(--primary-gradient);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  letter-spacing: 1px;
}

.header-title {
  font-size: 14px;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 2px;
  font-weight: 600;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 30px;
}

.user-info {
  text-align: right;
}

.user-name {
  font-size: 13px;
  color: var(--text-secondary);
  margin-bottom: 3px;
}

.user-role {
  font-size: 12px;
  color: var(--text-tertiary);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.nav-icon {
  width: 40px;
  height: 40px;
  border-radius: 0;
  background: rgba(216, 180, 254, 0.1);
  border: 1px solid rgba(216, 180, 254, 0.2);
  color: var(--primary-light);
  font-size: 18px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.nav-icon:hover {
  background: rgba(216, 180, 254, 0.2);
  border-color: rgba(216, 180, 254, 0.4);
}

/* ===== NAVIGATION ===== */
.navigation {
  background: linear-gradient(90deg, #1a1a1a 0%, #0f0f0f 100%);
  border-bottom: 1px solid var(--border-color);
  padding: 0 40px;
  display: flex;
  gap: 50px;
}

.nav-item {
  padding: 18px 0;
  font-size: 14px;
  font-weight: 600;
  color: var(--text-secondary);
  cursor: pointer;
  position: relative;
  transition: all 0.3s ease;
  border: none;
  background: none;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.nav-item:hover {
  color: var(--primary-light);
}

.nav-item.active {
  color: var(--primary-light);
}

.nav-item.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  width: 100%;
  height: 3px;
  background: var(--primary-gradient);
}

/* ===== NAV DROPDOWN ===== */
.nav-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  background: #1a1a1a;
  border: 1px solid var(--border-color);
  border-top: none;
  display: none;
  flex-direction: column;
  gap: 0;
  min-width: 200px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.6);
  z-index: 100;
}

.nav-dropdown.show {
  display: flex;
}

.nav-dropdown a {
  padding: 14px 20px;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 13px;
  font-weight: 600;
  transition: all 0.3s ease;
  border-bottom: 1px solid rgba(216, 180, 254, 0.1);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.nav-dropdown a:last-child {
  border-bottom: none;
}

.nav-dropdown a:hover {
  background: rgba(216, 180, 254, 0.15);
  color: var(--primary-light);
  padding-left: 24px;
}



/* ===== MAIN CONTENT ===== */
.main-content {
  flex: 1;
  padding: 60px 40px;
  display: flex;
  flex-direction: column;
  gap: 40px;
  justify-content: center;
  align-items: center;
}

/* ===== CARDS GRID ===== */
.cards-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 50px;
  width: 100%;
  max-width: 1000px;
}

@media (max-width: 1024px) {
  .cards-grid {
    grid-template-columns: 1fr;
    gap: 40px;
  }
}

/* ===== CARD STYLES ===== */
.dashboard-card {
  background: linear-gradient(135deg, #1a1a1a 0%, #0f0f0f 100%);
  border: 1px solid var(--border-color);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
  height: 420px;
  border-radius: 30px;
}

.dashboard-card:hover {
  border-color: #444;
  box-shadow: 0 12px 40px rgba(216, 180, 254, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  transform: translateY(-4px);
}

.card-header {
  background: var(--primary-gradient);
  padding: 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.card-header h2 {
  font-size: 20px;
  font-weight: 700;
  color: #000;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 12px;
  letter-spacing: 0.5px;
}

.menu-icon {
  cursor: pointer;
  font-size: 20px;
  color: #000;
  transition: all 0.3s ease;
  padding: 5px;
}

.menu-icon:hover {
  opacity: 0.7;
}

/* Dropdown */
.card-dropdown {
  position: absolute;
  top: 60px;
  right: 30px;
  background: #1a1a1a;
  border: 1px solid var(--border-color);
  display: none;
  flex-direction: column;
  width: 200px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
  z-index: 1001;
  overflow: hidden;
}

.card-dropdown.show {
  display: flex;
}

.card-dropdown a {
  padding: 14px 18px;
  color: var(--text-secondary);
  text-decoration: none;
  font-size: 13px;
  border-bottom: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.card-dropdown a:last-child {
  border-bottom: none;
}

.card-dropdown a:hover {
  background: rgba(216, 180, 254, 0.1);
  color: var(--primary-light);
  padding-left: 20px;
}

.card-body {
  padding: 35px;
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: var(--card-bg);
}

.card-stat {
  margin-bottom: 25px;
}

.card-stat-label {
  font-size: 12px;
  color: var(--text-tertiary);
  text-transform: uppercase;
  letter-spacing: 1.5px;
  font-weight: 600;
  margin-bottom: 8px;
}

.card-stat-value {
  font-size: 28px;
  font-weight: 700;
  color: var(--primary-light);
  letter-spacing: 1px;
}

.card-stat-icon {
  font-size: 18px;
  margin-right: 6px;
}

/* ===== BUTTONS ===== */
.btn {
  border: none;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 12px;
  padding: 14px 28px;
  border-radius: 0;
}

.btn-primary {
  background: var(--primary-gradient);
  color: #000;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(216, 180, 254, 0.3);
}

.btn-secondary {
  background: transparent;
  border: 1px solid #444;
  color: var(--text-secondary);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.08);
  border-color: #666;
  color: var(--text-primary);
}

/* ===== CHECKBOX ===== */
.checkbox-group {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: var(--primary-light);
}

.checkbox-label {
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 500;
  cursor: pointer;
}

/* Status Badge */
.status-badge {
  display: inline-block;
  font-size: 12px;
  padding: 6px 12px;
  border-radius: 0;
  font-weight: 600;
}

.status-active {
  color: var(--primary-light);
  background: rgba(216, 180, 254, 0.1);
  border: 1px solid rgba(216, 180, 254, 0.2);
}

.status-inactive {
  color: var(--text-tertiary);
  background: rgba(100, 100, 100, 0.1);
  border: 1px solid rgba(100, 100, 100, 0.2);
}

/* ===== MODAL ===== */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.85);
  z-index: 2000;
  overflow-y: auto;
  padding: 40px 20px;
}

.modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content {
  background: var(--card-bg);
  width: 90%;
  max-width: 900px;
  border: 1px solid var(--border-color);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.9);
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from { transform: translateY(-50px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.modal-header {
  background: var(--primary-gradient);
  padding: 30px;
  border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
  font-size: 20px;
  font-weight: 700;
  color: #000;
  margin: 0;
  letter-spacing: 0.5px;
}

.modal-body {
  padding: 30px;
}

.modal-footer {
  padding: 25px 30px;
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  background: #1a1a1a;
  border-top: 1px solid var(--border-color);
}

/* Table Styles */
.data-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 15px;
}

.data-table thead {
  background: #1a1a1a;
}

.data-table th {
  padding: 14px;
  text-align: left;
  font-size: 11px;
  color: var(--text-tertiary);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  border-bottom: 1px solid var(--border-color);
}

.data-table td {
  padding: 14px;
  font-size: 13px;
  color: var(--text-secondary);
  border-bottom: 1px solid #1a1a1a;
}

.data-table tr:hover {
  background: rgba(216, 180, 254, 0.03);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .header {
    flex-direction: column;
    gap: 20px;
  }

  .navigation {
    padding: 0 20px;
    gap: 30px;
    overflow-x: auto;
  }

  .main-content {
    padding: 40px 20px;
  }

  .cards-grid {
    gap: 40px;
  }
}

  </style>
</head>
<body>

<!-- ===== HEADER ===== -->
<div class="header">
  <div class="header-left">
    <div class="logo">☕ ODOO</div>
    <div class="header-title">Admin Dashboard</div>
  </div>
  <div class="header-right">
    <div class="user-info">
      <div class="user-name">Welcome</div>
      <div class="user-role">Administrator</div>
    </div>
    <button class="nav-icon">⚙️</button>
    <button class="nav-icon">🔔</button>
  </div>
</div>

<!-- ===== NAVIGATION ===== -->
<div class="navigation">
  <div style="position: relative;">
    <button class="nav-item active" onclick="toggleNavDropdown(this)">📋 Orders</button>
    <div class="nav-dropdown">
      <a href="orders.php">📊 View Orders</a>
      <a href="customers.php">👥 Customer</a>
      <a href="payments_list.php">💳 Payment History</a>
    </div>
  </div>
  <div style="position: relative;">
    <button class="nav-item" onclick="toggleNavDropdown(this)">🍽️ Products</button>
    <div class="nav-dropdown">
      <a href="products_management.php">➕ Manage Products</a>
      <a href="categories.php">📂 Category</a>
    </div>
  </div>
  <div style="position: relative;">
    <button class="nav-item" onclick="toggleNavDropdown(this)">📊 Reporting</button>
    <div class="nav-dropdown">
      <a href="reporting_dashboard.php">📈 Dashboard</a>
    </div>
  </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main-content">

  <!-- CARDS GRID -->
  <div class="cards-grid">

    <!-- LEFT CARD: Open Session -->
    <div class="dashboard-card">
      <div class="card-header">
        <h2>
          <span class="card-header-icon">🔓</span>
          Open Session
        </h2>
        <div style="position: relative;">
          <div class="menu-icon" onclick="toggleDropdown(this)">⋮</div>
          <div class="card-dropdown">
            <a href="settings.php">⚙️ Settings</a>
            <a href="kitchen_display.php">👨‍🍳 Kitchen Display</a>
            <a href="customer_display.php">👥 Customer Display</a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div>
          <div class="card-stat">
            <div class="card-stat-label">
              <span class="card-stat-icon">🕒</span>Last Session Open
            </div>
            <div class="card-stat-value"><?php echo htmlspecialchars($last_open); ?></div>
          </div>
          <div class="card-stat">
            <div class="card-stat-label">
              <span class="card-stat-icon">💰</span>Last Sale Amount
            </div>
            <div class="card-stat-value"><?php echo htmlspecialchars($last_sell); ?></div>
          </div>
        </div>

        <a href="pos_terminal.php" class="btn btn-primary" style="text-align: center; text-decoration: none; display: block;">
          🔓 Open Session
        </a>
      </div>
    </div>

    <!-- RIGHT CARD: Floor Configuration -->
    <div class="dashboard-card">
      <div class="card-header">
        <h2>
          <span class="card-header-icon">🏢</span>
          Floor Configuration
        </h2>
      </div>
      <div class="card-body">
        <div>
          <div class="checkbox-group">
            <input type="checkbox" id="floorCheck" checked>
            <label for="floorCheck" class="checkbox-label">Enable Floor Plan Management</label>
          </div>

          <div style="margin-bottom: 20px; margin-top: 20px;">
            <div class="card-stat-label"><?php echo htmlspecialchars($current_floor['name']); ?> - <?php echo $current_floor['table_count']; ?> Tables</div>
            <div style="color: var(--text-secondary); font-size: 13px; margin-top: 8px;">
              Status: <span class="status-badge status-active">● Active</span>
              <span style="margin-left: 10px;"><?php echo $current_floor['available_count']; ?>/<?php echo $current_floor['table_count']; ?> Available</span>
            </div>
          </div>
        </div>

        <button class="btn btn-primary" onclick="toggleFloorModal(true)" style="width: 100%;">
          📋 Edit Floor Plan
        </button>
      </div>
    </div>

  </div>

</div>

<!-- ===== FLOOR PLAN MODAL ===== -->
<div id="floorModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>📋 Floor Configuration - <?php echo htmlspecialchars($current_floor['name']); ?></h3>
    </div>
    <div class="modal-body">
      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Floor Name</label>
        <input type="text" id="floorName" value="<?php echo htmlspecialchars($current_floor['name']); ?>" style="width: 100%; background: #1a1a1a; border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; font-size: 13px; outline: none;">
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">POS Location</label>
        <select style="width: 100%; background: #1a1a1a; border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; font-size: 13px; outline: none;">
          <option>Odoo Cafe</option>
          <option>Main Restaurant</option>
        </select>
      </div>

      <div style="margin-bottom: 30px;">
        <h4 style="font-size: 13px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; font-weight: 600;">Available Tables</h4>
        <table class="data-table">
          <thead>
            <tr>
              <th width="40"><input type="checkbox" id="masterCheck" onchange="selectAllTables(this)"></th>
              <th>Table</th>
              <th>Seats</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="tablesTableBody">
            <?php if (empty($all_tables)): ?>
            <tr><td colspan="4" style="text-align: center; padding: 20px; color: var(--text-secondary);">No tables configured yet</td></tr>
            <?php else: ?>
            <?php foreach ($all_tables as $table): ?>
            <tr data-table-id="<?php echo $table['id']; ?>">
              <td><input type="checkbox" class="table-check"></td>
              <td><?php echo htmlspecialchars($table['table_number']); ?></td>
              <td><?php echo $table['seats']; ?></td>
              <td>
                <span class="status-badge <?php echo $table['status'] === 'available' ? 'status-active' : 'status-inactive'; ?>">
                  ● <?php echo ucfirst($table['status']); ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div style="margin-bottom: 20px; display: flex; gap: 12px;">
        <input type="text" id="newTableName" placeholder="Table Name" style="flex: 1; background: #1a1a1a; border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; font-size: 13px; outline: none;">
        <input type="number" id="newTableSeats" placeholder="Seats" min="1" max="20" style="width: 80px; background: #1a1a1a; border: 1px solid var(--border-color); color: var(--text-primary); padding: 12px 16px; font-size: 13px; outline: none;">
        <button class="btn btn-primary" onclick="addTable()" style="padding: 12px 24px;">➕ Add Table</button>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="toggleFloorModal(false)">← Cancel</button>
      <button class="btn btn-primary" onclick="saveFloorChanges()">✓ Save Changes</button>
    </div>
  </div>
</div>

<script>
function toggleDropdown(element) {
  const dropdown = element.nextElementSibling;
  dropdown.classList.toggle('show');
  
  // Close other dropdowns
  document.querySelectorAll('.card-dropdown').forEach(el => {
    if (el !== dropdown) el.classList.remove('show');
  });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
  if (!event.target.closest('.card-header')) {
    document.querySelectorAll('.card-dropdown').forEach(el => el.classList.remove('show'));
  }
});

function toggleFloorModal(show) {
  const modal = document.getElementById('floorModal');
  if (show) {
    modal.classList.add('show');
    loadFloorTables();
  } else {
    modal.classList.remove('show');
  }
}

function switchNav(element) {
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  element.classList.add('active');
}

function selectAllTables(checkbox) {
  document.querySelectorAll('.table-check').forEach(el => el.checked = checkbox.checked);
}

function toggleNavDropdown(element) {
  const dropdown = element.nextElementSibling;
  dropdown.classList.toggle('show');
  
  // Close other nav dropdowns
  document.querySelectorAll('.nav-dropdown').forEach(el => {
    if (el !== dropdown) el.classList.remove('show');
  });
}

// Close nav dropdown when clicking outside
document.addEventListener('click', function(event) {
  if (!event.target.closest('div[style*="position: relative"]')) {
    document.querySelectorAll('.nav-dropdown').forEach(el => el.classList.remove('show'));
  }
});

async function loadFloorTables() {
  try {
    const response = await fetch('../api/manage_tables.php?action=get_floor_tables&floor_id=1');
    const data = await response.json();
    
    if (data.success) {
      const tbody = document.getElementById('tablesTableBody');
      tbody.innerHTML = '';
      
      data.tables.forEach(table => {
        const row = tbody.insertRow();
        row.dataset.tableId = table.id;
        row.innerHTML = `
          <td><input type="checkbox" class="table-check"></td>
          <td>${table.table_number}</td>
          <td>${table.seats}</td>
          <td><span class="status-badge ${table.status === 'available' ? 'status-active' : 'status-inactive'}">● ${table.status.charAt(0).toUpperCase() + table.status.slice(1)}</span></td>
        `;
      });
    }
  } catch (error) {
    console.error('Error loading floor tables:', error);
  }
}

async function updateOpenSessionTables() {
  try {
    const response = await fetch('../api/manage_tables.php?action=get_floor_tables&floor_id=1');
    const data = await response.json();
    
    if (data.success) {
      const openSessionList = document.getElementById('openSessionTablesList');
      openSessionList.innerHTML = '';
      
      data.tables.forEach(table => {
        const tableDiv = document.createElement('div');
        tableDiv.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(216, 180, 254, 0.1);';
        
        const statusClass = table.status === 'available' ? 'status-active' : 'status-inactive';
        const statusText = table.status.charAt(0).toUpperCase() + table.status.slice(1);
        
        tableDiv.innerHTML = `
          <span style="font-size: 13px; font-weight: 500;">${table.table_number}</span>
          <span style="font-size: 12px; color: var(--text-tertiary); margin: 0 10px;">${table.seats} seats</span>
          <span class="status-badge ${statusClass}">● ${statusText}</span>
        `;
        
        openSessionList.appendChild(tableDiv);
      });
    }
  } catch (error) {
    console.error('Error updating open session tables:', error);
  }
}

async function addTable() {
  const tableName = document.getElementById('newTableName').value.trim();
  const tableSeats = document.getElementById('newTableSeats').value.trim();
  
  if (!tableName || !tableSeats) {
    alert('Please enter table name and seats');
    return;
  }
  
  try {
    const response = await fetch('../api/manage_tables.php?action=add_table', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        floor_id: 1,
        table_number: tableName,
        seats: parseInt(tableSeats)
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      document.getElementById('newTableName').value = '';
      document.getElementById('newTableSeats').value = '';
      loadFloorTables();
      updateFloorStats();
      updateOpenSessionTables();
      alert('✓ Table added successfully!');
    } else {
      alert('Error: ' + data.error);
    }
  } catch (error) {
    console.error('Error adding table:', error);
    alert('Failed to add table');
  }
}

async function saveFloorChanges() {
  toggleFloorModal(false);
  updateFloorStats();
  updateOpenSessionTables();
}

async function updateFloorStats() {
  try {
    const response = await fetch('../api/manage_tables.php?action=get_floor_tables&floor_id=1');
    const data = await response.json();
    
    if (data.success) {
      // Update the floor configuration card
      const tableCountElements = document.querySelectorAll('.card-stat-label');
      const statusElements = document.querySelectorAll('.status-badge');
      
      const available = data.tables.filter(t => t.status === 'available').length;
      const total = data.tables.length;
      
      // Find and update the floor stats in the card
      const cards = document.querySelectorAll('.dashboard-card');
      const floorCard = Array.from(cards).find(card => 
        card.textContent.includes('Floor Configuration')
      );
      
      if (floorCard) {
        const statsDiv = floorCard.querySelector('.card-stat-label');
        if (statsDiv) {
          statsDiv.textContent = `Ground Floor - ${total} Tables`;
        }
      }
    }
  } catch (error) {
    console.error('Error updating floor stats:', error);
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  updateFloorStats();
  updateOpenSessionTables();
});
</script>

</body>
</html>
