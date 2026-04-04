<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe POS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <a href="logout.php" class="logout-btn">Logout</a>
    
    <div class="dashboard">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Open POS Session</h5>
                        <p class="card-text">Start a new point of sale session to handle transactions.</p>
                        <a href="pos.php" class="btn-dashboard">Open POS</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Backend Configuration</h5>
                        <p class="card-text">Manage system settings and configurations.</p>
                        <a href="backend.php" class="btn-dashboard">Access Backend</a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($role == 'admin'): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Admin Panel</h5>
                        <p class="card-text">Manage users and system administration.</p>
                        <a href="admin.php" class="btn-dashboard">Admin Panel</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>