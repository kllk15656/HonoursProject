<?php
$admin_id = $_GET['admin_id'] ?? null;

if (!$admin_id) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./css/payment.css"/>
  <title>Payment</title>
</head>
<body>

<div class="progress-container">
  <div class="step complete">Services</div>
  <div class="arrow">→</div>
  <div class="step complete">Calendar</div>
  <div class="arrow">→</div>
  <div class="step complete">Details</div>
  <div class="arrow">→</div>
  <div class="step active">Payment</div>
</div>

<div class="header">
  <h2>Payment</h2>
</div>

<div class="payment-card">

    <div class="summary">
        <span id="service-name">Service</span>
        <span id="service-price">£0</span>
    </div>

    <div class="summary">
        <span>Total</span>
        <span id="total-price">£0</span>
    </div>

    <hr>

    <button class="apple-btn">ApplePay</button>
    <div class="or">OR</div>

    <input type="email" placeholder="Email" disabled>
    <p><strong>Payment method</strong></p>

    <input type="text" placeholder="Full name" disabled>
    <input type="text" placeholder="Billing address" disabled>
    <input type="text" placeholder="Card number" disabled>
    <input type="text" placeholder="MM/YY   CVC" disabled>

    <button class="method-btn" disabled>Clearpay</button>
    <button class="method-btn" disabled>Klarna</button>

    <button class="pay-btn" disabled>Pay Now</button>
</div>

<!-- Prototype Popup -->
<div class="overlay">
    <div class="payment-box">
        <h2>Payment</h2>
        <div class="warning">
            ⚠️ Payment system is not available.<br>
            This is a prototype demonstration.
        </div>
        <button class="pay" onclick="fakePayment()">
            Confirm Payment
        </button>
    </div>
</div>

<script>
// Load booking + client details from sessionStorage
const booking = {
    service: sessionStorage.getItem("selected_service_name"),
    price: sessionStorage.getItem("selected_service_price"),
    date: sessionStorage.getItem("selected_date"),
    time: sessionStorage.getItem("selected_time"),
    fname: sessionStorage.getItem("client_fname"),
    lname: sessionStorage.getItem("client_lname"),
    email: sessionStorage.getItem("client_email"),
    phone: sessionStorage.getItem("client_phone")
};

// Safety check: if no booking info, send back
if (!booking.service || !booking.date || !booking.time) {
    window.location.href = "Details.php?admin_id=<?php echo $admin_id; ?>";
}

// Update the summary box
document.getElementById("service-name").textContent = booking.service || "Service";
document.getElementById("service-price").textContent = "£" + (booking.price || "0");
document.getElementById("total-price").textContent = "£" + (booking.price || "0");

// Save confirmation flag
function saveBeforeConfirmation() {
    sessionStorage.setItem("booking_confirmed", "true");
}

// Fake payment for prototype
function fakePayment() {
    alert("This is a prototype. No real payment has been taken.");

    saveBeforeConfirmation();

    // Redirect to confirmation with admin_id
    window.location.href = "Confirmation.php?admin_id=<?php echo $admin_id; ?>";
}
</script>

</body>
</html>
