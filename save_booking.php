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
$services = $data["services"];
$total_price = $data["total_price"]; // full price total

$conn = new mysqli("localhost", "root", "", "bookingsystem");

if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

/*1. CHECK IF CLIENT EXISTS*/
$check = $conn->prepare("SELECT client_id FROM clients WHERE email = ? AND admin_id = ?");
$check->bind_param("si", $email, $admin_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $check->bind_result($client_id);
    $check->fetch();
} else {
    $insertClient = $conn->prepare(
        "INSERT INTO clients (admin_id, first_name, last_name, email, phone_number, created_at)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $insertClient->bind_param("issss", $admin_id, $fname, $lname, $email, $phone);
    $insertClient->execute();
    $client_id = $insertClient->insert_id;
}

/* ---------------------------------------------------------
   2. CALCULATE END TIME BASED ON TOTAL DURATION
--------------------------------------------------------- */
$start = new DateTime($time);
$end = clone $start;

$total_duration = 0;
foreach ($services as $s) {
    $total_duration += intval($s["duration"]);
}
$end->modify("+$total_duration minutes");
$end_time = $end->format("H:i:s");

/* 3. INSERT APPOINTMENT (NOW WITH created_at)*/
$insertAppt = $conn->prepare(
    "INSERT INTO appointments 
    (client_id, admin_id, date, start_time, end_time, total_price, status, is_seen, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 'confirmed', 0, NOW())"
);

$insertAppt->bind_param("iisssd", 
    $client_id, 
    $admin_id, 
    $date, 
    $time, 
    $end_time, 
    $total_price
);

$insertAppt->execute();
$appointment_id = $insertAppt->insert_id;

/*4. INSERT SERVICES INTO appointment_services */
foreach ($services as $index => $s) {
    $stmt = $conn->prepare(
        "INSERT INTO appointment_services 
        (appointment_id, service_id, service_name, duration, price, order_index)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param(
        "iisddi",
        $appointment_id,
        $s["id"],
        $s["name"],
        $s["duration"],
        $s["full_price"], // full price stored here
        $index + 1
    );
    $stmt->execute();
}

/* 5. RETURN SUCCESS*/
echo json_encode([
    "success" => true,
    "appointment_id" => $appointment_id,
    "client_id" => $client_id
]);
?>
