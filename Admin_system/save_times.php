<?php
session_start();
require_once "./db.php";

if (!isset($_SESSION["admin_id"])) {
    http_response_code(403);
    exit("Not authorized");
}

$admin_id = $_SESSION["admin_id"];
$date = $_POST['date'] ?? '';
$timesJson = $_POST['times'] ?? '[]';

$times = json_decode($timesJson, true);
if (!is_array($times)) {
    $times = [];
}

if (!$date) {
    http_response_code(400);
    exit("Missing date");
}

$pdo->beginTransaction();

// Remove existing slots for that admin + date
$del = $pdo->prepare("
    DELETE FROM time_slots
    WHERE admin_id = ? AND slot_date = ?
");
$del->execute([$admin_id, $date]);

// Insert new slots
$ins = $pdo->prepare("
    INSERT INTO time_slots (admin_id, slot_date, slot_time, is_booked)
    VALUES (?, ?, ?, 0)
");

foreach ($times as $t) {
    // Ensure HH:MM:SS format for TIME column
    if (strlen($t) === 5) {
        $t .= ":00";
    }
    $ins->execute([$admin_id, $date, $t]);
}

$pdo->commit();

echo "OK";
