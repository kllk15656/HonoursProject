<?php
require "./db.php";

if (!isset($_GET['id'])) {
    exit("No ID");
}

$id = $_GET['id'];

$sql = "UPDATE appointments SET is_seen = 1 WHERE appointment_id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);

echo "OK";
