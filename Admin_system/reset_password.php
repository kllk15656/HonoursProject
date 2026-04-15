<?php
session_start();
require_once "./db.php";

$token = $_GET['token'] ?? '';

// STEP 1 — When the page loads (GET request), validate the token
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!$token) {
        die("Invalid reset link.");
    }

    // Look up token
    $stmt = $pdo->prepare("
        SELECT rt.admin_id, rt.expires_at, a.email
        FROM reset_tokens rt
        JOIN admins a ON rt.admin_id = a.admin_id
        WHERE rt.token = :token
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch();

    if (!$row) {
        die("Invalid or expired reset link.");
    }

    if (strtotime($row['expires_at']) < time()) {
        die("This reset link has expired.");
    }

    // If token is valid → show password reset form
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Password</title>
        <link rel="stylesheet" href="./css/reset_password.css">
    </head>

    <body>

    <div class="reset-container">
        <h2>Reset Password</h2>
        <p>Enter your new password below</p>

        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <input type="password" name="password" placeholder="New Password" required>

            <button type="submit" class="reset-btn">Update Password</button>
        </form>

        <a href="Login.php" class="back-link">Back to Login</a>
    </div>

    </body>
    </html>

    <?php
    exit;
}

// STEP 2 — When the form is submitted (POST request), update password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['password'] ?? '';

    if (!$token || !$newPassword) {
        die("Invalid request.");
    }

    // Validate token again
    $stmt = $pdo->prepare("
        SELECT admin_id, expires_at
        FROM reset_tokens
        WHERE token = :token
        LIMIT 1
    ");
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch();

    if (!$row || strtotime($row['expires_at']) < time()) {
        die("Invalid or expired reset link.");
    }

    $adminId = $row['admin_id'];

    // Hash new password
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update admin password
    $stmt = $pdo->prepare("
        UPDATE admins
        SET password_hash = :password
        WHERE admin_id = :admin_id
    ");
    $stmt->execute([
        ':password' => $hashed,
        ':admin_id' => $adminId
    ]);

    // Delete token so it can't be reused
    $stmt = $pdo->prepare("DELETE FROM reset_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);

    echo "<script>
        alert('Password updated successfully. You can now log in.');
        window.location.href = 'Login.php';
    </script>";
    exit;
}
?>
