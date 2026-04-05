<?php
require_once '../config/bootstrap.php';

// Check if data already exists
$count_query = $conn->query("SELECT COUNT(*) as count FROM products");
$count_result = $count_query->fetch_assoc();

if ($count_result['count'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Sample data already exists!']);
    exit;
}

try {
    // Insert sample products
    $products = [
        ['Pizza Margherita', 'Pizza', 12.99, 'Classic cheese pizza'],
        ['Pepperoni Pizza', 'Pizza', 14.99, 'Pizza with pepperoni'],
        ['Burger Special', 'Burger', 10.99, 'Special burger with fries'],
        ['Classic Burger', 'Burger', 8.99, 'Simple classic burger'],
        ['Iced Coffee', 'Drink', 4.99, 'Cold coffee'],
        ['Fresh Orange Juice', 'Drink', 3.99, 'Orange juice'],
        ['Chocolate Cake', 'Dessert', 6.99, 'Homemade chocolate cake'],
        ['Cheesecake', 'Dessert', 7.99, 'New York style cheesecake'],
    ];

    $product_stmt = $conn->prepare("INSERT INTO products (name, category, price, description) VALUES (?, ?, ?, ?)");

    foreach ($products as $product) {
        $product_stmt->bind_param('ssds', $product[0], $product[1], $product[2], $product[3]);
        $product_stmt->execute();
    }

    // Insert sample floor
    $floor_stmt = $conn->prepare("INSERT INTO floors (name) VALUES (?)");
    $floor_name = "First Floor";
    $floor_stmt->bind_param('s', $floor_name);
    $floor_stmt->execute();
    $floor_id = $conn->insert_id;

    // Insert sample tables
    $table_stmt = $conn->prepare("INSERT INTO restaurant_tables (floor_id, table_number, seats) VALUES (?, ?, ?)");
    
    for ($i = 1; $i <= 7; $i++) {
        $table_num = "Table " . $i;
        $seats = 4;
        $table_stmt->bind_param('isi', $floor_id, $table_num, $seats);
        $table_stmt->execute();
    }

    // Insert sample user (if not exists)
    $user_check = $conn->query("SELECT id FROM users WHERE username = 'admin' LIMIT 1");
    
    if ($user_check->num_rows === 0) {
        $user_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        $username = "admin";
        $email = "admin@cafe.com";
        $password = password_hash("admin123", PASSWORD_BCRYPT);
        $role = "admin";
        $user_stmt->bind_param('ssss', $username, $email, $password, $role);
        $user_stmt->execute();
    }

    // Insert sample session
    $session_stmt = $conn->prepare("INSERT INTO pos_sessions (user_id, closing_amount) VALUES (?, ?)");
    $user_id = 1;
    $closing_amount = 0.00;
    $session_stmt->bind_param('id', $user_id, $closing_amount);
    $session_stmt->execute();
    $session_id = $conn->insert_id;

    // Insert sample orders
    $order_stmt = $conn->prepare("INSERT INTO orders (table_id, user_id, session_id, total_amount, status) VALUES (?, ?, ?, ?, ?)");
    
    $samples = [
        [1, 1, $session_id, 25.99, 'completed'],
        [2, 1, $session_id, 42.50, 'completed'],
        [3, 1, $session_id, 18.99, 'completed'],
        [1, 1, $session_id, 35.00, 'completed'],
        [4, 1, $session_id, 55.75, 'completed'],
        [5, 1, $session_id, 21.50, 'completed'],
        [6, 1, $session_id, 33.25, 'completed'],
        [7, 1, $session_id, 44.99, 'completed'],
    ];

    foreach ($samples as $order) {
        $order_stmt->bind_param('iidis', $order[0], $order[1], $order[2], $order[3], $order[4]);
        $order_stmt->execute();
        $order_id = $conn->insert_id;

        // Insert order items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        
        $product_id = rand(1, 8);
        $quantity = rand(1, 3);
        $price = 10.00;
        $subtotal = $quantity * $price;
        
        $item_stmt->bind_param('iiidd', $order_id, $product_id, $quantity, $price, $subtotal);
        $item_stmt->execute();
    }

    // Insert sample payments
    $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, amount, payment_method, status) VALUES (?, ?, ?, ?)");
    
    for ($i = 1; $i <= 8; $i++) {
        $amount = (25 + rand(0, 30));
        $method = ['cash', 'upi', 'digital'][rand(0, 2)];
        $status = 'success';
        $payment_stmt->bind_param('ids', $i, $amount, $method);
        $payment_stmt->execute();
    }

    echo json_encode(['success' => true, 'message' => 'Sample data inserted successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
