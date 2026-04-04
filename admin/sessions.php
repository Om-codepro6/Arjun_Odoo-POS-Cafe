<?php
include '../includes/auth_check.php';
include '../config/db.php';

if ($current_role !== 'admin') {
    header('Location: ../pos/index.php');
    exit();
}

$message = '';
if (isset($_GET['close_session']) && is_numeric($_GET['close_session'])) {
    $sessionId = (int) $_GET['close_session'];
    $amount = number_format((float) ($_GET['closing_amount'] ?? 0), 2, '.', '');
    $stmt = $conn->prepare('UPDATE pos_sessions SET session_end = NOW(), closing_amount = ? WHERE id = ?');
    $stmt->bind_param('di', $amount, $sessionId);
    $stmt->execute();
    $message = 'Session #' . $sessionId . ' closed successfully.';
}

$sessions = [];
$result = $conn->query('SELECT s.*, u.username FROM pos_sessions s JOIN users u ON u.id = s.user_id ORDER BY s.session_start DESC LIMIT 20');
while ($row = $result->fetch_assoc()) {
    $sessions[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Sessions - Odoo Cafe Admin</title>
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
            <h1 class="admin-page-title">POS Sessions</h1>
            <a href="dashboard.php" class="btn btn-outline btn-sm">Back to Dashboard</a>
        </div>

        <div class="admin-card">
            <h3 class="admin-card-title">Active and recent sessions</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Session</th>
                        <th>User</th>
                        <th>Started</th>
                        <th>Ended</th>
                        <th>Closing</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr><td colspan="6" class="text-muted">No sessions yet.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td>#<?php echo (int)$session['id']; ?></td>
                            <td><?php echo htmlspecialchars($session['username']); ?></td>
                            <td><?php echo htmlspecialchars($session['session_start']); ?></td>
                            <td><?php echo $session['session_end'] ? htmlspecialchars($session['session_end']) : '<span style="color: var(--orange);">Open</span>'; ?></td>
                            <td>₹<?php echo number_format((float)$session['closing_amount'], 2); ?></td>
                            <td>
                                <?php if (!$session['session_end']): ?>
                                    <a href="?close_session=<?php echo (int)$session['id']; ?>&closing_amount=0" class="btn btn-sm btn-danger">Close now</a>
                                <?php else: ?>
                                    <span class="badge badge-green">Closed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</body>
</html>
