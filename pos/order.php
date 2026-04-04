<?php
include '../includes/auth_check.php';

if (isset($_GET['table_id'])) {
    $_SESSION['selected_table_id'] = (int) $_GET['table_id'];
}

if (!isset($_SESSION['selected_table_id'])) {
    header('Location: floor.php');
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

if ($table['status'] === 'available') {
    $updateStmt = $conn->prepare('UPDATE restaurant_tables SET status = ? WHERE id = ?');
    $occupied = 'occupied';
    $updateStmt->bind_param('si', $occupied, $table_id);
    $updateStmt->execute();
    $table['status'] = 'occupied';
}

function fetchOpenOrder(mysqli $conn, int $table_id): ?array
{
    $sql = "SELECT * FROM orders WHERE table_id = ? AND status IN ('pending','preparing','ready') ORDER BY id DESC LIMIT 1";
    $st = $conn->prepare($sql);
    $st->bind_param('i', $table_id);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    return $row ?: null;
}

function refreshOrderTotal(mysqli $conn, int $order_id): void
{
    $order_id = (int) $order_id;
    $res = $conn->query('SELECT COALESCE(SUM(quantity * price), 0) AS s FROM order_items WHERE order_id = ' . $order_id);
    $sum = (float) ($res->fetch_assoc()['s'] ?? 0);
    $u = $conn->prepare('UPDATE orders SET total_amount = ? WHERE id = ?');
    $u->bind_param('di', $sum, $order_id);
    $u->execute();
}

$message = '';
$openOrder = fetchOpenOrder($conn, $table_id);
$cart = $_SESSION['cart'] ?? [];

if ($openOrder && !empty($cart)) {
    foreach ($cart as $product_id => $qty) {
        $product_id = (int) $product_id;
        $qty = max(1, (int) $qty);
        $p = $conn->query('SELECT id, price FROM products WHERE id = ' . $product_id)->fetch_assoc();
        if (!$p) {
            continue;
        }
        $price = (float) $p['price'];
        $oid = (int) $openOrder['id'];
        $st = $conn->prepare('SELECT id, quantity FROM order_items WHERE order_id = ? AND product_id = ?');
        $st->bind_param('ii', $oid, $product_id);
        $st->execute();
        $ex = $st->get_result()->fetch_assoc();
        if ($ex) {
            $nq = (int) $ex['quantity'] + $qty;
            $subtotal = $price * $nq;
            $up = $conn->prepare('UPDATE order_items SET quantity = ?, subtotal = ? WHERE id = ?');
            $eid = (int) $ex['id'];
            $up->bind_param('idi', $nq, $subtotal, $eid);
            $up->execute();
        } else {
            $subtotal = $price * $qty;
            $ins = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)');
            $ins->bind_param('iiidd', $oid, $product_id, $qty, $price, $subtotal);
            $ins->execute();
        }
    }
    unset($_SESSION['cart']);
    $cart = [];
    refreshOrderTotal($conn, (int) $openOrder['id']);
    $openOrder = fetchOpenOrder($conn, $table_id);
    $message = 'Cart merged into the open kitchen order.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_kitchen'])) {
    if ($openOrder) {
        $message = 'This table already has an open order. Use checkout or the kitchen board.';
    } elseif (empty($cart)) {
        $message = 'Add items before sending to the kitchen.';
    } else {
        $user_id = (int) $_SESSION['user_id'];
        $session_id = isset($_SESSION['pos_session_id']) ? (int) $_SESSION['pos_session_id'] : null;
        $status = 'pending';
        $total = 0.0;
        foreach ($cart as $pid => $q) {
            $pid = (int) $pid;
            $r = $conn->query('SELECT price FROM products WHERE id = ' . $pid)->fetch_assoc();
            if ($r) {
                $total += (float) $r['price'] * (int) $cart[$pid];
            }
        }
        if ($session_id !== null) {
            $ins = $conn->prepare('INSERT INTO orders (table_id, user_id, session_id, total_amount, status) VALUES (?, ?, ?, ?, ?)');
            $ins->bind_param('iiids', $table_id, $user_id, $session_id, $total, $status);
            $ins->execute();
        } else {
            $ins = $conn->prepare('INSERT INTO orders (table_id, user_id, session_id, total_amount, status) VALUES (?, ?, NULL, ?, ?)');
            $ins->bind_param('iids', $table_id, $user_id, $total, $status);
            $ins->execute();
        }
        $order_id = (int) $conn->insert_id;
        foreach ($cart as $product_id => $qty) {
            $product_id = (int) $product_id;
            $qty = (int) $cart[$product_id];
            $pr = $conn->query('SELECT price FROM products WHERE id = ' . $product_id)->fetch_assoc();
            if (!$pr) {
                continue;
            }
            $price = (float) $pr['price'];
            $subtotal = $price * $qty;
            $oi = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)');
            $oi->bind_param('iiidd', $order_id, $product_id, $qty, $price, $subtotal);
            $oi->execute();
        }
        unset($_SESSION['cart']);
        $cart = [];
        refreshOrderTotal($conn, $order_id);
        $openOrder = fetchOpenOrder($conn, $table_id);
        $message = 'Order sent to kitchen.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = max(1, (int) $_POST['quantity']);
    if ($quantity > 0) {
        if ($openOrder) {
            $oid = (int) $openOrder['id'];
            $pr = $conn->prepare('SELECT price FROM products WHERE id = ?');
            $pr->bind_param('i', $product_id);
            $pr->execute();
            $prow = $pr->get_result()->fetch_assoc();
            if ($prow) {
                $price = (float) $prow['price'];
                $st = $conn->prepare('SELECT id, quantity FROM order_items WHERE order_id = ? AND product_id = ?');
                $st->bind_param('ii', $oid, $product_id);
                $st->execute();
                $ex = $st->get_result()->fetch_assoc();
                if ($ex) {
                    $nq = (int) $ex['quantity'] + $quantity;
                    $subtotal = $price * $nq;
                    $up = $conn->prepare('UPDATE order_items SET quantity = ?, subtotal = ? WHERE id = ?');
                    $eid = (int) $ex['id'];
                    $up->bind_param('idi', $nq, $subtotal, $eid);
                    $up->execute();
                } else {
                    $subtotal = $price * $quantity;
                    $ins = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)');
                    $ins->bind_param('iiidd', $oid, $product_id, $quantity, $price, $subtotal);
                    $ins->execute();
                }
                refreshOrderTotal($conn, $oid);
                $openOrder = fetchOpenOrder($conn, $table_id);
                $message = 'Added to kitchen order.';
            }
        } else {
            if (isset($cart[$product_id])) {
                $cart[$product_id] += $quantity;
            } else {
                $cart[$product_id] = $quantity;
            }
            $_SESSION['cart'] = $cart;
            $message = 'Product added to order.';
        }
    }
}

