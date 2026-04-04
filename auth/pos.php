<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Start POS session in database
$stmt = $conn->prepare("INSERT INTO pos_sessions (user_id) VALUES (?)");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$session_id = $conn->insert_id;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe POS - POS Session</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <h1>POS Session Started</h1>
        <p>Session ID: <?php echo $session_id; ?></p>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">POS Interface</h5>
                        <p class="card-text">This is where the POS interface would be implemented. For now, it's a placeholder.</p>
                        <a href="dashboard.php" class="btn-dashboard">Back to Dashboard</a>
                        <a href="end_session.php?session_id=<?php echo $session_id; ?>" class="btn btn-danger ms-2">End Session</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>