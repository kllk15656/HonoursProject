<?php
header("Content-Type: application/json");

// Read JSON sent from Confirmation.php
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

// Connect to DB
$conn = new mysqli("localhost", "root", "", "honoursproject");

if ($conn->connect_error) {
    echo json_encode(["error" => "DB connection failed"]);
    exit;
}

//  Check if client already exists (match by email + admin)
$check = $conn->prepare("SELECT client_id FROM clients WHERE email = ? AND admin_id = ?");
$check->bind_param("si", $email, $admin_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Client exists
    $check->bind_result($client_id);
    $check->fetch();
} else {
    // 2. Create new client
    $insertClient = $conn->prepare(
        "INSERT INTO clients (admin_id, first_name, last_name, email, phone_number)
         VALUES (?, ?, ?, ?, ?)"
    );
    $insertClient->bind_param("issss", $admin_id, $fname, $lname, $email, $phone);
    $insertClient->execute();
    $client_id = $insertClient->insert_id;
}

// 3. Create appointment
$service = $data["service"];

$insertAppt = $conn->prepare(
    "INSERT INTO appointments (admin_id, client_id, appointment_date, appointment_time, service_name, status, is_seen)
     VALUES (?, ?, ?, ?, ?, 'confirmed', 0)"
);
$insertAppt->bind_param("iisss", $admin_id, $client_id, $date, $time, $service);
$insertAppt->execute();


echo json_encode([
    "success" => true,
    "appointment_id" => $insertAppt->insert_id,
    "client_id" => $client_id
]);
?>
