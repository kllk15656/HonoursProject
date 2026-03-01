<?php
session_start();
require_once "./db.php";

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

$admin_id = $_SESSION["admin_id"];

// Validate required fields
if (
    empty($_POST["client_id"]) ||
    empty($_POST["first_name"]) ||
    empty($_POST["last_name"]) ||
    empty($_POST["email"]) ||
    empty($_POST["phone_number"])
) {
    die("All fields are required.");
}

$client_id = $_POST["client_id"];
$first = trim($_POST["first_name"]);
$last = trim($_POST["last_name"]);
$email = trim($_POST["email"]);
$phone = trim($_POST["phone_number"]);

// Update client record
$stmt = $pdo->prepare("
    UPDATE clients
    SET first_name = ?, last_name = ?, email = ?, phone_number = ?
    WHERE client_id = ? AND admin_id = ?
");

$stmt->execute([$first, $last, $email, $phone, $client_id, $admin_id]);

header("Location: Clients.php?updated=1");
exit;
?>
