<?php
session_start();
require_once "./db.php";

if (!isset($_SESSION["admin_id"])) exit;

$admin_id = $_SESSION["admin_id"];
$date = $_POST["date"];
$status = $_POST["status"];

// Check if exists
$stmt = $pdo->prepare("
    SELECT availability_id FROM availability
    WHERE admin_id = ? AND available_date = ?
");
$stmt->execute([$admin_id, $date]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $stmt = $pdo->prepare("
        UPDATE availability SET status = ?
        WHERE availability_id = ?
    ");
    $stmt->execute([$status, $row["availability_id"]]);
} else {
    $stmt = $pdo->prepare("
        INSERT INTO availability (admin_id, available_date, status)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$admin_id, $date, $status]);
}

echo "OK";
