<!-- Top Navigation Bar -->
<nav class="top-nav">
    <a href="index.php" class="nav-brand">Odoo Cafe</a>

    <ul class="nav-links">
        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">POS</a></li>
        <li><a href="floor.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'floor.php' ? 'active' : ''; ?>">Tables</a></li>
        <li><a href="kitchen.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'kitchen.php' ? 'active' : ''; ?>">Kitchen</a></li>
        <?php if (($current_role ?? '') === 'admin'): ?>
        <li><a href="../admin/index.php">Admin</a></li>
        <?php endif; ?>
    </ul>

    <div class="nav-right">
        <span class="nav-user">👤 <?php echo htmlspecialchars($current_username ?? ''); ?></span>
        <a href="../auth/logout.php" class="nav-logout">Logout</a>
    </div>
</nav>
