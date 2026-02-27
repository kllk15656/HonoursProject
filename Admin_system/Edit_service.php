<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

require_once "./db.php";

$admin_id = $_SESSION["admin_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $service_id = $_POST["service_id"];
    $service_name = $_POST["service_name"];
    $duration = $_POST["duration_minutes"];
    $price = $_POST["price"];
    $deposit = $_POST["deposit_price"];
    $category_id = $_POST["category_id"];

    // Update only services belonging to this admin
    $stmt = $pdo->prepare("
        UPDATE services
        SET service_name = ?, duration_minutes = ?, price = ?, deposit_price = ?, category_id = ?
        WHERE service_id = ? AND admin_id = ?
    ");

    $stmt->execute([$service_name, $duration, $price, $deposit, $category_id, $service_id, $admin_id]);

    header("Location: Services.php");
    exit;
}
?>
