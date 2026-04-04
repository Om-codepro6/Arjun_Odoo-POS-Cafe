<?php
// ============================================
// ADMIN - FLOORS & TABLES MANAGEMENT
// Store floor and table configuration in the database
// ============================================

include '../includes/auth_check.php';
include '../config/db.php';

if ($current_role !== 'admin') {
    header('Location: ../pos/index.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_floor') {
        $name = trim($_POST['floor_name'] ?? '');
        if ($name !== '') {
            $stmt = $conn->prepare('INSERT INTO floors (name) VALUES (?)');
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $message = 'Floor added successfully.';
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'add_table') {
        $floor_id = (int) ($_POST['floor_id'] ?? 0);
        $table_number = trim($_POST['table_number'] ?? '');
        $seats = max(1, (int) ($_POST['seats'] ?? 4));
        if ($floor_id > 0 && $table_number !== '') {
            $status = 'available';
            $stmt = $conn->prepare('INSERT INTO restaurant_tables (floor_id, table_number, seats, status) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isis', $floor_id, $table_number, $seats, $status);
            $stmt->execute();
            $message = 'Table added successfully.';
        }
    }
}

$floors = [];
$result = $conn->query('SELECT f.*, COUNT(t.id) AS table_count FROM floors f LEFT JOIN restaurant_tables t ON t.floor_id = f.id GROUP BY f.id ORDER BY f.name');
while ($row = $result->fetch_assoc()) {
    $floors[] = $row;
}

$tables = [];
$result = $conn->query('SELECT t.*, f.name AS floor_name FROM restaurant_tables t JOIN floors f ON f.id = t.floor_id ORDER BY f.name, t.table_number');
while ($row = $result->fetch_assoc()) {
    $tables[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Floors & Tables - Odoo Cafe Admin</title>
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
            <h1 class="admin-page-title">Floors & Tables</h1>
            <button class="btn btn-primary" onclick="openAddFloor()">+ Add Floor</button>
        </div>

        <div class="floors-layout">
            <div class="admin-card">
                <h3 class="admin-card-title">Floors</h3>
                <?php if (empty($floors)): ?>
                    <p class="text-muted">No floors created yet. Add one to start mapping tables.</p>
                <?php endif; ?>
                <?php foreach ($floors as $floor): ?>
                    <div class="floor-list-item <?php echo $floor['id'] === (int)($floors[0]['id'] ?? 0) ? 'active' : ''; ?>" onclick="selectFloor(this)">
                        <div>
                            <strong><?php echo htmlspecialchars($floor['name']); ?></strong>
                            <p style="font-size: 0.8rem; color: var(--gray);"><?php echo (int)$floor['table_count']; ?> tables</p>
                        </div>
                        <span class="badge badge-green">Active</span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="admin-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 class="admin-card-title" style="margin-bottom: 0; padding-bottom: 0; border: none;">All Tables</h3>
                    <button class="btn btn-outline btn-sm" onclick="openAddTable()">+ Add Table</button>
                </div>

                <?php if (empty($tables)): ?>
                    <p class="text-muted">No tables are configured. Add a floor and tables to start taking orders.</p>
                <?php else: ?>
                    <div class="tables-manage-grid">
                        <?php foreach ($tables as $table): ?>
                            <div class="table-manage-card">
                                <h4><?php echo htmlspecialchars($table['table_number']); ?></h4>
                                <p><?php echo (int)$table['seats']; ?> Seats · <?php echo htmlspecialchars($table['floor_name']); ?></p>
                                <span class="badge <?php echo $table['status'] === 'occupied' ? 'badge-red' : 'badge-green'; ?>" style="margin-top: 8px;">
                                    <?php echo ucfirst($table['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Add Floor Modal -->
    <div class="modal-overlay" id="addFloorModal">
        <div class="modal-box" style="max-width: 400px;">
            <button class="modal-close" onclick="closeAddFloor()">&times;</button>
            <h2 class="modal-title">Add New Floor</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_floor">
                <div class="form-group">
                    <label class="form-label">Floor Name</label>
                    <input type="text" name="floor_name" class="form-input" placeholder="e.g. Main Floor" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeAddFloor()">Discard</button>
                    <button type="submit" class="btn btn-success">Save Floor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Table Modal -->
    <div class="modal-overlay" id="addTableModal">
        <div class="modal-box" style="max-width: 400px;">
            <button class="modal-close" onclick="closeAddTable()">&times;</button>
            <h2 class="modal-title">Add New Table</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_table">
                <div class="form-group">
                    <label class="form-label">Table Number</label>
                    <input type="text" name="table_number" class="form-input" placeholder="e.g. 8" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Number of Seats</label>
                    <input type="number" name="seats" class="form-input" placeholder="4" value="4" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Floor</label>
                    <select name="floor_id" class="form-input" required>
                        <option value="">Select Floor</option>
                        <?php foreach ($floors as $floor): ?>
                            <option value="<?php echo (int)$floor['id']; ?>"><?php echo htmlspecialchars($floor['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeAddTable()">Discard</button>
                    <button type="submit" class="btn btn-success">Save Table</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function selectFloor(el) {
            document.querySelectorAll('.floor-list-item').forEach(function(item) {
                item.classList.remove('active');
            });
            el.classList.add('active');
        }

        function openAddFloor() { document.getElementById('addFloorModal').classList.add('show'); }
        function closeAddFloor() { document.getElementById('addFloorModal').classList.remove('show'); }
        function openAddTable() { document.getElementById('addTableModal').classList.add('show'); }
        function closeAddTable() { document.getElementById('addTableModal').classList.remove('show'); }

        document.querySelectorAll('.modal-overlay').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('show');
            });
        });
    </script>

</body>
</html>
