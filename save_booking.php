<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

require "./Admin_system/db.php";

// Read JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "Invalid JSON"]);
    exit;
}

$admin_id      = $data["admin_id"];
$services      = $data["services"];
$date          = $data["date"];
$time          = $data["time"];
$deposit_total = $data["deposit_total"];
$total_price   = $data["total_price"];
$fname         = $data["fname"];
$lname         = $data["lname"];
$email         = $data["email"];
$phone         = $data["phone"];

// Validate
if (!$admin_id || !$date || !$time || !$fname || !$lname || !$email) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit;
}

// Convert time to MySQL format
if (strlen($time) === 5) {
    $time .= ":00";
}

// -----------------------------------------------------
// 1️⃣ INSERT OR FIND CLIENT
// -----------------------------------------------------
$stmt = $pdo->prepare("
    SELECT client_id FROM clients 
    WHERE email = ? AND admin_id = ?
");
$stmt->execute([$email, $admin_id]);
$existing = $stmt->fetchColumn();

if ($existing) {
    $client_id = $existing;
} else {
    $stmt = $pdo->prepare("
        INSERT INTO clients (admin_id, first_name, last_name, email, phone_number)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$admin_id, $fname, $lname, $email, $phone]);
    $client_id = $pdo->lastInsertId();
}

// -----------------------------------------------------
// 2️⃣ INSERT APPOINTMENT
// -----------------------------------------------------
$stmt = $pdo->prepare("
    INSERT INTO appointments 
    (client_id, admin_id, date, start_time, end_time, total_price, status, is_seen)
    VALUES (?, ?, ?, ?, ?, ?, 'pending', 0)
");

$end_time = $time; // You can calculate real end time later

$success = $stmt->execute([
    $client_id,
    $admin_id,
    $date,
    $time,
    $end_time,
    $total_price
]);

if (!$success) {
    echo json_encode(["success" => false, "error" => $stmt->errorInfo()]);
    exit;
}

$appointment_id = $pdo->lastInsertId();

// -----------------------------------------------------
// 3️⃣ INSERT SERVICES
// -----------------------------------------------------
foreach ($services as $index => $service) {

    $stmt2 = $pdo->prepare("
        INSERT INTO appointment_services
        (appointment_id, service_id, service_name, duration, price, order_index)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $success2 = $stmt2->execute([
        $appointment_id,
        $service["id"],
        $service["name"],
        $service["duration"],
        $service["full_price"],
        $index
    ]);

    if (!$success2) {
        echo json_encode(["success" => false, "error" => $stmt2->errorInfo()]);
        exit;
    }
}

// -----------------------------------------------------
// 4️⃣ SUCCESS RESPONSE
// -----------------------------------------------------
echo json_encode([
    "success" => true,
    "appointment_id" => $appointment_id
]);
exit;
?>
