<?php
session_start();
require_once "./db.php";

if (!isset($_POST['category_id'])) {
    header("Location: Categories.php");
    exit;
}

$id = $_POST['category_id'];
$admin_id = $_SESSION['admin_id'];

$stmt = $pdo->prepare("
    DELETE FROM categories 
    WHERE category_id = ? AND admin_id = ?
");

$stmt->execute([$id, $admin_id]);

header("Location: Categories.php");
exit;
