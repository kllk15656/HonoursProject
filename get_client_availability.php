<?php
require "./Admin_system/db.php";

if (!isset($_GET['admin_id'])) {
    echo json_encode([]);
    exit;
}

$admin_id = $_GET['admin_id'];

$stmt = $pdo->prepare("
    SELECT slot_date, slot_time
    FROM time_slots
    WHERE admin_id = ? AND is_booked = 0
    ORDER BY slot_time ASC
");
$stmt->execute([$admin_id]);

$availability = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $date = $row['slot_date'];
    $time = $row['slot_time'];

    if (!isset($availability[$date])) {
        $availability[$date] = [];
    }

    $availability[$date][] = $time;
}

echo json_encode($availability);
