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

.card-header-icon {
  font-size: 24px;
}

.menu-icon {
  cursor: pointer;
  font-size: 20px;
  color: #000;
  transition: all 0.3s ease;
  padding: 5px;
  border-radius: 0;
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
  border-radius: 0;
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
  margin-bottom: 20px;
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
  font-size: 20px;
  margin-right: 8px;
}

/* ===== BUTTONS ===== */
.btn {
  border: none;
  border-radius: 0;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-size: 12px;
  padding: 14px 28px;
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

.btn-link {
  background: transparent;
  border: 1px solid rgba(216, 180, 254, 0.3);
  color: var(--text-primary);
  padding: 8px 14px;
  font-size: 11px;
}

.btn-link:hover {
  background: rgba(216, 180, 254, 0.1);
  border-color: rgba(216, 180, 254, 0.5);
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
  border-radius: 0;
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

/* ===== FORMS ===== */
.form-group {
  margin-bottom: 20px;
}

.form-label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  background: #1a1a1a;
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  padding: 12px 16px;
  font-size: 13px;
  outline: none;
  transition: all 0.3s ease;
}

.form-input:focus {
  border-color: var(--primary-light);
  background: rgba(216, 180, 254, 0.05);
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
  <button class="nav-item active" onclick="switchNav(this)">📋 Orders</button>
  <button class="nav-item" onclick="switchNav(this)">🍽️ Products</button>
  <button class="nav-item" onclick="switchNav(this)">📊 Reporting</button>
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
            <a href="kitchen.php">👨‍🍳 Kitchen Display</a>
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

        <a href="<?php echo $has_open_session ? '../auth/end_session.php' : '../auth/pos.php'; ?>" class="btn btn-primary" style="text-align: center; text-decoration: none;">
          <?php echo $has_open_session ? '🔒 Close Session' : '🔓 Open Session'; ?>
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
        <div class="checkbox-group">
          <input type="checkbox" id="floorCheck" checked>
          <label for="floorCheck" class="checkbox-label">Enable Floor Plan Management</label>
        </div>

        <div style="margin-bottom: 20px;">
          <div class="card-stat-label">Ground Floor - 8 Tables</div>
          <div style="color: var(--text-secondary); font-size: 13px; margin-top: 8px;">
            Status: <span style="color: var(--primary-light);">● Active</span>
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
      <h3>📋 Floor Configuration - Ground Floor</h3>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Floor Name</label>
        <input type="text" class="form-input" value="Ground Floor" placeholder="Enter floor name">
      </div>

      <div style="margin-bottom: 20px;">
        <label class="form-label">POS Location</label>
        <select class="form-input">
          <option>Odoo Cafe</option>
          <option>Main Restaurant</option>
        </select>
      </div>

      <div style="margin-bottom: 20px;">
        <h4 style="font-size: 13px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; font-weight: 600;">Available Tables</h4>
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #1a1a1a; border-bottom: 1px solid var(--border-color);">
              <th style="padding: 12px; text-align: left; font-size: 11px; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                <input type="checkbox" id="masterCheck" onchange="selectAllTables(this)">
              </th>
              <th style="padding: 12px; text-align: left; font-size: 11px; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Table</th>
              <th style="padding: 12px; text-align: left; font-size: 11px; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Seats</th>
              <th style="padding: 12px; text-align: left; font-size: 11px; color: var(--text-tertiary); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Status</th>
            </tr>
          </thead>
          <tbody id="tableList">
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 12px;"><input type="checkbox" class="table-check"></td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">Table 101</td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">5</td>
              <td style="padding: 12px;"><span style="font-size: 11px; color: var(--primary-light); background: rgba(216, 180, 254, 0.1); padding: 4px 10px;">Active</span></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 12px;"><input type="checkbox" class="table-check"></td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">Table 102</td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">8</td>
              <td style="padding: 12px;"><span style="font-size: 11px; color: #888; background: rgba(100, 100, 100, 0.1); padding: 4px 10px;">Inactive</span></td>
            </tr>
            <tr style="border-bottom: 1px solid var(--border-color);">
              <td style="padding: 12px;"><input type="checkbox" class="table-check"></td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">Table 103</td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">4</td>
              <td style="padding: 12px;"><span style="font-size: 11px; color: var(--primary-light); background: rgba(216, 180, 254, 0.1); padding: 4px 10px;">Active</span></td>
            </tr>
            <tr>
              <td style="padding: 12px;"><input type="checkbox" class="table-check"></td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">Table 104</td>
              <td style="padding: 12px; color: var(--text-secondary); font-size: 13px;">2</td>
              <td style="padding: 12px;"><span style="font-size: 11px; color: var(--primary-light); background: rgba(216, 180, 254, 0.1); padding: 4px 10px;">Active</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="toggleFloorModal(false)">← Cancel</button>
      <button class="btn btn-primary" onclick="toggleFloorModal(false)">✓ Save Changes</button>
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
</script>

</body>
</html>
