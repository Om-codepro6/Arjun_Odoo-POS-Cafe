<?php
$amount = isset($_GET['amount']) ? preg_replace('/[^0-9.]/', '', $_GET['amount']) : '0';
if ($amount === '' || (float) $amount <= 0) {
    $amount = '0.00';
}
$label = 'CafePOS';
// Demo UPI deep link — replace pa= with your real UPI ID in production.
$upi = 'upi://pay?pa=DEMO@upi&pn=' . rawurlencode($label) . '&am=' . rawurlencode($amount) . '&cu=INR';
$qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($upi);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPI QR · Cafe POS</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, sans-serif;
            background: #0b1220;
            color: #e2e8f0;
            padding: 24px;
        }
        .box {
            text-align: center;
            max-width: 360px;
        }
        .box img {
            border-radius: 16px;
            background: #fff;
            padding: 12px;
        }
        .amt { font-size: 2rem; font-weight: 800; margin: 20px 0 8px; }
        .sub { color: #94a3b8; margin-bottom: 24px; }
        .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        a, button {
            padding: 12px 22px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            font-size: 1rem;
        }
        .btn-ok { background: #22c55e; color: #052e16; }
        .btn-no { background: transparent; color: #f87171; border: 1px solid #f87171; }
    </style>
</head>
<body>
    <div class="box">
        <img src="<?php echo htmlspecialchars($qrSrc); ?>" width="220" height="220" alt="UPI QR code">
        <div class="amt">₹<?php echo htmlspecialchars($amount); ?></div>
        <div class="sub">Scan with any UPI app · <?php echo htmlspecialchars($label); ?></div>
        <div class="actions">
            <button type="button" class="btn-ok" onclick="window.close()">Done</button>
            <a class="btn-no" href="javascript:window.close()">Close</a>
        </div>
        <p style="margin-top:24px;font-size:0.8rem;color:#64748b;">Replace DEMO@upi in <code>pos/upi_qr.php</code> with your merchant UPI ID.</p>
    </div>
</body>
</html>
