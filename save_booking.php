<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "No data received"]);
    exit;
}

$admin_id = $data["admin_id"];
$fname = $data["fname"];
$lname = $data["lname"];
$email = $data["email"];
$phone = $data["phone"];
$date = $data["date"];
$time = $data["time"];
$services = $data["services"]; // array of services from cart
$total_price = $data["total_price"];

$conn = new mysqli("localhost", "root", "", "bookingsystem");

if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

// 1. Check if client exists
$check = $conn->prepare("SELECT client_id FROM clients WHERE email = ? AND admin_id = ?");
$check->bind_param("si", $email, $admin_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->bind_result($client_id);
    $check->fetch();
} else {
    // Create new client
    $insertClient = $conn->prepare(
        "INSERT INTO clients (admin_id, first_name, last_name, email, phone_number)
         VALUES (?, ?, ?, ?, ?)"
    );
    $insertClient->bind_param("issss", $admin_id, $fname, $lname, $email, $phone);
    $insertClient->execute();
    $client_id = $insertClient->insert_id;
}

// 2. Create appointment (correct columns)
$insertAppt = $conn->prepare(
    "INSERT INTO appointments (client_id, admin_id, date, start_time, end_time, total_price, status, is_seen)
     VALUES (?, ?, ?, ?, ?, ?, 'confirmed', 0)"
);

// Calculate end time based on total duration
$start = new DateTime($time);
$end = clone $start;

$total_duration = 0;
foreach ($services as $s) {
    $total_duration += intval($s["duration"]);
}
$end->modify("+$total_duration minutes");

$end_time = $end->format("H:i:s");

$insertAppt->bind_param("iisssd", $client_id, $admin_id, $date, $time, $end_time, $total_price);
$insertAppt->execute();

$appointment_id = $insertAppt->insert_id;

// 3. Insert services into appointment_services
foreach ($services as $index => $s) {
    $stmt = $conn->prepare(
        "INSERT INTO appointment_services (appointment_id, service_id, service_name, duration, price, order_index)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "iisddi",
        $appointment_id,
        $s["id"],
        $s["name"],
        $s["duration"],
        $s["price"],
        $index + 1
    );
    $stmt->execute();
}

echo json_encode([
    "success" => true,
    "appointment_id" => $appointment_id,
    "client_id" => $client_id
]);
?>
