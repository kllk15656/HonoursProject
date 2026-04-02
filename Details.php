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
  <link rel="stylesheet" href="./css/details.css"/>
  <title>Details</title>
</head>
<body>

<div class="progress-container">
  <div class="step complete">Services</div>
  <div class="arrow">→</div>
  <div class="step complete">Calendar</div>
  <div class="arrow">→</div>
  <div class="step active">Details</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Payment</div>
</div>

<div class="header">
  <h2>Details</h2>
</div>

<div class="detail-box">
  <form>

    <div class="details">
      <div class="fname"> 
        First Name:
        <input type="text" id="fname" name="fname">
      </div><br>

      <div class="lname"> 
        Last Name:
        <input type="text" id="lname" name="lname">
      </div><br>

      <div class="email"> 
        Email:
        <input type="email" id="email" name="email">
      </div><br>

      <div class="phone">
        Contact Number:
        <input type="number" id="phone" name="phone">
      </div>
    </div>

    <div class="confirmation">
      <br>
      <p>⚠️ *Please note: Deposits are non-refundable.*</p>
      <p>By ticking this box, you confirm you have read and agree to our booking terms*</p>
      <br>

      <div class="deposit">
        <label>
          I understand and agree that my deposit is non-refundable.
          <input type="checkbox" id="deposit" name="deposit">
        </label>
      </div>

      <br>

      <div class="terms">
        <label>
          I agree to the <a href="terms.html" target="_blank">terms and conditions</a>
          <input type="checkbox" id="terms" name="terms">
        </label>
      </div>

    </div>
  </form>
</div>

<p>*Reminder: your deposit is non-refundable once payment is made*</p>

<button class="payment-btn" disabled>Payment</button>

<script>
// Make sure date/time exist
const selectedDate = sessionStorage.getItem("selected_date");
const selectedTime = sessionStorage.getItem("selected_time");

if (!selectedDate || !selectedTime) {
  window.location.href = "Calendar.php?admin_id=<?php echo $admin_id; ?>";
}

// Validate form in real time
function validateForm() {
  const fname   = document.getElementById("fname").value.trim();
  const lname   = document.getElementById("lname").value.trim();
  const email   = document.getElementById("email").value.trim();
  const phone   = document.getElementById("phone").value.trim();
  const deposit = document.getElementById("deposit").checked;
  const terms   = document.getElementById("terms").checked;

  const paymentBtn = document.querySelector(".payment-btn");

  if (fname && lname && email && phone && deposit && terms) {
    paymentBtn.disabled = false;
  } else {
    paymentBtn.disabled = true;
  }
}

// Attach validation listeners
document.getElementById("fname").addEventListener("input", validateForm);
document.getElementById("lname").addEventListener("input", validateForm);
document.getElementById("email").addEventListener("input", validateForm);
document.getElementById("phone").addEventListener("input", validateForm);
document.getElementById("deposit").addEventListener("change", validateForm);
document.getElementById("terms").addEventListener("change", validateForm);

// Handle Payment button click
const paymentBtn = document.querySelector(".payment-btn");

paymentBtn.addEventListener("click", function (event) {
  event.preventDefault();

  const fname = document.getElementById("fname").value.trim();
  const lname = document.getElementById("lname").value.trim();
  const email = document.getElementById("email").value.trim();
  const phone = document.getElementById("phone").value.trim();

  sessionStorage.setItem("client_fname", fname);
  sessionStorage.setItem("client_lname", lname);
  sessionStorage.setItem("client_email", email);
  sessionStorage.setItem("client_phone", phone);

  window.location.href = "Payment.php?admin_id=<?php echo $admin_id; ?>";
});
</script>

</body>
</html>
