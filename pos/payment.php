<?php
session_start();
include '../config/db.php';
include '../includes/auth_check.php';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
$table_id = isset($_GET['table']) ? intval($_GET['table']) : 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Payment Interface</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --bg-dark: #121212;
            --card-bg: #1e1e1e;
            --header-bg: #252525;
            --primary-purple: #714B67;
            --accent-purple: #d4a5bc;
            --text: #e0e0e0;
            --border: #444;
            --success-green: #28a745;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #000;
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* --- Payment Side Panel --- */
        .payment-panel {
            width: 320px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .payment-title {
            font-size: 24px;
            text-align: center;
            margin-bottom: 10px;
        }

        .amount-display {
            background: white;
            color: var(--success-green);
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
            border-radius: 4px;
            border-bottom: 4px solid var(--success-green);
        }

        .method-btn {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text);
            padding: 12px;
            text-align: center;
            cursor: pointer;
            border-radius: 4px;
            transition: 0.2s;
        }

        .method-btn:hover { background: #333; }
        
        .method-btn.active {
            background: #333;
            border: 2px solid var(--accent-purple);
            color: var(--accent-purple);
        }

        .validate-btn {
            background: var(--accent-purple);
            color: black;
            border: none;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        /* --- Modal Overlay --- */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.85);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal {
            background: var(--bg-dark);
            border: 1px solid var(--border);
            width: 300px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            position: relative;
        }

        .close-modal {
            position: absolute;
            top: 10px;
            right: 15px;
            cursor: pointer;
            font-size: 20px;
        }

        .qr-placeholder {
            background: white;
            width: 180px;
            height: 180px;
            margin: 15px auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: black;
            font-weight: bold;
        }

        .qr-placeholder img {
            width: 150px;
        }

        .modal-amount {
            font-size: 20px;
            margin: 15px 0;
            color: var(--success-green);
            font-weight: bold;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
        }

        .btn-confirm { 
            background: var(--accent-purple); 
            color: black;
            flex: 1; 
            border: none; 
            padding: 10px; 
            border-radius: 4px; 
            cursor: pointer;
            font-weight: bold;
        }
        .btn-cancel { 
            background: #444; 
            color: white; 
            flex: 1; 
            border: none; 
            padding: 10px; 
            border-radius: 4px; 
            cursor: pointer;
        }

        .thank-you-modal { width: 400px; }
        .thank-you-modal h2 { margin: 0 0 20px 0; color: var(--success-green); font-size: 28px; }
        .thank-you-modal p { font-size: 16px; margin: 10px 0; }

        #invoiceContent { display: none; }
    </style>
</head>
<body>

    <div class="payment-panel">
        <div class="payment-title">Payment</div>
        <div class="amount-display" id="displayAmount">$ 0.00</div>
        
        <div class="method-btn" onclick="selectMethod('cash', this)">💵 Cash</div>
        <div class="method-btn" onclick="selectMethod('card', this)">💳 Digital (Bank, Card)</div>
        <div class="method-btn" onclick="selectMethod('upi', this)">📱 UPI</div>

        <button class="validate-btn" onclick="validatePayment()">Validate</button>
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="modal-overlay">
        <div class="modal">
            <span class="close-modal" onclick="closeModal('qrModal')">✕</span>
            <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;">UPI QR Code</div>
            
            <div class="qr-placeholder">
                <img id="qrCode" src="" alt="QR Code">
            </div>

            <div class="modal-amount">Amount: <span id="upiAmount">$ 0.00</span></div>
            <div style="font-size: 12px; color: #888; margin-bottom: 15px;">UPI ID: <span id="upiId">upi@example</span></div>

            <div class="modal-actions">
                <button class="btn-confirm" onclick="confirmUPI()">Confirmed</button>
                <button class="btn-cancel" onclick="closeModal('qrModal')">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Card Payment Modal -->
    <div id="cardModal" class="modal-overlay">
        <div class="modal" style="width: 500px; text-align: left;">
            <span class="close-modal" onclick="closeModal('cardModal')">✕</span>
            <h2 style="text-align: center; margin-top: 0;">Debit / Credit Card</h2>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: #888; font-size: 12px; margin-bottom: 5px;">Cardholder Name</label>
                <input type="text" id="cardName" placeholder="Full Name on Card" style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; color: #888; font-size: 12px; margin-bottom: 5px;">Card Number</label>
                <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000" style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box;">
            </div>

            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; color: #888; font-size: 12px; margin-bottom: 5px;">Expiry Date</label>
                    <input type="text" id="expiry" placeholder="MM / YY" style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; color: #888; font-size: 12px; margin-bottom: 5px;">CVV</label>
                    <input type="password" id="cvv" placeholder="***" style="width: 100%; padding: 10px; background: #0a0a0a; border: 1px solid #444; color: white; border-radius: 4px; box-sizing: border-box;">
                </div>
            </div>

            <div style="text-align: center;">
                <div style="color: #888; margin-bottom: 10px;">Amount: <span style="color: var(--success-green); font-weight: bold;" id="cardAmount">$ 0.00</span></div>
                <button class="btn-confirm" onclick="confirmCard()" style="width: 100%;">Validate Payment</button>
            </div>
        </div>
    </div>

    <!-- Thank You Modal -->
    <div id="thankYouModal" class="modal-overlay">
        <div class="modal thank-you-modal">
            <h2 style="text-align: center;">✓ Thank You!</h2>
            <p style="text-align: center; font-size: 16px;">You can pay at the counter and enjoy your meals.</p>
            <button class="btn-confirm" onclick="confirmCash()" style="width: 100%;">Continue</button>
        </div>
    </div>

    <!-- Invoice (Hidden) -->
    <div id="invoiceContent">
        <div style="padding: 20px; background: white; color: black; font-family: Arial; width: 400px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h2 style="margin: 0;">INVOICE</h2>
                <p style="margin: 5px 0;">Odoo POS Cafe</p>
            </div>
            
            <div style="border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
                <p style="margin: 0;"><strong>Date:</strong> <span id="invDate"></span></p>
                <p style="margin: 0;"><strong>User:</strong> <span id="invUserName"></span></p>
            </div>

            <div style="margin-bottom: 10px;">
                <p style="margin: 0;"><strong>Items:</strong></p>
                <div id="invItems" style="margin-left: 10px;"></div>
            </div>

            <div style="border-top: 1px solid #000; padding-top: 10px; margin-top: 10px;">
                <p style="margin: 0;"><strong>Total Amount:</strong> <span id="invTotal"></span></p>
                <p style="margin: 0;"><strong>Payment Method:</strong> UPI</p>
                <p style="margin: 0;"><strong>UPI ID:</strong> <span id="invUpiId"></span></p>
            </div>

            <div style="text-align: center; margin-top: 20px; font-size: 12px;">
                <p style="margin: 0;">Thank you for your purchase!</p>
            </div>
        </div>
    </div>

    <script>
        let selectedMethod = null;
        let totalAmount = 0;
        let cartItems = [];
        let userName = 'Customer';
        let currentTableId = null;

        // Get data from URL parameters or sessionStorage
        function initPayment() {
            const params = new URLSearchParams(window.location.search);
            totalAmount = parseFloat(params.get('amount')) || parseFloat(sessionStorage.getItem('paymentAmount')) || 0;
            cartItems = JSON.parse(sessionStorage.getItem('cartItems')) || [];
            userName = sessionStorage.getItem('userName') || 'Customer';
            currentTableId = parseInt(sessionStorage.getItem('currentTable')) || 1;
            
            document.getElementById('displayAmount').textContent = '$ ' + totalAmount.toFixed(2);
        }

        function selectMethod(method, element) {
            selectedMethod = method;
            document.querySelectorAll('.method-btn').forEach(btn => btn.classList.remove('active'));
            element.classList.add('active');
        }

        function validatePayment() {
            if (!selectedMethod) {
                alert('Please select a payment method');
                return;
            }

            if (selectedMethod === 'cash') {
                openModal('thankYouModal');
            } else if (selectedMethod === 'upi') {
                generateUPIQR();
            } else if (selectedMethod === 'card') {
                document.getElementById('cardAmount').textContent = '$ ' + totalAmount.toFixed(2);
                openModal('cardModal');
            }
        }

        function generateUPIQR() {
            const upiId = 'odoo' + Math.floor(Math.random() * 100000) + '@upi';
            const upiLink = `upi://pay?pa=${upiId}&pn=OdooCafe&am=${totalAmount}&tn=Order`;
            
            document.getElementById('upiAmount').textContent = '$ ' + totalAmount.toFixed(2);
            document.getElementById('upiId').textContent = upiId;
            
            // Generate QR code
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(upiLink)}`;
            document.getElementById('qrCode').src = qrUrl;
            
            sessionStorage.setItem('upiId', upiId);
            openModal('qrModal');
        }

        function confirmUPI() {
            const upiId = sessionStorage.getItem('upiId');
            generateInvoice('UPI', upiId);
            downloadInvoice();
            closeModal('qrModal');
            completePayment();
        }

        function confirmCard() {
            const cardName = document.getElementById('cardName').value.trim();
            if (!cardName) {
                alert('Please enter cardholder name');
                return;
            }
            generateInvoice('Debit Card', '****' + document.getElementById('cardNumber').value.slice(-4));
            closeModal('cardModal');
            completePayment();
        }

        function confirmCash() {
            generateInvoice('Cash', 'N/A');
            closeModal('thankYouModal');
            completePayment();
        }

        function generateInvoice(method, reference) {
            const now = new Date();
            const dateStr = now.toLocaleDateString() + ' ' + now.toLocaleTimeString();
            
            document.getElementById('invDate').textContent = dateStr;
            document.getElementById('invUserName').textContent = userName;
            document.getElementById('invTotal').textContent = '$ ' + totalAmount.toFixed(2);
            document.getElementById('invUpiId').textContent = reference;
            
            let itemsHtml = '';
            cartItems.forEach(item => {
                itemsHtml += `<p style="margin: 3px 0;">${item.quantity} x ${item.name} - $${(item.price * item.quantity).toFixed(2)}</p>`;
            });
            document.getElementById('invItems').innerHTML = itemsHtml || '<p style="margin: 3px 0;">N/A</p>';
            
            sessionStorage.setItem('paymentMethod', method);
        }

        function downloadInvoice() {
            const element = document.getElementById('invoiceContent').querySelector('div');
            const opt = {
                margin: 10,
                filename: 'invoice_' + Date.now() + '.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };
            html2pdf().set(opt).from(element).save();
        }

        async function completePayment() {
            try {
                // Save order to database
                const orderResponse = await fetch('../api/save_order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        tableId: currentTableId || 1,
                        items: cartItems,
                        totalAmount: totalAmount,
                        sessionId: null
                    })
                });

                const orderData = await orderResponse.json();
                
                if (!orderData.success) {
                    console.error('Order save error:', orderData.error);
                }

                const orderId = orderData.order_id;

                // Save payment to database
                const paymentResponse = await fetch('../api/save_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        orderId: orderId,
                        amount: totalAmount,
                        paymentMethod: sessionStorage.getItem('paymentMethod') || 'cash',
                        transactionId: sessionStorage.getItem('upiId') || null
                    })
                });

                const paymentData = await paymentResponse.json();
                
                if (!paymentData.success) {
                    console.error('Payment save error:', paymentData.error);
                }

                // Complete payment flow
                setTimeout(() => {
                    sessionStorage.setItem('paymentConfirmed', 'true');
                    window.location.href = 'pos_terminal.php?paymentComplete=true';
                }, 1000);

            } catch (error) {
                console.error('Payment completion error:', error);
                alert('Error processing payment. Please try again.');
            }
        }

        function openModal(modalId) {
            document.getElementById(modalId).classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Initialize
        initPayment();
    </script>
</body>
</html>
