<?php
// ============================================
// POS - RECEIPT / SUCCESS SCREEN (SCREEN 11)
// Shows green checkmark, order number, amount paid
// Buttons: Email Receipt + Continue
// ============================================

include '../includes/auth_check.php';
include '../config/db.php';

// Get info from URL
$table_number = isset($_GET['table']) ? intval($_GET['table']) : 1;
$total = isset($_GET['total']) ? intval($_GET['total']) : 0;
$method = isset($_GET['method']) ? $_GET['method'] : 'cash';

// Generate random order number
$order_number = rand(2200, 2299);

// Payment method display text
$method_text = 'Cash';
if ($method == 'digital') $method_text = 'Card/Bank';
if ($method == 'upi') $method_text = 'UPI';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success - Odoo Cafe POS</title>
    <link rel="stylesheet" href="pos_style.css">
</head>
<body>

    <!-- Top Navigation Bar -->
    <?php include '../includes/header.php'; ?>

    <!-- Receipt Container -->
    <div class="receipt-container">

        <div class="receipt-card">
            <!-- Green Checkmark -->
            <div class="receipt-check">✓</div>

            <!-- Order Number -->
            <div class="receipt-order-no">Order #<?php echo $order_number; ?></div>

            <!-- Amount Paid -->
            <div class="receipt-amount">Amount Paid ₹<?php echo number_format($total); ?></div>

            <!-- Status -->
            <div class="receipt-status">Payment via <?php echo htmlspecialchars($method_text); ?> — Successful ✓</div>

            <!-- Action Buttons -->
            <div class="receipt-actions">
                <button class="btn-email-receipt" onclick="alert('Receipt sent to email!')">📧 Email Receipt</button>
                <a href="floor.php" class="btn-continue">Continue ➜</a>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>

    <!-- Clear cart data from session storage -->
    <script>
        sessionStorage.removeItem('cart');
        sessionStorage.removeItem('cartTotal');
        sessionStorage.removeItem('tableNumber');
    </script>

</body>
</html>
