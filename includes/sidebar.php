<!-- ============================================
     ADMIN SIDEBAR - Left side navigation
     Only used in admin pages
     ============================================ -->

<aside class="admin-sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-brand">
        <span class="sidebar-logo">☕</span>
        <span>Admin Panel</span>
    </div>

    <!-- Sidebar Menu Links -->
    <nav class="sidebar-menu">
        <a href="dashboard.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
            <span class="sidebar-icon">📊</span>
            Dashboard
        </a>
        <a href="products.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
            <span class="sidebar-icon">🍔</span>
            Products
        </a>
        <a href="payments.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'payments.php') ? 'active' : ''; ?>">
            <span class="sidebar-icon">💳</span>
            Payments
        </a>
        <a href="floors.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'floors.php' || basename($_SERVER['PHP_SELF']) == 'tables.php') ? 'active' : ''; ?>">
            <span class="sidebar-icon">🏠</span>
            Floors & Tables
        </a>
        <a href="reports.php" class="sidebar-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
            <span class="sidebar-icon">📈</span>
            Reports
        </a>
    </nav>

    <!-- Bottom: Back to POS -->
    <div class="sidebar-bottom">
        <a href="../pos/index.php" class="sidebar-link sidebar-back">
            <span class="sidebar-icon">🖥️</span>
            Back to POS
        </a>
    </div>
</aside>
