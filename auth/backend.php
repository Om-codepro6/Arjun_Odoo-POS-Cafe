<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $config_key = $_POST['config_key'];
    $config_value = $_POST['config_value'];

    // Check if config exists
    $stmt = $conn->prepare("SELECT id FROM config WHERE config_key = ?");
    $stmt->bind_param("s", $config_key);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update
        $stmt = $conn->prepare("UPDATE config SET config_value = ? WHERE config_key = ?");
        $stmt->bind_param("ss", $config_value, $config_key);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO config (config_key, config_value) VALUES (?, ?)");
        $stmt->bind_param("ss", $config_key, $config_value);
    }
    $stmt->execute();
    $message = "Configuration updated successfully.";
}

// Get all configs
$result = $conn->query("SELECT * FROM config");
$configs = [];
while ($row = $result->fetch_assoc()) {
    $configs[$row['config_key']] = $row['config_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe POS - Backend Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <h1>Backend Configuration</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Current Configurations</h5>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($configs as $key => $value): ?>
                                <li class="list-group-item"><?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add/Update Configuration</h5>
                        <form method="POST">
                            <div class="form-group">
                                <input type="text" class="form-control" name="config_key" placeholder="Config Key" required>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="config_value" placeholder="Config Value" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn-dashboard mt-4">Back to Dashboard</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>