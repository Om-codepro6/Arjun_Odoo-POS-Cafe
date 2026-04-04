<?php
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['session_id'])) {
    $session_id = $_GET['session_id'];
    $user_id = $_SESSION['user_id'];

    // Update session end time
    $stmt = $conn->prepare("UPDATE pos_sessions SET session_end = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();
}

header("Location: dashboard.php");
exit();
?>