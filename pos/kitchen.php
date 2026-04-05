<?php
include '../auth/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['advance_order'])) {
    $oid = (int) $_POST['order_id'];
    $st = $conn->prepare("SELECT id, status FROM orders WHERE id = ? AND status IN ('pending','preparing','ready')");
    $st->bind_param('i', $oid);
    $st->execute();
    $o = $st->get_result()->fetch_assoc();
    if ($o) {
        $next = null;
        if ($o['status'] === 'pending') {
            $next = 'preparing';
        } elseif ($o['status'] === 'preparing') {
            $next = 'ready';
        }
        if ($next !== null) {
            $up = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $up->bind_param('si', $next, $oid);
            $up->execute();
        }
    }
    header('Location: kitchen.php');
    exit();
}

function loadBoard(mysqli $conn, string $status): array
{
    $list = [];
    $s = $conn->prepare(
        "SELECT o.id, o.table_id, o.status, o.created_at, t.table_number, f.name AS floor_name
         FROM orders o
         JOIN restaurant_tables t ON t.id = o.table_id
         JOIN floors f ON f.id = t.floor_id
         WHERE o.status = ?
         ORDER BY o.created_at ASC"
    );
    $s->bind_param('s', $status);
    $s->execute();
    $r = $s->get_result();
    while ($row = $r->fetch_assoc()) {
        $items = [];
        $q = $conn->query(
            'SELECT oi.quantity, p.name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ' . (int) $row['id']
        );
        while ($i = $q->fetch_assoc()) {
            $items[] = $i;
        }
        $row['items'] = $items;
        $list[] = $row;
    }
    return $list;
}

$colCook = loadBoard($conn, 'pending');
$colPrep = loadBoard($conn, 'preparing');
$colReady = loadBoard($conn, 'ready');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display · Cafe POS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #111; color: #eee; min-height: 100vh; }
        .kcol { background: #1a1a1a; border-radius: 12px; padding: 16px; min-height: 320px; border: 1px solid #333; }
        .kcol h2 { font-size: 1rem; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 16px; }
        .kcol.cook h2 { color: #fbbf24; }
        .kcol.prep h2 { color: #38bdf8; }
        .kcol.ready h2 { color: #4ade80; }
        .kticket {
            background: #252525;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            border-left: 4px solid #f59e0b;
        }
        .kticket.prep { border-left-color: #38bdf8; }
        .kticket small { color: #9ca3af; }
        .kticket ul { margin: 8px 0 0; padding-left: 18px; }
        .kticket.ready { border-left-color: #4ade80; }
    </style>
</head>
<body class="p-3 p-md-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="h3 mb-0">Kitchen</h1>
        <div class="d-flex gap-2">
            <a href="floor.php" class="btn btn-outline-light btn-sm">Floor</a>
            <a href="index.php" class="btn btn-outline-secondary btn-sm">POS home</a>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="kcol cook">
                <h2>To cook</h2>
                <?php foreach ($colCook as $o): ?>
                    <div class="kticket">
                        <strong>#<?php echo (int) $o['id']; ?></strong> · Table <?php echo htmlspecialchars($o['table_number']); ?>
                        <small class="d-block"><?php echo htmlspecialchars($o['floor_name']); ?></small>
                        <ul>
                            <?php foreach ($o['items'] as $it): ?>
                                <li><?php echo (int) $it['quantity']; ?> × <?php echo htmlspecialchars($it['name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="order_id" value="<?php echo (int) $o['id']; ?>">
                            <button type="submit" name="advance_order" value="1" class="btn btn-warning btn-sm text-dark">Start preparing →</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($colCook)): ?>
                    <p class="text-muted mb-0">No tickets waiting.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="kcol prep">
                <h2>Preparing</h2>
                <?php foreach ($colPrep as $o): ?>
                    <div class="kticket prep">
                        <strong>#<?php echo (int) $o['id']; ?></strong> · Table <?php echo htmlspecialchars($o['table_number']); ?>
                        <ul>
                            <?php foreach ($o['items'] as $it): ?>
                                <li><?php echo (int) $it['quantity']; ?> × <?php echo htmlspecialchars($it['name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" class="mt-2">
                            <input type="hidden" name="order_id" value="<?php echo (int) $o['id']; ?>">
                            <button type="submit" name="advance_order" value="1" class="btn btn-info btn-sm">Mark ready →</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($colPrep)): ?>
                    <p class="text-muted mb-0">Nothing on the line.</p>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="kcol ready">
                <h2>Ready</h2>
                <p class="small text-muted">Food is up — cashier collects payment on the POS.</p>
                <?php foreach ($colReady as $o): ?>
                    <div class="kticket ready">
                        <strong>#<?php echo (int) $o['id']; ?></strong> · Table <?php echo htmlspecialchars($o['table_number']); ?>
                        <ul>
                            <?php foreach ($o['items'] as $it): ?>
                                <li><?php echo (int) $it['quantity']; ?> × <?php echo htmlspecialchars($it['name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($colReady)): ?>
                    <p class="text-muted mb-0">Nothing waiting for pickup.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
