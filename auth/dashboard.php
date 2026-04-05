<?php
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: ../admin/index.php');
    exit();
}

$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe POS · Terminal</title>
    <link rel="stylesheet" href="terminal.css">
</head>
<body class="terminal-body">
<div class="terminal-shell">
    <header class="terminal-bar">
        <div class="terminal-brand">
            <span class="terminal-brand__mark">C</span>
            <div class="terminal-brand__text">
                <strong>Cafe POS</strong>
                <span>Terminal</span>
            </div>
        </div>
        <div class="terminal-bar__user">
            <span class="terminal-bar__name">Signed in as <b><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></b></span>
            <a class="terminal-btn-logout" href="logout.php">Logout</a>
        </div>
    </header>

    <section class="terminal-hero">
        <h1>Start selling</h1>
        <p>Open a POS session to record orders and payments against today’s shift.</p>
        <a class="terminal-btn-primary" href="pos.php">Open POS session →</a>
    </section>

    <div class="terminal-grid">
        <a class="terminal-card" href="../pos/floor.php">
            <h3>Floor &amp; tables</h3>
            <p>Pick a table and take orders when a session is active.</p>
            <span class="terminal-card__tag">Tables</span>
        </a>
        <a class="terminal-card" href="../pos/products.php">
            <h3>Products</h3>
            <p>Browse the menu and prices from the terminal.</p>
            <span class="terminal-card__tag">Menu</span>
        </a>
        <a class="terminal-card" href="../pos/payments.php">
            <h3>Payments</h3>
            <p>Review payment options and recent activity.</p>
            <span class="terminal-card__tag">Checkout</span>
        </a>
    </div>

    <p class="terminal-hint">Admins sign in to the back office automatically. This screen is for staff on the floor.</p>
</div>
</body>
</html>
