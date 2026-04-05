<?php
if (!isset($pageTitle)) {
    $pageTitle = 'Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> · Cafe POS Admin</title>
    <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="admin-body">
<div class="admin-app">
<?php require __DIR__ . '/sidebar.php'; ?>
<div class="admin-panel">
<?php require __DIR__ . '/topbar.php'; ?>
<main class="admin-content">
