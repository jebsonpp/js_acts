<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__.'/../inc/auth.php';
require_once __DIR__.'/../inc/db.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    die(json_encode(['success' => false, 'message' => 'Not authenticated']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data || empty($data['cart'])) {
            throw new Exception('Empty cart');
        }

        $user = current_user();
        $user_id = intval($user['id']); // Ensure integer
        $total = floatval($data['total']);
        $order_json = json_encode($data['cart']);

        // First query - create order with correct column order
        $stmt = $mysqli->prepare("INSERT INTO orders (order_json, total, added_by, date_added) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }

        if (!$stmt->bind_param('sdi', $order_json, $total, $user_id)) {
            throw new Exception('Binding parameters failed: ' . $stmt->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $order_id = $mysqli->insert_id;
        
        // Second query - insert order items
        $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $mysqli->error);
        }
        
        foreach ($data['cart'] as $item) {
            $prod_id = intval($item['id']);
            $qty = intval($item['qty']);
            $price = floatval($item['price']);
            
            if (!$stmt->bind_param('iiid', $order_id, $prod_id, $qty, $price)) {
                throw new Exception('Binding parameters failed: ' . $stmt->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
        }

        echo json_encode(['success' => true, 'id' => $order_id]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);
exit;
