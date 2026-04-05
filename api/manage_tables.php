<?php
session_start();
header('Content-Type: application/json');

require_once '../config/bootstrap.php';

try {
    $action = isset($_GET['action']) ? $_GET['action'] : '';

    if ($action === 'get_floor_tables') {
        // Get all tables for a floor
        $floor_id = isset($_GET['floor_id']) ? intval($_GET['floor_id']) : 1;
        
        $query = $conn->prepare("
            SELECT id, table_number, seats, status 
            FROM restaurant_tables 
            WHERE floor_id = ? 
            ORDER BY id ASC
        ");
        $query->bind_param('i', $floor_id);
        $query->execute();
        $result = $query->get_result();
        $tables = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'tables' => $tables,
            'count' => count($tables)
        ]);
        exit;
    }

    if ($action === 'add_table') {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['floor_id']) || !isset($input['table_number']) || !isset($input['seats'])) {
            throw new Exception("Missing required fields");
        }

        $floor_id = intval($input['floor_id']);
        $table_number = htmlspecialchars($input['table_number']);
        $seats = intval($input['seats']);

        $insert = $conn->prepare("
            INSERT INTO restaurant_tables (floor_id, table_number, seats, status)
            VALUES (?, ?, ?, 'available')
        ");
        $insert->bind_param('isi', $floor_id, $table_number, $seats);
        
        if (!$insert->execute()) {
            throw new Exception("Failed to add table: " . $insert->error);
        }

        echo json_encode([
            'success' => true,
            'table_id' => $conn->insert_id,
            'message' => 'Table added successfully'
        ]);
        exit;
    }

    if ($action === 'update_table_status') {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['table_id']) || !isset($input['status'])) {
            throw new Exception("Missing required fields");
        }

        $table_id = intval($input['table_id']);
        $status = htmlspecialchars($input['status']);

        $update = $conn->prepare("
            UPDATE restaurant_tables 
            SET status = ? 
            WHERE id = ?
        ");
        $update->bind_param('si', $status, $table_id);
        
        if (!$update->execute()) {
            throw new Exception("Failed to update table: " . $update->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Table status updated'
        ]);
        exit;
    }

    if ($action === 'delete_table') {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['table_id'])) {
            throw new Exception("Table ID required");
        }

        $table_id = intval($input['table_id']);

        $delete = $conn->prepare("
            DELETE FROM restaurant_tables 
            WHERE id = ?
        ");
        $delete->bind_param('i', $table_id);
        
        if (!$delete->execute()) {
            throw new Exception("Failed to delete table: " . $delete->error);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Table deleted successfully'
        ]);
        exit;
    }

    if ($action === 'get_floors') {
        $query = $conn->query("
            SELECT f.id, f.name, 
            (SELECT COUNT(*) FROM restaurant_tables WHERE floor_id = f.id) as table_count,
            (SELECT COUNT(*) FROM restaurant_tables WHERE floor_id = f.id AND status = 'available') as available_count
            FROM floors f
            ORDER BY f.id ASC
        ");
        $floors = $query->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode([
            'success' => true,
            'floors' => $floors
        ]);
        exit;
    }

    throw new Exception("Invalid action");

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
