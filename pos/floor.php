<?php
include '../includes/auth_check.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_floor') {
        $floor_name = trim($_POST['floor_name'] ?? '');
        if ($floor_name !== '') {
            $stmt = $conn->prepare('INSERT INTO floors (name) VALUES (?)');
            $stmt->bind_param('s', $floor_name);
            $stmt->execute();
            $message = 'Floor added.';
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'add_table') {
        $floor_id = (int) ($_POST['floor_id'] ?? 0);
        $table_number = trim($_POST['table_number'] ?? '');
        if ($floor_id > 0 && $table_number !== '') {
            $stmt = $conn->prepare('INSERT INTO restaurant_tables (floor_id, table_number, status) VALUES (?, ?, ?)');
            $status = 'available';
            $stmt->bind_param('iss', $floor_id, $table_number, $status);
            $stmt->execute();
            $message = 'Table added.';
        }
    }
}

$floors = [];
$r = $conn->query('SELECT * FROM floors ORDER BY name');
while ($row = $r->fetch_assoc()) {
    $floors[] = $row;
}

$tables = [];
$r = $conn->query('SELECT t.*, f.name AS floor_name FROM restaurant_tables t JOIN floors f ON f.id = t.floor_id ORDER BY f.name, t.table_number');
while ($row = $r->fetch_assoc()) {
    $tables[$row['floor_id']][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floor Plan - Odoo Cafe POS</title>
    <link rel="stylesheet" href="pos_style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="dashboard-content">
    <div class="tab-bar">
        <a href="floor.php" class="tab-btn active">Table</a>
        <a href="order.php" class="tab-btn">Register</a>
        <a href="kitchen.php" class="tab-btn">Kitchen</a>
    </div>

    <h1 class="page-title">Floor <span>Tables</span></h1>

    <?php if ($message): ?>
        <p style="color: var(--green); margin-bottom: 12px;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if (empty($floors)): ?>
        <p class="floor-label">No floors yet. Add a floor below, then add tables.</p>
    <?php endif; ?>

    <?php foreach ($floors as $floor): ?>
        <p class="floor-label">📍 <?php echo htmlspecialchars($floor['name']); ?></p>
        <div class="tables-grid">
            <?php if (empty($tables[$floor['id']])): ?>
                <p style="grid-column: 1/-1; color: rgba(255,255,255,0.6);">No tables on this floor.</p>
            <?php else: ?>
                <?php foreach ($tables[$floor['id']] as $t): ?>
                    <a href="order.php?table_id=<?php echo (int) $t['id']; ?>"
                       class="table-card<?php echo $t['status'] === 'occupied' ? ' occupied' : ''; ?>">
                        <?php echo htmlspecialchars($t['table_number']); ?>
                        <span class="table-label" style="<?php echo $t['status'] === 'occupied' ? 'color: var(--green);' : ''; ?>">
                            <?php echo $t['status'] === 'occupied' ? 'Busy' : 'Free'; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div style="margin-top: 32px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
        <div style="background: var(--glass-bg); padding: 20px; border-radius: 16px; border: 1px solid var(--glass-border);">
            <h3 style="margin-top:0;">Add floor</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_floor">
                <input type="text" name="floor_name" placeholder="Ground floor" required class="form-control" style="width:100%; padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #ccc;">
                <button type="submit" class="btn-open-session" style="display:inline-block; text-decoration:none; border:none; cursor:pointer;">Add floor</button>
            </form>
        </div>
        <div style="background: var(--glass-bg); padding: 20px; border-radius: 16px; border: 1px solid var(--glass-border);">
            <h3 style="margin-top:0;">Add table</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_table">
                <input type="text" name="table_number" placeholder="Table number" required class="form-control" style="width:100%; padding:10px; margin-bottom:10px; border-radius:8px; border:1px solid #ccc;">
                <select name="floor_id" required style="width:100%; padding:10px; margin-bottom:10px; border-radius:8px;">
                    <option value="">Select floor</option>
                    <?php foreach ($floors as $f): ?>
                        <option value="<?php echo (int) $f['id']; ?>"><?php echo htmlspecialchars($f['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-open-session" style="display:inline-block; text-decoration:none; border:none; cursor:pointer;">Add table</button>
            </form>
        </div>
    </div>

    <p style="margin-top: 24px;">
        <a href="products.php" style="color: var(--cream);">Product management</a>
        · <a href="payments.php" style="color: var(--cream);">Payments help</a>
        · <a href="../auth/dashboard.php" style="color: var(--cream);">Staff home</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
