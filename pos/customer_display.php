<?php
// ============================================
// CUSTOMER DISPLAY (SCREEN 12)
// Clean UI showing order items, total, payment status
// This page could be shown on a second screen facing the customer
// ============================================

// No auth needed for customer display - it's public facing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_username = 'Customer';
$current_role = 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Display - Odoo Cafe</title>
    <link rel="stylesheet" href="pos_style.css">
    <style>
        /* ===== CUSTOMER DISPLAY SPECIFIC STYLES ===== */

        body {
            background: linear-gradient(180deg, #1a0f07, #2d1810);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .customer-display {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .customer-logo {
            font-size: 3rem;
            margin-bottom: 10px;
        }

        .customer-brand {
            font-size: 2rem;
            font-weight: 800;
            color: #f5a623;
            margin-bottom: 40px;
            letter-spacing: 1px;
        }

        .customer-card {
            background: rgba(255,255,255,0.95);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.3);
        }

        .customer-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #3b1f0b;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .customer-table-badge {
            background: #3b1f0b;
            color: white;
            padding: 4px 14px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .customer-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0e6d6;
            font-size: 1rem;
        }

        .customer-item:last-child {
            border-bottom: none;
        }

        .customer-item-name {
            font-weight: 500;
            color: #1a1a1a;
        }

        .customer-item-qty {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .customer-item-price {
            font-weight: 700;
            color: #5c3317;
        }

        .customer-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0 0;
            margin-top: 10px;
            border-top: 3px solid #3b1f0b;
            font-size: 1.4rem;
            font-weight: 800;
            color: #3b1f0b;
        }

        .customer-status {
            text-align: center;
            margin-top: 25px;
            padding: 16px;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
        }

        .status-unpaid {
            background: rgba(245, 166, 35, 0.12);
            color: #b8860b;
        }

        .status-paid {
            background: rgba(45, 106, 79, 0.12);
            color: #2d6a4f;
        }

        .customer-footer {
            text-align: center;
            padding: 30px;
            color: rgba(255,255,255,0.4);
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <!-- Customer Display -->
    <div class="customer-display">

        <!-- Logo -->
        <div class="customer-logo">☕</div>
        <div class="customer-brand">Odoo Cafe</div>

        <!-- Order Card -->
        <div class="customer-card">
            <div class="customer-card-title">
                Your Order
                <span class="customer-table-badge">Table 6</span>
            </div>

            <!-- Order Items -->
            <div class="customer-item">
                <div>
                    <div class="customer-item-name">🍔 Burger</div>
                    <div class="customer-item-qty">1 x ₹95</div>
                </div>
                <div class="customer-item-price">₹95</div>
            </div>

            <div class="customer-item">
                <div>
                    <div class="customer-item-name">🍕 Pizza</div>
                    <div class="customer-item-qty">2 x ₹450</div>
                </div>
                <div class="customer-item-price">₹900</div>
            </div>

            <div class="customer-item">
                <div>
                    <div class="customer-item-name">☕ Coffee</div>
                    <div class="customer-item-qty">2 x ₹18</div>
                </div>
                <div class="customer-item-price">₹36</div>
            </div>

            <div class="customer-item">
                <div>
                    <div class="customer-item-name">🍰 Cake</div>
                    <div class="customer-item-qty">1 x ₹150</div>
                </div>
                <div class="customer-item-price">₹150</div>
            </div>

            <!-- Total -->
            <div class="customer-total">
                <span>Total</span>
                <span>₹1,181</span>
            </div>

            <!-- Payment Status -->
            <div class="customer-status status-unpaid" id="paymentStatus">
                ⏳ Payment Pending
            </div>
        </div>

    </div>

    <!-- Footer -->
    <div class="customer-footer">
        <p>Thank you for visiting Odoo Cafe! ☕</p>
    </div>

    <!-- Toggle payment status demo -->
    <script>
        // Click on the status to toggle (for demo purposes)
        var statusEl = document.getElementById('paymentStatus');
        statusEl.addEventListener('click', function() {
            if (this.classList.contains('status-unpaid')) {
                this.classList.remove('status-unpaid');
                this.classList.add('status-paid');
                this.innerHTML = '✅ Paid — Thank You!';
            } else {
                this.classList.remove('status-paid');
                this.classList.add('status-unpaid');
                this.innerHTML = '⏳ Payment Pending';
            }
        });
    </script>

</body>
</html>
