<?php
// ============================================
// ADMIN - PAYMENT SETTINGS
// Manage payment methods and UPI configuration
// ============================================

include '../includes/auth_check.php';
include '../config/db.php';

if ($current_role !== 'admin') {
    header('Location: ../pos/index.php');
    exit();
}

function getConfigValue(mysqli $conn, string $key, string $default = ''): string
{
    $stmt = $conn->prepare('SELECT config_value FROM config WHERE config_key = ? LIMIT 1');
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['config_value'] ?? $default;
}

function saveConfigValue(mysqli $conn, string $key, string $value): void
{
    $stmt = $conn->prepare('INSERT INTO config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)');
    $stmt->bind_param('ss', $key, $value);
    $stmt->execute();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cash = isset($_POST['payment_cash']) ? '1' : '0';
    $digital = isset($_POST['payment_digital']) ? '1' : '0';
    $upi = isset($_POST['payment_upi']) ? '1' : '0';
    $upi_id = trim($_POST['payment_upi_id'] ?? '');
    $terminal_name = trim($_POST['terminal_name'] ?? 'Odoo Cafe');
    $auto_print = isset($_POST['auto_print']) ? '1' : '0';
    $kitchen_display = isset($_POST['kitchen_display']) ? '1' : '0';

    saveConfigValue($conn, 'payment_cash', $cash);
    saveConfigValue($conn, 'payment_digital', $digital);
    saveConfigValue($conn, 'payment_upi', $upi);
    saveConfigValue($conn, 'payment_upi_id', $upi_id ?: '123@ybl.com');
    saveConfigValue($conn, 'terminal_name', $terminal_name);
    saveConfigValue($conn, 'auto_print', $auto_print);
    saveConfigValue($conn, 'kitchen_display', $kitchen_display);

    $message = 'Payment settings updated successfully.';
}

$paymentCash = getConfigValue($conn, 'payment_cash', '1');
$paymentDigital = getConfigValue($conn, 'payment_digital', '1');
$paymentUpi = getConfigValue($conn, 'payment_upi', '1');
$paymentUpiId = getConfigValue($conn, 'payment_upi_id', '123@ybl.com');
$terminalName = getConfigValue($conn, 'terminal_name', 'Odoo Cafe');
$autoPrint = getConfigValue($conn, 'auto_print', '0');
$kitchenDisplay = getConfigValue($conn, 'kitchen_display', '1');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Settings - Odoo Cafe Admin</title>
    <link rel="stylesheet" href="admin_style.css">
</head>
<body>

    <!-- Top Nav -->
    <nav class="admin-top-nav">
        <a href="dashboard.php" class="nav-brand">Odoo Cafe</a>
        <div class="nav-right">
            <span class="nav-user">👤 <?php echo htmlspecialchars($current_username); ?></span>
            <a href="../auth/logout.php" class="nav-logout">Logout</a>
        </div>
    </nav>

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="admin-main">

        <form method="POST">
            <?php if ($message): ?>
                <div class="admin-card" style="border-left: 4px solid var(--green);">
                    <p style="margin:0; color: var(--green);"><?php echo htmlspecialchars($message); ?></p>
                </div>
            <?php endif; ?>

            <div class="admin-page-header">
                <h1 class="admin-page-title">Payment Settings</h1>
                <button type="submit" class="btn btn-success">💾 Save Settings</button>
            </div>

            <div class="admin-card" style="max-width: 720px;">
                <h3 class="admin-card-title">Payment Options</h3>

                <div class="toggle-container">
                    <div class="toggle-info">
                        <h4>💵 Cash</h4>
                        <p>Accept cash payments at the counter</p>
                    </div>
                    <label class="toggle-switch <?php echo $paymentCash === '1' ? 'on' : ''; ?>">
                        <input type="checkbox" name="payment_cash" style="display:none;" <?php echo $paymentCash === '1' ? 'checked' : ''; ?>>
                    </label>
                </div>

                <div class="toggle-container">
                    <div class="toggle-info">
                        <h4>💳 Card / Digital</h4>
                        <p>Accept digital and card payments</p>
                    </div>
                    <label class="toggle-switch <?php echo $paymentDigital === '1' ? 'on' : ''; ?>">
                        <input type="checkbox" name="payment_digital" style="display:none;" <?php echo $paymentDigital === '1' ? 'checked' : ''; ?>>
                    </label>
                </div>

                <div class="toggle-container">
                    <div class="toggle-info">
                        <h4>📱 UPI</h4>
                        <p>Enable UPI payments and QR generation</p>
                    </div>
                    <label class="toggle-switch <?php echo $paymentUpi === '1' ? 'on' : ''; ?>" onclick="toggleInput('upiIdSection')">
                        <input type="checkbox" name="payment_upi" style="display:none;" <?php echo $paymentUpi === '1' ? 'checked' : ''; ?>>
                    </label>
                </div>

                <div id="upiIdSection" style="padding: 15px 0 0; border-top: 1px dashed var(--gray-light); display: <?php echo $paymentUpi === '1' ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label class="form-label">UPI ID</label>
                        <input type="text" class="form-input" name="payment_upi_id" value="<?php echo htmlspecialchars($paymentUpiId); ?>">
                    </div>
                    <p style="font-size: 0.85rem; color: var(--gray);">This UPI ID appears on the POS payment QR screen.</p>
                </div>
            </div>

            <div class="admin-card" style="max-width: 720px;">
                <h3 class="admin-card-title">Terminal Settings</h3>

                <div class="form-group">
                    <label class="form-label">Terminal Name</label>
                    <input type="text" class="form-input" name="terminal_name" value="<?php echo htmlspecialchars($terminalName); ?>">
                </div>

                <div class="toggle-container">
                    <div class="toggle-info">
                        <h4>🧾 Auto Print Receipt</h4>
                        <p>Print receipt after payment completion</p>
                    </div>
                    <label class="toggle-switch <?php echo $autoPrint === '1' ? 'on' : ''; ?>">
                        <input type="checkbox" name="auto_print" style="display:none;" <?php echo $autoPrint === '1' ? 'checked' : ''; ?>>
                    </label>
                </div>

                <div class="toggle-container" style="border-bottom: none;">
                    <div class="toggle-info">
                        <h4>🍽️ Kitchen Display</h4>
                        <p>Send orders to the kitchen board automatically</p>
                    </div>
                    <label class="toggle-switch <?php echo $kitchenDisplay === '1' ? 'on' : ''; ?>">
                        <input type="checkbox" name="kitchen_display" style="display:none;" <?php echo $kitchenDisplay === '1' ? 'checked' : ''; ?>>
                    </label>
                </div>
            </div>
        </form>

    </main>

    <script>
        function toggleInput(sectionId) {
            var section = document.getElementById(sectionId);
            if (section.style.display === 'none' || section.style.display === '') {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        }
    </script>

</body>
</html>
