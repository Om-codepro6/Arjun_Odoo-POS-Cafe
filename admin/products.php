<?php
// ============================================
// ADMIN - PRODUCT MANAGEMENT
// Add products, view menu items, update stock
// ============================================

include '../includes/auth_check.php';
include '../config/db.php';

if ($current_role !== 'admin') {
    header('Location: ../pos/index.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
        $name = trim($_POST['name'] ?? '');
        $price = number_format((float) ($_POST['price'] ?? 0), 2, '.', '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $stock = max(0, (int) ($_POST['stock'] ?? 0));

        if ($name !== '') {
            $stmt = $conn->prepare('INSERT INTO products (name, category, price, description, stock) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('ssdsi', $name, $category, $price, $description, $stock);
            if ($stmt->execute()) {
                $message = 'Product added successfully.';
            }
        }
    }

    if (isset($_POST['update_stock'])) {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $stock = max(0, (int) ($_POST['stock'] ?? 0));
        $stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
        $stmt->bind_param('ii', $stock, $productId);
        if ($stmt->execute()) {
            $message = 'Stock updated successfully.';
        }
    }
}

$products = [];
$result = $conn->query('SELECT * FROM products ORDER BY name');
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Odoo Cafe Admin</title>
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

        <?php if ($message): ?>
            <div class="admin-card" style="border-left: 4px solid var(--green); margin-bottom: 20px;">
                <p style="margin: 0; color: var(--green);"><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <div class="admin-page-header">
            <h1 class="admin-page-title">Products</h1>
            <button class="btn btn-primary" onclick="openModal()">+ Add Product</button>
        </div>

        <div class="data-table-container">
            <div class="data-table-header">
                <h3 class="data-table-title">Menu items</h3>
                <input type="text" class="form-input" placeholder="Search products..." style="max-width: 250px; margin: 0;">
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr><td colspan="6" class="text-muted">No products created yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($product['category']); ?></td>
                            <td>₹<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo (int)$product['stock']; ?></td>
                            <td>
                                <span class="badge <?php echo $product['stock'] > 0 ? 'badge-green' : 'badge-yellow'; ?>">
                                    <?php echo $product['stock'] > 0 ? 'In stock' : 'Out of stock'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="update_stock" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                    <input type="hidden" name="stock" value="<?php echo max(0, (int)$product['stock'] - 1); ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">-</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="update_stock" value="1">
                                    <input type="hidden" name="product_id" value="<?php echo (int)$product['id']; ?>">
                                    <input type="hidden" name="stock" value="<?php echo (int)$product['stock'] + 1; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">+</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

    <!-- ====== ADD PRODUCT MODAL (Popup) ====== -->
    <div class="modal-overlay" id="productModal">
        <div class="modal-box">
            <button class="modal-close" onclick="closeModal()">&times;</button>
            <h2 class="modal-title">Add New Product</h2>

            <form method="POST">
                <input type="hidden" name="action" value="add_product">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-input" name="name" placeholder="e.g. Burger" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" class="form-input" name="category" placeholder="e.g. Quick Bites" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" step="0.01" class="form-input" name="price" placeholder="95.00" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock</label>
                        <input type="number" class="form-input" name="stock" placeholder="10" value="0" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-input" name="description" placeholder="Short description...">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Discard</button>
                    <button type="submit" class="btn btn-success">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('productModal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('show');
        }

        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>
