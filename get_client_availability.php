<?php
require "./Admin_system/db.php";

if (!isset($_GET['admin_id'])) {
    // If no admin_id is provided, return an empty JSON object
    echo json_encode([]);
    exit;
}

$admin_id = $_GET['admin_id'];

// Fetch available dates
// The admin marks dates as available/unavailable in Admin-Calendar.php
$stmt = $pdo->prepare("
    SELECT available_date 
    FROM availability 
    WHERE admin_id = ? AND status = 1
");
$stmt->execute([$admin_id]);
$dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch times for each date
$availability = [];

foreach ($dates as $date) {
    $stmt2 = $pdo->prepare("
        SELECT times 
        FROM timeslots 
        WHERE admin_id = ? AND date = ?
    ");
    $stmt2->execute([$admin_id, $date]);
    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
     // If times exist, decode JSON → array
    if ($row && $row['times']) {
        $availability[$date] = json_decode($row['times'], true);
    } else {
        $availability[$date] = []; // no times saved
    }
}

echo json_encode($availability);
