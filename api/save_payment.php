<?php
session_start();
header('Content-Type: application/json');

require_once '../config/bootstrap.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        throw new Exception("Invalid JSON data");
    }

    $order_id = isset($input['orderId']) ? intval($input['orderId']) : null;
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0;
    $payment_method = isset($input['paymentMethod']) ? $input['paymentMethod'] : 'cash';
    $transaction_id = isset($input['transactionId']) ? $input['transactionId'] : null;

    if (!$order_id || $amount <= 0) {
        throw new Exception("Invalid payment data");
    }

    // Save payment
    $payment_stmt = $conn->prepare("
        INSERT INTO payments (order_id, amount, payment_method, status, transaction_id, created_at)
        VALUES (?, ?, ?, 'success', ?, NOW())
    ");

    $payment_stmt->bind_param('ids', $order_id, $amount, $payment_method);
    
    if (!$payment_stmt->execute()) {
        throw new Exception("Failed to save payment: " . $payment_stmt->error);
    }

    $payment_id = $conn->insert_id;

    // Update order status to completed
    $update_stmt = $conn->prepare("
        UPDATE orders SET status = 'completed' WHERE id = ?
    ");
    $update_stmt->bind_param('i', $order_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update order status: " . $update_stmt->error);
    }

    echo json_encode([
        'success' => true,
        'payment_id' => $payment_id,
        'message' => 'Payment recorded successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
