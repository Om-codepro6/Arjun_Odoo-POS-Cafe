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

    $table_id = isset($input['tableId']) ? intval($input['tableId']) : 1;
    $items = isset($input['items']) ? $input['items'] : [];
    $total_amount = isset($input['totalAmount']) ? floatval($input['totalAmount']) : 0;
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;
    $session_id = isset($input['sessionId']) ? intval($input['sessionId']) : null;

    if (empty($items) || $total_amount <= 0) {
        throw new Exception("Invalid order data");
    }

    // Start transaction
    $conn->begin_transaction();

    // Insert order
    $order_stmt = $conn->prepare("
        INSERT INTO orders (table_id, user_id, session_id, total_amount, status, created_at)
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    
    $order_stmt->bind_param('iid', $table_id, $user_id, $total_amount);
    
    if (!$order_stmt->execute()) {
        throw new Exception("Failed to insert order: " . $order_stmt->error);
    }

    $order_id = $conn->insert_id;

    // Insert order items
    $item_stmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $product_id = intval($item['id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);
        $subtotal = $quantity * $price;

        $item_stmt->bind_param('iidd', $order_id, $product_id, $quantity, $subtotal);
        
        if (!$item_stmt->execute()) {
            throw new Exception("Failed to insert order item: " . $item_stmt->error);
        }
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Order saved successfully'
    ]);

} catch (Exception $e) {
    if ($conn->connect_error === null) {
        $conn->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
