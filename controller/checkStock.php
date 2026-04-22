<?php
require "../config/db.php";

header("Content-Type: application/json");

$cart = json_decode(file_get_contents("php://input"), true);

$result = [];

foreach ($cart as $item) {
    $name = $item['name'];

    $stmt = mysqli_prepare($conn, "SELECT stock_small, stock_large, image FROM product WHERE product_name = ?");
    mysqli_stmt_bind_param($stmt, "s", $name);
    mysqli_stmt_execute($stmt);

    $query = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($query);

    $result[] = [
        "name" => $name,
        "cart_qty" => $item['quantity'],
        "type" => $item['type'],
        "price" => $item['price'],
        "image" => base64_encode($product['image']),
        "db_stock_small" => $product['stock_small'],
        "db_stock_large" => $product['stock_large']
    ];
}

echo json_encode($result);
?>