<?php
session_start();
require_once "./db.php";

$admin_id = $_SESSION["admin_id"];
$date = $_GET['date'] ?? '';

// 1. Get all available time slots for that date
$stmt = $pdo->prepare("
    SELECT slot_time 
    FROM time_slots
    WHERE admin_id = ? AND slot_date = ?
");
$stmt->execute([$admin_id, $date]);
$allSlots = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 2. Get all booked times for that date
$stmt2 = $pdo->prepare("
    SELECT start_time
    FROM appointments
    WHERE admin_id = ? AND date = ?
");
$stmt2->execute([$admin_id, $date]);
$booked = $stmt2->fetchAll(PDO::FETCH_COLUMN);

// 3. Convert booked times to HH:MM format
$bookedFormatted = array_map(function($t) {
    return substr($t, 0, 5);
}, $booked);

// 4. Convert all slots to HH:MM format
$allFormatted = array_map(function($t) {
    return substr($t, 0, 5);
}, $allSlots);

// 5. Remove booked times
$available = array_diff($allFormatted, $bookedFormatted);

// 6. Return available times
echo json_encode(["times" => array_values($available)]);
