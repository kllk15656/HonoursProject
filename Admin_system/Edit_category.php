<?php
session_start();
require_once "./db.php";

if (!isset($_POST['category_id'], $_POST['category_name'])) {
    header("Location: Categories.php");
    exit;
}

$id = $_POST['category_id'];
$name = $_POST['category_name'];
$admin_id = $_SESSION['admin_id'];

$stmt = $pdo->prepare("
    UPDATE categories 
    SET category_name = ?
    WHERE category_id = ? AND admin_id = ?
");

$stmt->execute([$name, $id, $admin_id]);

header("Location: Categories.php");
exit;
