<?php

//Start session
session_start();
require "./db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

//Collects data from form
$email =$_POST["email"];
$password = $_POST["password"];

//Looking up admin by email
$sql = "SELECT admin_id, password_hash FROM admins WHERE email =:email";
$stmt =$pdo->prepare( $sql);
$stmt->execute([":email"=>$email]);

$admin =$stmt->fetch();

// If admin emails exists

 if ($admin){

 // Verify admin password
  if (password_verify($password, $admin["password_hash"])) {

    // Store admin in session
    $_SESSION["admin_id"] =$admin["admin_id"];

    // Redirect to dashboard
    header("Location: ./Dashboard.php");
    exit;
 } else{
  die("Incorrect password");
 }
 } else {
  die ("Email not found");
 }
 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel ="stylesheet" href="./css/style.css">
</head>

<body>
  

<img src="../bear.png" alt="Bear logo" class="bear-icon">
  <div class="container">
    <!--Left section-->
    <div class="form-section">
      <h2> Welcome </h2>
      <p> Please login in to access your dashboard</p>

      <!-- Login form-->
      <form action="Login.php" method="POST">

        <!--Email input-->
        <div class="form-group">
          <label for="email"> Email</label>
          <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password"required>
        </div>
      
        <button type="submit" class="login-btn">
          Login
        </button>
        </form>

        <div class="links">
          <p><a href="#forgot-popup" >Forgot Password</a></p>
          <p><a href="Register.php"> Create an account</a></p>
        </div>
        <p><a href="#forgot-popup">Forgot Password</a></p>
        <div id="forgot-popup" class="popup">
          <div class="popup-content">
            <a href="Login.php" class="close-btn">&times;</a>
            <h3>Reset Password</h3>
            <p>Please enter your email address</p>
            <form method="POST" action="send_reset.php">
              <input type="email" name="email" placeholder="Enter your admin email" required>
              <button type="submit">Send Reset Link</button>
            </form>
          </div>
        </div>

    </div>
     
    <div class="info-section">
      <h3> Cozy Bear booking</h3>
      <p> Mange your bookings, clients and payments using
        a affordable system.
      </p>
    </div>
  </div>
</body>
</html>




