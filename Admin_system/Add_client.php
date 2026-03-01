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
    empty($_POST["first_name"]) ||
    empty($_POST["last_name"]) ||
    empty($_POST["email"]) ||
    empty($_POST["phone_number"])
) {
    die("All fields are required.");
}

// Clean input
$first = trim($_POST["first_name"]);
$last = trim($_POST["last_name"]);
$email = trim($_POST["email"]);
$phone = trim($_POST["phone_number"]);

// Insert into database
$stmt = $pdo->prepare("
    INSERT INTO clients (admin_id, first_name, last_name, email, phone_number)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([$admin_id, $first, $last, $email, $phone]);

// Redirect back to clients page
header("Location: Clients.php?added=1");
exit;
?>
