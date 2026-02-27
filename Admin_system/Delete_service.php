<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

require_once "./db.php";

$admin_id = $_SESSION["admin_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $service_id = (int) $_POST["service_id"];

    // Validate service ID
    if ($service_id <= 0) {
        header("Location: Services.php?error=invalid_id");
        exit;
    }

    // Delete only if service belongs to this admin
    $stmt = $pdo->prepare("
        DELETE FROM services
        WHERE service_id = ? AND admin_id = ?
    ");

    $stmt->execute([$service_id, $admin_id]);

    header("Location: Services.php?success=deleted");
    exit;
}
?>
