<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>👥 Customers - Cafe POS</title>
  <style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  background: url('https://images.unsplash.com/photo-1559496417-e7f25cb247f3?auto=format&fit=crop&w=1600&q=80') center/cover no-repeat fixed;
  color: #e5e5e5;
  font-family: "Segoe UI", sans-serif;
  min-height: 100vh;
}

body::before {
  content: '';
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  pointer-events: none;
  z-index: -1;
}

/* Top Navigation Bar */
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

.back-btn {
  color: #fff;
  text-decoration: none;
  font-size: 1.2rem;
  transition: color 0.3s ease;
  cursor: pointer;
}

.back-btn:hover {
  color: #ff66cc;
}

/* Container */
.customer-container {
  max-width: 900px;
  margin: 30px auto;
  background: linear-gradient(145deg, rgba(26, 26, 26, 0.95), rgba(40, 40, 40, 0.95));
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 25px;
  backdrop-filter: blur(10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

/* Top Menu */
.top-menu {
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #333;
  display: flex;
  gap: 25px;
}

.top-menu span {
  margin-right: 0;
  color: #888;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
}

.top-menu span:hover {
  color: #fff;
}

.top-menu .active {
  color: #fff;
  border-bottom: 2px solid #d8b4fe;
  margin-bottom: -11px;
  padding-bottom: 9px;
}

/* Header */
.header {
  display: flex;
  align-items: center;
  margin: 20px 0 30px 0;
  gap: 15px;
}

.new-btn {
  background: linear-gradient(135deg, #ff66cc 0%, #ff99dd 100%);
  border: none;
  padding: 10px 18px;
  margin-right: 0;
  cursor: pointer;
  border-radius: 6px;
  color: #1a1a1a;
  font-weight: 600;
  transition: all 0.3s ease;
}

.new-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255, 102, 204, 0.3);
}

.header h2 {
  font-size: 1.8rem;
  background: linear-gradient(90deg, #d8b4fe, #c084fc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  flex: 1;
}

.search-box {
  margin-left: auto;
}

.search-box input {
  padding: 10px 15px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  border-radius: 6px;
  width: 250px;
  transition: all 0.3s ease;
}

.search-box input:focus {
  border-color: rgba(255, 102, 204, 0.5);
  background: rgba(255, 102, 204, 0.05);
  outline: none;
}

.search-box input::placeholder {
  color: #888;
}

/* TABLE STYLES */
.table-header {
  display: grid;
  grid-template-columns: 1.5fr 2fr 1fr;
  padding: 15px;
  background: linear-gradient(90deg, rgba(216, 180, 254, 0.1), rgba(192, 132, 252, 0.1));
  color: #d8b4fe;
  border-radius: 8px;
  font-weight: 600;
  border: 1px solid rgba(216, 180, 254, 0.2);
  margin-bottom: 15px;
  font-size: 14px;
}

.row {
  display: grid;
  grid-template-columns: 1.5fr 2fr 1fr;
  padding: 15px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  margin-bottom: 10px;
  transition: all 0.3s ease;
}

.row:hover {
  background: rgba(255, 102, 204, 0.1);
  border-color: rgba(255, 102, 204, 0.3);
  transform: translateX(4px);
}

.row span:first-child {
  color: #d8b4fe;
  font-weight: 600;
}

.row span:nth-child(2) {
  color: #aaa;
  font-size: 0.9rem;
  line-height: 1.5;
}

.row span:last-child {
  text-align: right;
  color: #86efac;
  font-weight: 500;
}

/* FORM VIEW */
.form-view {
  display: none;
}

.form-view.active {
  display: block;
}

.form {
  display: flex;
  flex-direction: column;
  gap: 15px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 25px;
}

.form h3 {
  color: #d8b4fe;
  font-weight: 600;
  margin: 15px 0 10px 0;
  font-size: 1.1rem;
}

.form input,
.form select {
  padding: 12px;
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  border-radius: 6px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form input:focus,
.form select:focus {
  border-color: rgba(255, 102, 204, 0.5);
  background: rgba(255, 102, 204, 0.05);
  outline: none;
}

.form input::placeholder {
  color: #888;
}

/* Form Grid */
.grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.save {
  background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
  border: none;
  padding: 12px 20px;
  cursor: pointer;
  color: #fff;
  font-weight: 600;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.save:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.discard-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 12px 20px;
  cursor: pointer;
  color: #fff;
  font-weight: 600;
  border-radius: 6px;
  transition: all 0.3s ease;
}

.discard-btn:hover {
  background: rgba(255, 255, 255, 0.15);
}

/* Hidden */
.hidden {
  display: none;
}

/* Responsive */
@media (max-width: 768px) {
  .customer-container {
    margin: 15px;
    padding: 15px;
  }

  .header {
    flex-wrap: wrap;
  }

  .search-box {
    width: 100%;
    margin-left: 0;
    margin-top: 10px;
  }

  .search-box input {
    width: 100%;
  }

  .table-header,
  .row {
    grid-template-columns: 1fr;
  }

  .top-menu {
    gap: 15px;
  }

  .grid {
    grid-template-columns: 1fr;
  }

  .row span:last-child {
    text-align: left;
  }
}
  </style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
  <span class="back-btn" onclick="goBack()">⬅️ Back</span>
  <h2 style="margin: 0; color: #fff; flex: 1;">👥 Customers</h2>
</div>

<!-- Customer Container -->
<div class="customer-container">

  <!-- Top Menu -->
  <div class="top-menu">
    <span class="active" onclick="goToOrders()">📋 Orders</span>
    <span onclick="goToProducts()">🍽️ Products</span>
    <span onclick="goToReporting()">📊 Reporting</span>
  </div>

  <!-- LIST VIEW -->
  <div id="listView">

    <!-- Header -->
    <div class="header">
      <button class="new-btn" onclick="showForm()">➕ New</button>
      <h2>Customers</h2>
      <div class="search-box">
        <input type="text" placeholder="🔍 Search Customer...">
      </div>
    </div>

    <!-- Table -->
    <div class="table-header">
      <span>👤 Name</span>
      <span>📧 Contact</span>
      <span>💰 Total Sales</span>
    </div>

    <div class="row">
      <span>Eric Smith</span>
      <span>
        eric@odoo.com<br>
        +91 9898989898
      </span>
      <span>₹2000</span>
    </div>

    <div class="row">
      <span>John Doe</span>
      <span>
        john@example.com<br>
        +91 9876543210
      </span>
      <span>₹4500</span>
    </div>

    <div class="row">
      <span>Sarah Johnson</span>
      <span>
        sarah@example.com<br>
        +91 9123456789
      </span>
      <span>₹3200</span>
    </div>

  </div>

  <!-- FORM VIEW -->
  <div id="formView" class="form-view">

    <div class="header">
      <h2>➕ Add New Customer</h2>
    </div>

    <form class="form">

      <input type="text" placeholder="e.g Eric Smith" required>
      <input type="email" placeholder="eric@odoo.com" required>
      <input type="text" placeholder="+91 9898989898" required>

      <h3>📍 Address</h3>

      <input type="text" placeholder="Street 1">
      <input type="text" placeholder="Street 2 (Optional)">

      <div class="grid">
        <input type="text" placeholder="City">
        <select>
          <option value="">Select State</option>
          <option>Gujarat</option>
          <option>Maharashtra</option>
          <option>Delhi</option>
          <option>Bangalore</option>
        </select>
      </div>

      <select>
        <option value="">Select Country</option>
        <option>🇮🇳 India</option>
        <option>🇺🇸 USA</option>
        <option>🇬🇧 UK</option>
        <option>🇨🇦 Canada</option>
      </select>

      <div class="form-actions">
        <button type="button" class="discard-btn" onclick="showList()">❌ Discard</button>
        <button type="submit" class="save">✅ Save</button>
      </div>

    </form>

  </div>

</div>

<script>
function showForm() {
  document.getElementById("listView").style.display = "none";
  document.getElementById("formView").style.display = "block";
  document.getElementById("formView").classList.add("active");
  window.scrollTo(0, 0);
}

function showList() {
  document.getElementById("formView").style.display = "none";
  document.getElementById("formView").classList.remove("active");
  document.getElementById("listView").style.display = "block";
  window.scrollTo(0, 0);
}

function goBack() {
  window.history.back();
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