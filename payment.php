<?php
session_start();
require "config/db.php";

header("Content-Type: application/json");

// Validasi session
if (!isset($_SESSION['table_number']) || !isset($_SESSION['outlet_code'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid session']);
    exit;
}

$table_number = $_SESSION['table_number'];
$outlet_code = $_SESSION['outlet_code'];

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No data received']);
        exit;
    }

    $order_id = $data['order_id'];
    $payment_method = $data['payment_method'];
    $cart = $data['cart'];

    // Calculate total
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Insert order
    $stmt = mysqli_prepare($conn, "
        INSERT INTO orders (order_id, outlet_code, table_number, payment_method, total_amount, status, created_at)
        VALUES (?, ?, ?, ?, ?, 'pending', NOW())
    ");
    mysqli_stmt_bind_param($stmt, "ssiss", $order_id, $outlet_code, $table_number, $payment_method, $total);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
        exit;
    }

    $order_db_id = mysqli_insert_id($conn);

    // Insert order items
    foreach ($cart as $item) {
        $stmt_item = mysqli_prepare($conn, "
            INSERT INTO order_items (order_id, product_name, size, quantity, unit_price, total_price)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt_item, "ssiiid",
            $order_db_id,
            $item['name'],
            $item['type'],
            $item['quantity'],
            $item['price'] / $item['quantity'], // unit price
            $item['price'] // total price for this item
        );
        mysqli_stmt_execute($stmt_item);
    }

    // Clear cart from session
    unset($_SESSION['cart']);

    echo json_encode([
        'success' => true,
        'message' => 'Order created successfully',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>