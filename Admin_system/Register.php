<?php
//Start session
session_start();

// requesting DB connection
require "./db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {


// Collect the form data
$business = $_POST["business"];
$name = $_POST["name"];
$email =$_POST["email"];
$password = $_POST["password"];
$confirm =  $_POST["confirm"];

// Checking if password matches
if ($password !== $confirm){
    die("Password doesn't match");
}

//Hashing password
$hashPassword = password_hash($password, PASSWORD_DEFAULT);

//Insert admin details into database
$sql =  "INSERT INTO admins (business_name, admin_name, email, password_hash)
VALUES (:business, :name, :email, :password_hash)";

$stmt = $pdo->prepare($sql);

try{
    $stmt->execute([
        ":business" => $business,
        ":name" => $name,
        ":email" => $email,
        ":password_hash" => $hashPassword
    ]);

    //Admin id stored in session
    $_SESSION["admin_id"] = $pdo-> lastInsertId();

    //Redirect to dashboard page
    header("Location: ./Dashboard.php");
    exit;
} catch (PDOException $e){
    //handles duplicate email
    if ($e->getCode()== 23000){
        die("Email already exists.");
    }
    die("Registration  failed.");
    }

}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Register | Booking System</title>
        <link rel ="stylesheet" href="./css/register.css">
    </head>

    <body>
        <img src="../bear.png" alt="Bear logo" class="bear-icon">
        <div class="container">
        <!-- Left panel -->
        <div class="info-section">
            <h3> Create an Account</h3>
            <p> Register your business and start managing<br>
                appintments with ease.
            </p>
        </div>
        <!-- Right panel-->
         <div class="form-section">
            <h2> Register</h2>
            <p> Create a new account</p>
            <form action="Register.php" method="POST">

                <div class ="form-group">
                    <label for="business"> Business Name</label>
                    <input type="text" id="business" name="business" required>
                </div>
                <div class ="form-group">
                    <label for="name"> Owner Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class ="form-group">
                    <label for="email"> Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class ="form-group">
                    <label for="password"> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class ="form-group">
                    <label for="confirm"> Confirm password</label>
                    <input type="password" id="confirm" name="confirm" required>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
            <div class="links">
                <p><a href="Login.php">Already have an account ?</a></p>
            </div>
            </div>
         </div>
    </body>
</html>