if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove = (int) $_GET['remove'];
    if ($openOrder) {
        $oid = (int) $openOrder['id'];
        $del = $conn->prepare('DELETE FROM order_items WHERE order_id = ? AND product_id = ?');
        $del->bind_param('ii', $oid, $remove);
        $del->execute();
        refreshOrderTotal($conn, $oid);
        $cnt = (int) ($conn->query('SELECT COUNT(*) AS c FROM order_items WHERE order_id = ' . $oid)->fetch_assoc()['c'] ?? 0);
        if ($cnt === 0) {
            $conn->query('DELETE FROM orders WHERE id = ' . $oid);
            $av = 'available';
            $u = $conn->prepare('UPDATE restaurant_tables SET status = ? WHERE id = ?');
            $u->bind_param('si', $av, $table_id);
            $u->execute();
            header('Location: floor.php');
            exit();
        }
        $openOrder = fetchOpenOrder($conn, $table_id);
    } elseif (isset($cart[$remove])) {
        unset($cart[$remove]);
        $_SESSION['cart'] = $cart;
    }
    header('Location: order.php');
    exit();
}

if (isset($_GET['clear'])) {
    if (!$openOrder) {
        unset($_SESSION['cart']);
    }
    header('Location: order.php');
    exit();
}

$products = [];
$result = $conn->query('SELECT * FROM products ORDER BY name');
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

