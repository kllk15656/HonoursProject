<?php
session_start();
require_once "./db.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

if (!isset($_POST['category_name'])) {
    header("Location: Categories.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
$category_name = $_POST['category_name'];

$stmt = $pdo->prepare("
    INSERT INTO categories (category_name, admin_id)
    VALUES (?, ?)
");

$stmt->execute([$category_name, $admin_id]);

header("Location: Categories.php");
exit;
