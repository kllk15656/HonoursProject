<?php
require "./Admin_system/db.php";

// Read JSON sent from JavaScript
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['admin_id'], $data['date'], $data['slots']) || !is_array($data['slots'])) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

$admin_id = $data['admin_id'];
$date = $data['date'];
$slots = $data['slots']; // array of HH:MM:SS times

// Prepare update statement
$stmt = $pdo->prepare("
    UPDATE time_slots
    SET is_booked = 1
    WHERE admin_id = ? AND slot_date = ? AND slot_time = ? AND is_booked = 0
");

$updated = 0;

// Loop through all slots and book them
foreach ($slots as $time) {
    $stmt->execute([$admin_id, $date, $time]);
    $updated += $stmt->rowCount();
}

// If at least one slot was booked, success
echo json_encode(["success" => $updated > 0]);
