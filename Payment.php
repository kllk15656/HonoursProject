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
// Load cart
const cart = JSON.parse(sessionStorage.getItem("cart")) || [];

// Safety check: if cart is empty, send back
if (cart.length === 0) {
    window.location.href = "Services.php?admin_id=<?php echo $admin_id; ?>";
}

// Calculate totals
const depositTotal = cart.reduce((sum, s) => sum + s.price, 0);
const fullTotal = cart.reduce((sum, s) => sum + s.full_price, 0);

// Display service names
document.getElementById("service-name").textContent =
  cart.map(s => s.name).join(", ");

// Display deposit total
document.getElementById("service-price").textContent = "£" + depositTotal;
document.getElementById("total-price").textContent = "£" + depositTotal;

// ===============================
// FINAL FIXED PAYMENT FUNCTION
// ===============================
function fakePayment() {
    alert("This is a prototype. No real payment has been taken.");

    const payload = {
        admin_id: <?php echo $admin_id; ?>,
        services: cart,
        date: sessionStorage.getItem("selected_date"),
        time: sessionStorage.getItem("selected_time"),
        deposit_total: depositTotal,
        total_price: fullTotal,
        fname: sessionStorage.getItem("client_fname"),
        lname: sessionStorage.getItem("client_lname"),
        email: sessionStorage.getItem("client_email"),
        phone: sessionStorage.getItem("client_phone")
    };

    fetch("save_booking.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert("Booking failed: " + data.error);
            return;
        }

        sessionStorage.setItem("appointment_id", data.appointment_id);

        window.location.href = "Confirmation.php?admin_id=<?php echo $admin_id; ?>";
    })
    .catch(err => {
        alert("Network error: " + err);
    });
}
</script>

</body>
</html>
