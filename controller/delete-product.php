<?php
session_start();
require "../config/db.php";


if (!isset($_SESSION["id"])) {
    header("Location: " . $BASE_URL . "auth.php");
    exit;
}


if ($_SESSION["role"] !== "admin") {
    header("Location: " . $BASE_URL . "index.html");
    exit;
}

$outlet_code = $_SESSION['outlet_code'];
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;


if (!$product_id) {
    header("Location: " . $BASE_URL . "admin/dashboard.php");
    exit;
}


$check_query = mysqli_query($conn, "SELECT id FROM product WHERE id = '$product_id' AND outlet_code = '$outlet_code'");

if (mysqli_num_rows($check_query) === 0) {
    header("Location: " . $BASE_URL . "admin/dashboard.php");
    exit;
}


$delete_query = mysqli_query($conn, "DELETE FROM product WHERE id = '$product_id' AND outlet_code = '$outlet_code'");

if ($delete_query) {
    header("Location: " . $BASE_URL . "admin/dashboard.php?deleted=true");
    exit;
} else {
    header("Location: " . $BASE_URL . "admin/dashboard.php?error=true");
    exit;
}
?>
