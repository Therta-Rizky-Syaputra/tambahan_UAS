<?php
    session_start();
    $_SESSION = [];
    session_unset();
    session_destroy();

    require "../config/db.php";

    header("Location: " . $BASE_URL . "auth.php");
    exit;
?>