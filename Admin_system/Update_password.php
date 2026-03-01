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
    empty($_POST["current_password"]) ||
    empty($_POST["new_password"]) ||
    empty($_POST["confirm_password"])
) {
    die("All password fields are required.");
}

$current = $_POST["current_password"];
$new = $_POST["new_password"];
$confirm = $_POST["confirm_password"];

// Check if new passwords match
if ($new !== $confirm) {
    die("New passwords do not match.");
}

// Fetch current password hash
$stmt = $pdo->prepare("SELECT password FROM admins WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    die("Admin not found.");
}

// Verify current password
if (!password_verify($current, $admin["password"])) {
    die("Current password is incorrect.");
}

// Hash new password
$new_hashed = password_hash($new, PASSWORD_DEFAULT);

// Update password
$stmt = $pdo->prepare("
    UPDATE admins
    SET password = ?
    WHERE admin_id = ?
");

$stmt->execute([$new_hashed, $admin_id]);

// Redirect with success message
header("Location: Setting.php?password_updated=1");
exit;
?>
