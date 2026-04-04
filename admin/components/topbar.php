<?php
$pageHeading = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? 'Overview';
$username = htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars($_SESSION['role'] ?? '', ENT_QUOTES, 'UTF-8');
$initial = strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1));
?>
<header class="admin-topbar">
    <div>
        <h1 class="admin-topbar__title"><?php echo htmlspecialchars($pageHeading, ENT_QUOTES, 'UTF-8'); ?></h1>
        <span class="admin-topbar__crumb"><?php echo htmlspecialchars($pageSubtitle, ENT_QUOTES, 'UTF-8'); ?></span>
    </div>
    <div class="admin-topbar__actions">
        <div class="admin-user">
            <span class="admin-user__avatar" aria-hidden="true"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></span>
            <div class="admin-user__meta">
                <span class="admin-user__name"><?php echo $username; ?></span>
                <span class="admin-user__role"><?php echo $role; ?></span>
            </div>
        </div>
        <a class="btn-logout" href="../auth/logout.php">Logout</a>
    </div>
</header>
