<?php
// Get admin_id from query string, e.g. Calendar.php?admin_id=1
if (!isset($_GET['admin_id'])) {
    die("Admin not found.");
}
$admin_id = (int) $_GET['admin_id'];
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=shopping_cart" />

  <link rel="stylesheet" href="./css/calendar.css"/>

  <title>Calender</title>
  <style>
  .material-symbols-outlined {
     font-variation-settings:
     'FILL' 0,
     'wght' 400,
     'GRAD' 0,
     'opsz' 24
}
</style>
  </style>
</head>
<body>

  <!-- Cart and Hamburger Menu -->
  <header>
   <div id="cart-icon">
    <span class="material-symbols-outlined">
shopping_cart
</span>
 
</div>
</header>
<div class="progress-container">
  <div class="step complete">Services</div>
  <div class="arrow">→</div>
  <div class="step active">Calendar</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Details</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Payment</div>
</div>
<!-- go back to welcome page-->

<div class="header">
  <h2>Details</h2>
</div>




<div class="calendar-layout">

  <div class="info">
  <div style="display: flex; align-items: center; gap: 8px; font-family: Arial, sans-serif; margin-bottom: 15px;">
    <span style="font-weight: bold;">Unavailable</span>
    <span style="font-size: 20px;">&#8594;</span>
    <div style="width: 20px; height: 20px; background-color: #d33131; border-radius: 4px; border: 1px solid #ccc;"></div>
  </div>
  <div style="display: flex; align-items: center; gap: 10px; font-family: Arial, sans-serif; margin-bottom: 15px;">
    <span style="font-weight: bold;">Available</span>
    <span style="font-size: 20px;">&#8594;</span>
    <div style="width: 20px; height: 20px; background-color: #32c232; border-radius: 4px; border: 1px solid #ccc;"></div>
  </div>
</div>
<!-- Calendar - buttons need updating -->
<div class="calendar">
  <div class="calendar-header">
    <button id="prev-month">&lt;</button>
    <h2></h2>
    <button id="next-month">&gt;</button>
  </div>
<div class="weekdays">
  <span>Mon</span>
  <span>Tue</span>
  <span>Wed</span>
  <span>Thu</span>
  <span>Fri</span>
  <span>Sat</span>
  <span>Sun</span>
</div>

  <div class="calendar-grid" id="calendar"></div>
</div>

    <!-- Time Slots -->
<div class="time-panel">
  <h3>Available Times</h3>
  <p id="selected-date">Select a date</p><br>
    <button id="clear-selection" class="clear-btn">Clear Selection</button>
    <br>
  <div class="times" id="times"></div>
</div>

</div>
</div>
<!-- Cart Sidebar -->
<div id="side-cart">
  <div class="cart-panel"> 
    <button id="close-cart">X</button>
    <h3> Booking Cart</h3>
    <!-- Cart contents go here -->
    <p>Your cart is empty</p>
    <div class="cart-total">
      <strong> Total Price</strong>
      <Strong> £ 0</Strong>
    </div>
    <a href="Details.html" class="continue-btn">Continue</a>
  </div>


<script>

// Cart toggle functionality
    const adminId = <?= $admin_id ?>;
    const cartIcon = document.getElementById('cart-icon');
    const sideCart = document.getElementById('side-cart');
    const closeCart = document.getElementById('close-cart');
    const cartPanel = document.querySelector('.cart-panel');

    // Open cart
    cartIcon.addEventListener('click', (e) => {
      e.stopPropagation(); //Prevents document click
      sideCart.classList.add('open');
    });

    // Close cart
    closeCart.addEventListener('click', (e) => {
      e.stopPropagation();
      sideCart.classList.remove('open');
    });

    sideCart.addEventListener('click',() =>{
      sideCart.classList.remove('open');
    });

    cartPanel.addEventListener('click',(e)=> {
      e.stopPropagation();
    })


// Availability will be loaded from the server
let availability = {};

// Fetch real availability from admin calendar
fetch("get_client_availability.php?admin_id=" + adminId)
  .then(res => res.json())
  .then(data => {
    availability = data || {};
    buildCalendar();   // only build calendar once data is loaded
  });

  // Get today's date
const today = new Date();

// Extract month + year
let currentMonth = today.getMonth();   // 0–11
let currentYear = today.getFullYear();

// Month names
const monthNames = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

// Update header text
document.querySelector(".calendar-header h2").innerText =
  `${monthNames[currentMonth]} ${currentYear}`;


const calendar = document.getElementById("calendar");
const times = document.getElementById("times");
const selectedDate = document.getElementById("selected-date");

function buildCalendar() {
  calendar.innerHTML = "";

  const today = new Date();
  const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

  for (let d = 1; d <= daysInMonth; d++) {
    const date = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(d).padStart(2, "0")}`;

    const div = document.createElement("div");
    div.classList.add("day");

    if (
      d === today.getDate() &&
      currentMonth === today.getMonth() &&
      currentYear === today.getFullYear()
    ) {
      div.classList.add("today");
    }

    const thisDate = new Date(currentYear, currentMonth, d);
    if (thisDate < today.setHours(0,0,0,0)) {
      div.classList.add("unavailable");
      div.classList.add("past");
      div.innerText = d;
      calendar.appendChild(div);
      continue;
    }

    if (availability[date] && availability[date].length > 0) {
      div.classList.add("available");
      div.onclick = () => showTimes(date);
    } else {
      div.classList.add("unavailable");
    }

    div.innerText = d;
    calendar.appendChild(div);
  }
}


// Show available times for selected date
function showTimes(date) {
  selectedDate.innerText = date;
  times.innerHTML = "";

  const slots = availability[date] || [];

  if (slots.length === 0) {
    times.innerHTML = "<p>No available times for this date</p>";
    return;
  }

  slots.forEach(t => {
    const btn = document.createElement("button");
    btn.innerText = t;

    btn.onclick = () => {
        // Save selected date + time
        sessionStorage.setItem("selected_date", date);
        sessionStorage.setItem("selected_time", t);

        // Redirect to details page
        window.location.href = "Details.php?admin_id=" + adminId;
    };

    times.appendChild(btn);
  });
}
document.getElementById("clear-selection").onclick = () => {
    // Remove stored date/time
    sessionStorage.removeItem("selected_date");
    sessionStorage.removeItem("selected_time");

    // Reset UI
    selectedDate.innerText = "Select a date";
    times.innerHTML = "";
};



</script>
    </div>

</body>
</html>