<?php
session_start();
require_once "./db.php";

// Ensure admin is logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

$admin_id = $_SESSION["admin_id"];

// Validate ID
if (empty($_POST["client_id"])) {
    die("Client ID missing.");
}

$client_id = $_POST["client_id"];

// Delete only if the client belongs to this admin
$stmt = $pdo->prepare("
    DELETE FROM clients
    WHERE client_id = ? AND admin_id = ?
");

$stmt->execute([$client_id, $admin_id]);

header("Location: Clients.php?deleted=1");
exit;
?>
