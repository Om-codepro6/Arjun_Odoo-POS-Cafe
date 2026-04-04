<?php
include '../includes/auth_check.php';

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = '';
if ($search) {
    $search = $conn->real_escape_string($search);
    $search_query = " WHERE name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%'";
}

// Fetch customers with their total sales
$customers_result = $conn->query("
    SELECT c.id, c.name, c.email, c.phone, c.city, c.state, c.country,
           COALESCE(SUM(o.total_amount), 0) as total_sales, COUNT(o.id) as orders_count
    FROM customers c
    LEFT JOIN orders o ON c.id = o.customer_id AND o.status = 'completed'
    $search_query
    GROUP BY c.id
    ORDER BY total_sales DESC
    LIMIT 30
");

$customers = [];
while ($row = $customers_result->fetch_assoc()) {
    $customers[] = $row;
}

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $street1 = $conn->real_escape_string($_POST['street1']);
    $street2 = $conn->real_escape_string($_POST['street2']);
    $city = $conn->real_escape_string($_POST['city']);
    $state = $conn->real_escape_string($_POST['state']);
    $country = $conn->real_escape_string($_POST['country']);

    $stmt = $conn->prepare("
        INSERT INTO customers (name, email, phone, street1, street2, city, state, country, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param('ssssssss', $name, $email, $phone, $street1, $street2, $city, $state, $country);
    
    if ($stmt->execute()) {
        $message = '✅ Customer added successfully!';
        header("Refresh:2");
    } else {
        $message = '❌ Error adding customer. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>👥 Customers - Cafe POS</title>
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
.customer-container {
  max-width: 1000px;
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

/* Header Section */
.header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid rgba(255, 102, 204, 0.3);
}

.header h1 {
  font-size: 2rem;
  background: linear-gradient(90deg, #d8b4fe, #c084fc);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin: 0;
  flex: 1;
}

.new-btn {
  background: linear-gradient(135deg, #ff66cc 0%, #ff99dd 100%);
  color: #1a1a1a;
  border: none;
  padding: 12px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.new-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(255, 102, 204, 0.3);
}

.search-box {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  padding: 10px 15px;
  width: 250px;
}

.search-box input {
  background: transparent;
  border: none;
  color: #fff;
  width: 100%;
  outline: none;
}

.search-box input::placeholder {
  color: #888;
}

/* Message Alert */
.alert {
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 8px;
  background: rgba(76, 175, 80, 0.2);
  border-left: 4px solid #4caf50;
  color: #86efac;
}

/* Table Header */
.table-header {
  display: grid;
  grid-template-columns: 1.5fr 2fr 1fr;
  background: linear-gradient(90deg, rgba(216, 180, 254, 0.1), rgba(192, 132, 252, 0.1));
  padding: 15px;
  font-size: 14px;
  color: #d8b4fe;
  border-radius: 8px;
  font-weight: 600;
  border: 1px solid rgba(216, 180, 254, 0.2);
  margin-bottom: 15px;
  gap: 20px;
}

/* Customer Row */
.row {
  display: grid;
  grid-template-columns: 1.5fr 2fr 1fr;
  padding: 15px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  margin-bottom: 10px;
  transition: all 0.3s ease;
  gap: 20px;
}

.row:hover {
  background: rgba(255, 102, 204, 0.1);
  border-color: rgba(255, 102, 204, 0.3);
  transform: translateX(4px);
}

.row-name {
  font-weight: 600;
  color: #d8b4fe;
  display: flex;
  align-items: center;
  gap: 8px;
}

.row-contact {
  color: #aaa;
  font-size: 0.9rem;
  line-height: 1.6;
}

.row-sales {
  text-align: right;
  color: #86efac;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}

/* Form Section */
.form-section {
  display: none;
  animation: slideIn 0.3s ease;
}

.form-section.active {
  display: block;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 25px;
  display: grid;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  color: #d8b4fe;
  font-weight: 600;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  background: rgba(0, 0, 0, 0.3);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #fff;
  padding: 12px;
  border-radius: 6px;
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  border-color: rgba(255, 102, 204, 0.5);
  background: rgba(255, 102, 204, 0.05);
  outline: none;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
}

.form-title {
  color: #d8b4fe;
  font-weight: 600;
  font-size: 1.1rem;
  margin-top: 15px;
  margin-bottom: 10px;
}

.form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.form-actions button {
  padding: 12px 20px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.save-btn {
  background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
  color: #fff;
}

.save-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.cancel-btn {
  background: rgba(255, 255, 255, 0.1);
  color: #fff;
}

.cancel-btn:hover {
  background: rgba(255, 255, 255, 0.15);
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
  }

  .table-header,
  .row {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .row-sales {
    justify-content: flex-start;
  }
}
  </style>
</head>
<body>

<!-- Top Navigation -->
<div class="top-nav">
  <a href="index.php" class="back-link">⬅️ Back</a>
  <h2 style="margin: 0; color: #fff;">👥 Customer Management</h2>
</div>

<!-- Customer Container -->
<div class="customer-container">

  <!-- Message Alert -->
  <?php if ($message): ?>
  <div class="alert"><?php echo $message; ?></div>
  <?php endif; ?>

  <!-- Top Menu -->
  <div class="top-menu">
    <span class="active" onclick="goToOrders()" style="cursor: pointer;">📋 Orders</span>
    <span onclick="goToProducts()" style="cursor: pointer;">🍽️ Products</span>
    <span onclick="goToReporting()" style="cursor: pointer;">📊 Reporting</span>
  </div>

  <!-- LIST VIEW -->
  <div id="listView" class="active">

    <!-- Header -->
    <div class="header">
      <h1>👥 Customers</h1>
      <button class="new-btn" onclick="showForm()">➕ New Customer</button>
      <form class="search-box" method="GET" style="width: auto; flex: 1; max-width: 250px;">
        <input type="text" name="search" placeholder="🔍 Search..." value="<?php echo htmlspecialchars($search); ?>">
      </form>
    </div>

    <!-- Table -->
    <?php if (count($customers) > 0): ?>
    <div class="table-header">
      <span>👤 Name</span>
      <span>📧 Contact Info</span>
      <span style="text-align: right;">💰 Total Sales</span>
    </div>

    <?php foreach ($customers as $customer): ?>
    <div class="row">
      <div class="row-name">
        <?php echo htmlspecialchars($customer['name']); ?>
      </div>
      <div class="row-contact">
        📧 <?php echo htmlspecialchars($customer['email'] ?? 'N/A'); ?><br>
        📱 <?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?><br>
        📍 <?php echo htmlspecialchars(($customer['city'] ?? '') . ', ' . ($customer['state'] ?? '') . ' ' . ($customer['country'] ?? '')); ?>
      </div>
      <div class="row-sales">
        ₹<?php echo number_format($customer['total_sales'], 2); ?><br>
        <small><?php echo $customer['orders_count']; ?> orders</small>
      </div>
    </div>
    <?php endforeach; ?>
    
    <?php else: ?>
    <div class="empty-state">
      <i class="bi bi-people"></i>
      <p>No customers found</p>
      <small>Add a new customer to get started</small>
    </div>
    <?php endif; ?>

  </div>

  <!-- FORM VIEW -->
  <div id="formView" class="form-section">

    <div class="header">
      <h1>➕ Add New Customer</h1>
    </div>

    <form method="POST" class="form">

      <div class="form-row">
        <div class="form-group">
          <label>Full Name *</label>
          <input type="text" name="name" placeholder="e.g John Smith" required>
        </div>
        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="john@example.com">
        </div>
      </div>

      <div class="form-group">
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="+91 9898989898">
      </div>

      <div class="form-title">📍 Address</div>

      <div class="form-group">
        <label>Street Address 1</label>
        <input type="text" name="street1" placeholder="123 Main Street">
      </div>

      <div class="form-group">
        <label>Street Address 2 (Optional)</label>
        <input type="text" name="street2" placeholder="Apartment, suite, etc.">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>City</label>
          <input type="text" name="city" placeholder="City name">
        </div>
        <div class="form-group">
          <label>State/Province</label>
          <select name="state">
            <option value="">Select State</option>
            <option value="Gujarat">Gujarat</option>
            <option value="Maharashtra">Maharashtra</option>
            <option value="Delhi">Delhi</option>
            <option value="Bangalore">Bangalore</option>
            <option value="Goa">Goa</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Country</label>
        <select name="country">
          <option value="India">🇮🇳 India</option>
          <option value="USA">🇺🇸 USA</option>
          <option value="UK">🇬🇧 UK</option>
          <option value="Canada">🇨🇦 Canada</option>
          <option value="Australia">🇦🇺 Australia</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="button" class="cancel-btn" onclick="showList()">❌ Cancel</button>
        <button type="submit" name="add_customer" class="save-btn">✅ Save Customer</button>
      </div>

    </form>

  </div>

</div>

<script>
function showForm() {
  document.getElementById("listView").classList.remove("active");
  document.getElementById("formView").classList.add("active");
  window.scrollTo(0, 0);
}

function showList() {
  document.getElementById("formView").classList.remove("active");
  document.getElementById("listView").classList.add("active");
  window.scrollTo(0, 0);
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