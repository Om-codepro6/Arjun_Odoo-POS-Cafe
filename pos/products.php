<?php
include '../auth/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_product') {
        $name = trim($_POST['name']);
        $price = number_format((float) $_POST['price'], 2, '.', '');
        $category = trim($_POST['category']);
        $stock = (int) $_POST['stock'];
        if ($name !== '') {
            $stmt = $conn->prepare('INSERT INTO products (name, category, price, stock) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssdi', $name, $category, $price, $stock);
            $stmt->execute();
            $message = 'Product added successfully.';
        }
    }

    if (isset($_POST['update_stock'])) {
        $product_id = (int) $_POST['product_id'];
        $stock = (int) $_POST['stock'];
        $stmt = $conn->prepare('UPDATE products SET stock = ? WHERE id = ?');
        $stmt->bind_param('ii', $stock, $product_id);
        $stmt->execute();
        $message = 'Product stock updated.';
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
    <title>Cafe POS - Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../auth/style.css">
</head>
<body>
    <div class="auth-shell">
        <div class="auth-card" style="max-width: 1100px;">
            <div class="auth-side" style="background: linear-gradient(180deg, rgba(137, 65, 23, 0.95), rgba(30, 41, 59, 0.92));">
                <div>
                    <div class="brand-badge">Product Management</div>
                    <h1>Menu & Pricing</h1>
                    <p>Create and manage your cafe product catalog. Update pricing, categories, and availability in real time.</p>
                </div>
                <div class="auth-side-footer">A clean product list ensures fast order creation and professional checkout flow.</div>
            </div>
            <div class="auth-form">
                <div class="auth-header">
                    <h2>Add / Manage Products</h2>
                    <p>Keep your menu items organized and ready for order entry.</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="row gx-4 gy-4">
                    <div class="col-lg-5">
                        <div class="card p-4">
                            <h5 class="mb-3">Add New Product</h5>
                            <form method="POST">
                                <input type="hidden" name="action" value="add_product">
                                <div class="form-group mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Category</label>
                                    <input type="text" class="form-control" name="category" placeholder="Beverage, Dessert, Snack">
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control" name="price" placeholder="25.00" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" class="form-control" name="stock" placeholder="10" value="0" required>
                                </div>
                                <button class="btn btn-primary">Save Product</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="card p-4">
                            <h5 class="mb-3">Current Products</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($products)): ?>
                                            <tr><td colspan="5" class="text-muted">No products yet.</td></tr>
                                        <?php endif; ?>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                <td><?php echo htmlspecialchars($product['category']); ?></td>
                                                <td><?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo (int) $product['stock']; ?> units</td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="update_stock" value="1">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                                        <input type="hidden" name="stock" value="<?php echo max(0, (int) $product['stock'] - 1); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning">
                                                            Edit Stock
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-between gap-2 flex-column flex-md-row">
                    <a href="floor.php" class="btn btn-outline-secondary">Back to Floor</a>
                    <a href="../auth/dashboard.php" class="btn btn-outline-primary">Return to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>