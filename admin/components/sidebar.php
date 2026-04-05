<?php
$nav = $currentNav ?? '';
?>
<aside class="admin-sidebar" aria-label="Admin navigation">
    <a class="admin-sidebar__brand" href="index.php">
        <span class="admin-sidebar__logo">C</span>
        <span class="admin-sidebar__titles">
            <strong>Cafe POS</strong>
            <span>Admin</span>
        </span>
    </a>
    <nav class="admin-nav" aria-label="Main">
        <a class="<?php echo $nav === 'dashboard' ? 'is-active' : ''; ?>" href="index.php">
            <span class="admin-nav__icon" aria-hidden="true">◉</span>
            Dashboard
        </a>
        <a class="<?php echo $nav === 'products' ? 'is-active' : ''; ?>" href="../pos/products.php">
            <span class="admin-nav__icon" aria-hidden="true">▤</span>
            Products
        </a>
        <a class="<?php echo $nav === 'payments' ? 'is-active' : ''; ?>" href="../pos/payments.php">
            <span class="admin-nav__icon" aria-hidden="true">◇</span>
            Payments
        </a>
        <a class="<?php echo $nav === 'floors' ? 'is-active' : ''; ?>" href="../pos/floor.php">
            <span class="admin-nav__icon" aria-hidden="true">▦</span>
            Floors / Tables
        </a>
        <a class="<?php echo $nav === 'sessions' ? 'is-active' : ''; ?>" href="sessions.php">
            <span class="admin-nav__icon" aria-hidden="true">⏱</span>
            POS Sessions
        </a>
        <a class="<?php echo $nav === 'reports' ? 'is-active' : ''; ?>" href="reports.php">
            <span class="admin-nav__icon" aria-hidden="true">▣</span>
            Reports
        </a>
    </nav>
</aside>
