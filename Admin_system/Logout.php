
<?php
session_start();
session_unset();
session_destroy();

header("Location: ./Login.php")
?>

<!DOCTYPE html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="./css/style.css">
    <title>Logged Out</title>
</head>

<body>
    <div class="container">
    <img src="../bear.png" alt="Bear logo" class="bear-icon">
    <div class="form-section" style= "width:100% ; text-align:center;">
        <h2>You,ve logged out</h2>
        <p> thank you for using Cozy Bear Booking</p>

        <p style="margin-top: 20px;">
            Your session has ended successfully
        </p>

        <a href="Login.html">
            <button class="login-btn " style="width: 20%;">
          Back to Login
        </button>
        </a>

    </div>
    </div>


</body>
</html>

