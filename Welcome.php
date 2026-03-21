<?php
require "./Admin_system/db.php";

// get the admind_id from the url if missing set to null to avoid error
$admin_id = $_GET['admin_id'] ?? null;


// getch the admin business details
$stmt = $pdo->prepare("SELECT business_name, description FROM admins WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(); // returns business name and description
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome | Cozy Bear Booking</title>
  <link rel="stylesheet" href="./css/style.css">
</head>

<body>
  <!-- Logo -->
  <img src="./bear.png" alt="Bear logo" class="bear-icon">

  <div class="container">
    <div class="form-section">
      <h2>Welcome</h2>
      <p>Please choose an option below to continue</p>
      <!-- Buttons -->
      <div class="welcome-buttons">
        <!-- Existing client -->
         <a href="Service.php?admin_id=<?= $admin_id ?>" class="service-btn">Existing Client</a>
        <!-- New client -->
        <a href="Service.php?admin_id=<?= $admin_id ?>" class="service-btn">New Client</a>
      </div>
    </div>


    <!-- RIGHT: info section -->
    <div class="info-section">
      <h3><?= htmlspecialchars($admin['business_name'] ?? 'Business Name') ?></h3>
      <p><?= nl2br(htmlspecialchars($admin['description'] ?? 'Online booking system')) ?></p>
    </div>
  </div>
</body>
</html>

