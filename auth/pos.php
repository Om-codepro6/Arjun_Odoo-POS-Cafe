<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $conn->prepare('INSERT INTO pos_sessions (user_id) VALUES (?)');
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$session_id = $conn->insert_id;
$_SESSION['pos_session_id'] = $session_id;

header('Location: ../pos/floor.php?session_id=' . $session_id);
exit();