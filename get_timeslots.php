<?php
require "./Admin_system/db.php";

if (!isset($_GET['admin_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit;
}

$admin_id = $_GET['admin_id'];
$date = $_GET['date'];

$stmt = $pdo->prepare("
    SELECT slot_time 
    FROM time_slots 
    WHERE admin_id = ? AND slot_date = ? AND is_booked = 0
");
$stmt->execute([$admin_id, $date]);

$times = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($times);
