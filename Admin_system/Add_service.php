<?php
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

require_once "./db.php";

$admin_id = $_SESSION["admin_id"];

// Only run when form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Collect form data
    $service_name = trim($_POST["service_name"]);
    $duration = (int) $_POST["duration_minutes"];
    $price = (float) $_POST["price"];
    $deposit = (float) $_POST["deposit_price"];
    $category_id = (int) $_POST["category_id"];

    // Basic validation (optional but recommended)
    if ($service_name === "" || $duration <= 0 || $price < 0 || $deposit < 0 || $category_id <= 0) {
        header("Location: Services.php?error=invalid_input");
        exit;
    }

    // Insert service
    $stmt = $pdo->prepare("
        INSERT INTO services 
        (service_name, duration_minutes, price, deposit_price, category_id, admin_id)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $service_name,
        $duration,
        $price,
        $deposit,
        $category_id,
        $admin_id
    ]);

    header("Location: Services.php?success=added");
    exit;
}
?>
