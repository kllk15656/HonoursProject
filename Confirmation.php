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
  <link rel="stylesheet" href="./css/confirmation.css"/>
  <title>Booking Confirmation</title>
</head>
<body>

<div class="form">
    <div class="confirmation">

        <label>Service:</label>
        <span id="service"></span><br>

        <label>Date:</label>
        <span id="date"></span><br>

        <label>Time:</label>
        <span id="time"></span><br>

        <label>Payment:</label>
        <span>Confirmed</span><br><br>

        <h2>Thank you for booking with us</h2>
        <h3>Looking forward to seeing you</h3>

    </div>
</div>

<script>
// Load booking details from sessionStorage
const service = sessionStorage.getItem("selected_service_name");
const date = sessionStorage.getItem("selected_date");
const time = sessionStorage.getItem("selected_time");

// Safety check: if missing, send back
if (!service || !date || !time) {
    window.location.href = "details.php?admin_id=<?php echo $admin_id; ?>";
}

// Insert into page
document.getElementById("service").textContent = service;
document.getElementById("date").textContent = date;
document.getElementById("time").textContent = time;

// OPTIONAL: prepare data to send to backend
const bookingData = {
    admin_id: "<?php echo $admin_id; ?>",
    service: service,
    date: date,
    time: time,
    fname: sessionStorage.getItem("client_fname"),
    lname: sessionStorage.getItem("client_lname"),
    email: sessionStorage.getItem("client_email"),
    phone: sessionStorage.getItem("client_phone")
};
fetch("save_booking.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(bookingData)
})
.then(res => res.json())
.then(data => {
    if (data.success) {
        console.log("Booking saved:", data);

        //clear sessionStorage now that booking is saved
        // sessionStorage.clear();

    } else {
        console.error("Booking failed:", data.error);
        alert("There was an issue saving your booking. Please try again.");
    }
})
.catch(err => {
    console.error("Fetch error:", err);
    alert("A network error occurred while saving your booking.");
});




</script>

</body>
</html>
