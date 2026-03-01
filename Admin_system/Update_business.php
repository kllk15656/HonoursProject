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
if (empty($_POST["business_name"]) || empty($_POST["email"])) {
    die("Business name and email are required.");
}

$business_name = trim($_POST["business_name"]);
$email = trim($_POST["email"]);

// Update admin business info
$stmt = $pdo->prepare("
    UPDATE admins
    SET business_name = ?, email = ?
    WHERE admin_id = ?
");

$stmt->execute([$business_name, $email, $admin_id]);

// Redirect back with success message
header("Location: Setting.php?business_updated=1");
exit;
?>