$cart_items = [];
$total = 0.00;
if ($openOrder) {
    $oid = (int) $openOrder['id'];
    $q = $conn->query(
        'SELECT oi.*, p.name, p.category FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ' . $oid
    );
    while ($row = $q->fetch_assoc()) {
        $row['subtotal'] = (int) $row['quantity'] * (float) $row['price'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
} elseif (!empty($cart)) {
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $result = $conn->query('SELECT * FROM products WHERE id IN (' . $ids . ')');
    while ($row = $result->fetch_assoc()) {
        $row['quantity'] = $cart[$row['id']];
        $row['subtotal'] = $row['quantity'] * $row['price'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order · Table <?php echo htmlspecialchars($table['table_number']); ?></title>
    <link rel="stylesheet" href="pos_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../auth/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="auth-shell" style="min-height: auto; padding: 24px 16px;">
    <div class="auth-card" style="max-width: 1200px; width: 100%;">
        <div class="auth-form" style="max-width: none;">
            <div class="auth-header">
                <h2>Table <?php echo htmlspecialchars($table['table_number']); ?> — <?php echo htmlspecialchars($table['floor_name']); ?></h2>
                <p>Add items, send to kitchen, or checkout.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($openOrder): ?>
                <div class="alert alert-warning">
                    Kitchen order <strong>#<?php echo (int) $openOrder['id']; ?></strong> — status: <strong><?php echo htmlspecialchars($openOrder['status']); ?></strong>.
                    <a href="customer_display.php?order_id=<?php echo (int) $openOrder['id']; ?>" target="_blank" rel="noopener" class="alert-link">Customer display</a>
                </div>
            <?php endif; ?>

            <div class="row gx-4 gy-4">
                <div class="col-lg-7">
                    <div class="card p-4">
                        <h5 class="mb-3">Products</h5>
                        <div class="row gx-3 gy-3">
                            <?php if (empty($products)): ?>
                                <p class="text-muted">No products — add some under Product management.</p>
                            <?php endif; ?>
                            <?php foreach ($products as $product): ?>
                                <div class="col-sm-6">
                                    <div class="pos-card p-3">
                                        <h6><?php echo htmlspecialchars($product['name']); ?></h6>
                                        <p class="text-muted mb-2"><?php echo htmlspecialchars($product['category']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong>₹<?php echo number_format($product['price'], 2); ?></strong>
                                            <form method="POST" class="d-flex gap-2 align-items-center">
                                                <input type="hidden" name="add_product" value="1">
                                                <input type="hidden" name="product_id" value="<?php echo (int) $product['id']; ?>">
                                                <input type="number" name="quantity" value="1" min="1" class="form-control form-control-sm" style="width: 72px;">
                                                <button type="submit" class="btn btn-primary btn-sm">Add</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card p-4">
                        <h5 class="mb-3"><?php echo $openOrder ? 'Kitchen order' : 'Cart'; ?></h5>
                        <?php if (empty($cart_items)): ?>
                            <p class="text-muted">Cart is empty.</p>
                        <?php else: ?>
                            <div class="list-group mb-3">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                            <small class="text-muted"><?php echo (int) $item['quantity']; ?> × ₹<?php echo number_format((float) $item['price'], 2); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span>₹<?php echo number_format($item['subtotal'], 2); ?></span><br>
                                            <a href="order.php?remove=<?php echo (int) $item['product_id']; ?>" class="text-danger small">Remove</a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>₹<?php echo number_format($total, 2); ?></strong></div>
                            <div class="d-grid gap-2">
                                <?php if ($openOrder): ?>
                                    <a href="payments.php" class="btn btn-success">Proceed to payment</a>
                                <?php else: ?>
                                    <form method="POST" class="d-grid gap-2">
                                        <button type="submit" name="send_kitchen" value="1" class="btn btn-warning text-dark">Send to kitchen</button>
                                        <a href="payments.php" class="btn btn-success">Checkout (skip kitchen)</a>
                                    </form>
                                    <a href="order.php?clear=1" class="btn btn-outline-danger">Clear cart</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex flex-wrap gap-2">
                <a href="floor.php" class="btn btn-outline-secondary">Floor</a>
                <a href="kitchen.php" class="btn btn-outline-dark">Kitchen board</a>
                <a href="products.php" class="btn btn-outline-primary">Products</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
