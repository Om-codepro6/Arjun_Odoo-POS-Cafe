<?php
include '../includes/auth_check.php';

if (!isset($_SESSION['selected_table_id'])) {
    header('Location: order.php');
    exit();
}

$table_id = (int) $_SESSION['selected_table_id'];
$stmt = $conn->prepare('SELECT t.*, f.name as floor_name FROM restaurant_tables t JOIN floors f ON t.floor_id = f.id WHERE t.id = ?');
$stmt->bind_param('i', $table_id);
$stmt->execute();
$table = $stmt->get_result()->fetch_assoc();
if (!$table) {
    header('Location: floor.php');
    exit();
}

$openOrderId = null;
$stOpen = $conn->prepare("SELECT id FROM orders WHERE table_id = ? AND status IN ('pending','preparing','ready') ORDER BY id DESC LIMIT 1");
$stOpen->bind_param('i', $table_id);
$stOpen->execute();
$rowOpen = $stOpen->get_result()->fetch_assoc();
if ($rowOpen) {
    $openOrderId = (int) $rowOpen['id'];
}

$products = [];
$total = 0.00;
$cart = $_SESSION['cart'] ?? [];

if ($openOrderId) {
    $q = $conn->prepare(
        'SELECT oi.product_id AS id, oi.quantity, oi.price, p.name, p.category
         FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?'
    );
    $q->bind_param('i', $openOrderId);
    $q->execute();
    $r = $q->get_result();
    while ($row = $r->fetch_assoc()) {
        $row['subtotal'] = (int) $row['quantity'] * (float) $row['price'];
        $total += $row['subtotal'];
        $products[] = $row;
    }
} elseif (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = $conn->query('SELECT * FROM products WHERE id IN (' . $ids . ')');
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $cart[$row['id']];
        $row['subtotal'] = $row['quantity'] * $row['price'];
        $total += $row['subtotal'];
        $products[] = $row;
    }
} else {
    header('Location: order.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? 'cash';
    $amount = number_format((float) ($_POST['amount_paid'] ?? 0), 2, '.', '');

    if ($amount < $total) {
        $message = 'Amount paid must be equal or greater than the total.';
    } elseif ($openOrderId) {
        $stOpen2 = $conn->prepare("SELECT id FROM orders WHERE table_id = ? AND status IN ('pending','preparing','ready') ORDER BY id DESC LIMIT 1");
        $stOpen2->bind_param('i', $table_id);
        $stOpen2->execute();
        $verify = $stOpen2->get_result()->fetch_assoc();
        if (!$verify || (int) $verify['id'] !== $openOrderId) {
            $message = 'Order changed. Refresh and try again.';
        } else {
            $status = 'completed';
            $up = $conn->prepare('UPDATE orders SET status = ?, total_amount = ? WHERE id = ?');
            $up->bind_param('sdi', $status, $total, $openOrderId);
            $up->execute();

            $oid = $openOrderId;
            $amt = (float) $amount;
            $stmt = $conn->prepare('INSERT INTO payments (order_id, amount, payment_method) VALUES (?, ?, ?)');
            $stmt->bind_param('ids', $oid, $amt, $payment_method);
            $stmt->execute();

            $updateTable = $conn->prepare('UPDATE restaurant_tables SET status = ? WHERE id = ?');
            $statusAvailable = 'available';
            $updateTable->bind_param('si', $statusAvailable, $table_id);
            $updateTable->execute();

            unset($_SESSION['cart']);
            unset($_SESSION['selected_table_id']);
            $message = 'Payment successful. Order #' . $openOrderId . ' has been completed.';
            $openOrderId = null;
            $products = [];
            $total = 0;
        }
    } elseif ($amount >= $total) {
        $user_id = (int) $_SESSION['user_id'];
        $session_id = isset($_SESSION['pos_session_id']) ? (int) $_SESSION['pos_session_id'] : null;
        $status = 'completed';

        if ($session_id !== null) {
            $stmt = $conn->prepare('INSERT INTO orders (table_id, user_id, session_id, status, total_amount) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('iiisd', $table_id, $user_id, $session_id, $status, $total);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare('INSERT INTO orders (table_id, user_id, session_id, status, total_amount) VALUES (?, ?, NULL, ?, ?)');
            $stmt->bind_param('iisd', $table_id, $user_id, $status, $total);
            $stmt->execute();
        }
        $order_id = (int) $conn->insert_id;

        foreach ($products as $item) {
            $subtotal = (float) $item['subtotal'];
            $stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)');
            $pid = (int) $item['id'];
            $qty = (int) $item['quantity'];
            $prc = (float) $item['price'];
            $stmt->bind_param('iiidd', $order_id, $pid, $qty, $prc, $subtotal);
            $stmt->execute();
        }

        $amt = (float) $amount;
        $stmt = $conn->prepare('INSERT INTO payments (order_id, amount, payment_method) VALUES (?, ?, ?)');
        $stmt->bind_param('ids', $order_id, $amt, $payment_method);
        $stmt->execute();

        $updateTable = $conn->prepare('UPDATE restaurant_tables SET status = ? WHERE id = ?');
        $statusAvailable = 'available';
        $updateTable->bind_param('si', $statusAvailable, $table_id);
        $updateTable->execute();

        unset($_SESSION['cart']);
        unset($_SESSION['selected_table_id']);
        $message = 'Payment successful. Order #' . $order_id . ' has been completed.';
        $products = [];
        $total = 0;
    }
}

$upiAmount = number_format($total, 2, '.', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment · Cafe POS</title>
    <link rel="stylesheet" href="pos_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../auth/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="auth-shell" style="min-height: auto; padding: 24px 16px;">
    <div class="auth-card" style="max-width: 1100px; width: 100%;">
        <div class="auth-form" style="max-width: none;">
            <div class="auth-header">
                <h2>Payment — Table <?php echo htmlspecialchars($table['table_number']); ?></h2>
                <p>Confirm total and record payment.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert <?php echo strpos($message, 'successful') !== false ? 'alert-success' : 'alert-danger'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($total > 0): ?>
            <div class="row gx-4 gy-4">
                <div class="col-lg-6">
                    <div class="card p-4">
                        <h5 class="mb-3">Order details</h5>
                        <ul class="list-group mb-3">
                            <?php foreach ($products as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                        <small class="text-muted"><?php echo (int) $item['quantity']; ?> × ₹<?php echo number_format((float) $item['price'], 2); ?></small>
                                    </div>
                                    <span>₹<?php echo number_format($item['subtotal'], 2); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="d-flex justify-content-between mb-2"><span>Total</span><strong>₹<?php echo number_format($total, 2); ?></strong></div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card p-4">
                        <h5 class="mb-3">Pay</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Method</label>
                                <select name="payment_method" class="form-control" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Digital / Card</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Amount paid</label>
                                <input type="number" step="0.01" class="form-control" name="amount_paid" value="<?php echo htmlspecialchars($upiAmount); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Confirm payment</button>
                        </form>
                        <p class="mt-3 mb-0">
                            <a href="upi_qr.php?amount=<?php echo urlencode($upiAmount); ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">Show UPI QR</a>
                        </p>
                    </div>
                </div>
            </div>
            <?php elseif (strpos($message, 'successful') !== false): ?>
                <p><a href="floor.php" class="btn btn-primary">Back to floor</a></p>
            <?php endif; ?>

            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="order.php" class="btn btn-outline-secondary">Back to order</a>
                <a href="floor.php" class="btn btn-outline-primary">Floor</a>
                <a href="kitchen.php" class="btn btn-outline-dark">Kitchen</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
