<?php
session_start();
require_once "./db.php";

$admin_id = $_SESSION["admin_id"];
$date = $_GET['date'] ?? '';

$stmt = $pdo->prepare("
    SELECT slot_time 
    FROM time_slots
    WHERE admin_id = ? AND slot_date = ?
");
$stmt->execute([$admin_id, $date]);

$rows = $stmt->fetchAll(PDO::FETCH_COLUMN);

$times = [];
foreach ($rows as $t) {
    $times[] = substr($t, 0, 5); // convert HH:MM:SS → HH:MM
}

echo json_encode(["times" => $times]);
