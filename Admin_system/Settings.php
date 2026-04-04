<?php
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

require_once "./db.php";

// Logged-in admin ID
$admin_id = $_SESSION["admin_id"];

// Fetch admin business info
$stmt = $pdo->prepare("
    SELECT business_name, email, description, website_url
    FROM admins 
    WHERE admin_id = ?
");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Default values if empty
$business_name = $admin['business_name'] ?? "";
$email = $admin['email'] ?? "";
$description = $admin['description'] ?? "";
$website_url = $admin['website_url'] ?? "";
?>


<!DOCTYPE html>
<html lang ="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href="./css/settings.css">
</head>

<body>

<!-- Top Nav -->
<div class="top-nav">
        <h1>Admin Dashboard</h1>
         <div class="hamburger" onclick="toggleMenu()">
        <img src="./images/menu.png" alt="Menu">
    </div>
    </div>

    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="Admin-Calendar.php">Calendar</a>
        <a href="Categories.php">Categories</a>
        <a href="Services.php">Services</a>
        <a href="Clients.php">Clients</a>

        <p class="mobile-nav-label">Navigation</p>

        <div class="mobile-nav-links"> 
            <a href="Dashboard.php">Dashboard</a>
            <a href="Settings.php"class="active">Settings</a>
            <a href="Logout.php">Log Out</a>
        </div>
    </div>
        <div class="overlay" onclick="toggleMenu()"></div>

<!-- Main Content -->
<div class="main-content">
    <div class="card">
        <h1>Settings</h1>

        <!-- Business Info / allows the admin to update their business name -->
        <div class="settings-section">
            <h3>Business Information</h3>
            <form action="Update_business.php" method="POST">
                <div class="form-group">
                    <label>Business Name:</label>
                    <input type="text" name="business_name" value="<?= htmlspecialchars($business_name) ?>">
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($description) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Website URL (optional):</label>
                    <input type="text" name="website_url" value="<?= htmlspecialchars($website_url) ?>">
                </div>


                <button class="btn-yellow">Save Details</button>
            </form>
        </div>

        <hr>

        <!-- Password / allows admins to create a new password -->
        <div class="settings-section">
            <h3>Password</h3>

            <form action="Update_password.php" method="POST">
                <div class="form-group">
                    <label>Current:</label>
                    <input type="password" name="current_password">
                </div>

                <div class="form-group">
                    <label>New:</label>
                    <input type="password" name="new_password">
                </div>

                <div class="form-group">
                    <label>Confirm:</label>
                    <input type="password" name="confirm_password">
                </div>

                <button class="btn-yellow">Save Password</button>
            </form>
        </div>

        <hr>

        <!-- Stripe / just a place hodlder for stripe integration. for future connection to Stripe API  -->
        <div class="settings-section">
            <h3>Payment Integration (Stripe)</h3>

            <p>Status:
                <span class="status-dot"></span> Connected
            </p>

            <form action="UpdateStripe.php" method="POST">
                <div class="form-group">
                    <label>Stripe Account ID:</label>
                    <input type="text" name="stripe_id">
                </div>

                <button class="btn-yellow">Change Stripe Account</button>
            </form>
        </div>

<!-- This is just a UI -->
        <div class="save-all">
            <button class="btn-main">Save All Changes</button>
        </div>

    </div>
</div>

<script>
    function toggleMenu() {
    console.log('toggleMenu fired');

    const menu = document.querySelector('.side-nav');
    const overlay = document.querySelector('.overlay');

    console.log('before:', menu.className);
    menu.classList.toggle('open');
    overlay.classList.toggle('show');
    console.log('after:', menu.className);
}
</script>

</body>
</html>
