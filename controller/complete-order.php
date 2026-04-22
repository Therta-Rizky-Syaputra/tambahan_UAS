<?php
session_start();
require "../config/db.php";
require "../controller/OrderController.php";

header("Content-Type: application/json");


if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'kasir') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

try {
   
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['order_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }

    $order_id = (int)$data['order_id'];
    $outlet_code = $_SESSION['outlet_code'] ?? 'BRG-1024';

    
    $orderController = new OrderController($conn, $outlet_code);

   
    $result = $orderController->completeOrder($order_id);

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
