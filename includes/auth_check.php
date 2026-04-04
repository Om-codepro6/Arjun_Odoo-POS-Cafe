<?php

require_once __DIR__ . '/../auth/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_username = $_SESSION['username'] ?? '';
$current_role = $_SESSION['role'] ?? 'user';
