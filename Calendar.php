<?php
session_start();
$admin_id = $_GET['admin_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Calendar</title>
  <link rel="stylesheet" href="./css/calendar.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body>

<header>
  <div id="cart-icon">
    <span class="material-symbols-outlined">shopping_cart</span>
  </div>
</header>

<!-- Progress Bar -->
<div class="progress-container">
  <div class="step complete">Services</div>
  <div class="arrow">→</div>
  <div class="step active">Calendar</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Details</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Payment</div>
</div>

<div class="header"><h2>Calendar</h2></div>

<div class="calendar-layout">

  <!-- Legend -->
  <div class="info">
    <p><strong>Unavailable:</strong> 🔴</p>
    <p><strong>Available:</strong> 🟢</p>
  </div>

  <!-- Calendar -->
  <div class="calendar">
    <div class="calendar-header">
      <button id="prev-month">←</button>
      <h2></h2>
      <button id="next-month">→</button>
    </div>

    <div class="weekdays">
      <div>Mon</div><div>Tue</div><div>Wed</div>
      <div>Thu</div><div>Fri</div><div>Sat</div><div>Sun</div>
    </div>

    <div id="calendar" class="calendar-grid"></div>
  </div>

  <!-- Time Panel -->
  <div class="time-panel">
    <h3>Available Times</h3>
    <p id="time-message">Select a date</p>
    <div id="times"></div>
    <button id="clear-selection">Clear Selection</button>
  </div>

</div>

<!-- CART -->
<div id="side-cart">
  <div class="cart-panel">
    <button id="close-cart">X</button>
    <h3>Booking Cart</h3>
    <p id="cart-timer"></p>

    <div class="cart-scroll">
      <table class="cart-table">
        <thead>
          <tr><th>Services</th><th>Price</th></tr>
        </thead>
        <tbody id="cart-items"></tbody>
      </table>
    </div>

    <div class="cart-selection-info">
      <strong>Date:</strong> <span id="cart-date">Not selected</span><br>
      <strong>Time:</strong> <span id="cart-time">Not selected</span>
    </div>

    <div class="cart-total">
      <strong>Total Price</strong>
      <strong id="total-price">£0</strong>
    </div>

    <a href="Details.php?admin_id=<?= $admin_id ?>" class="continue-btn">Continue</a>
  </div>
</div>
<script>
sessionStorage.setItem("service_duration", 60); // from your service table: duration_minutes
sessionStorage.setItem("cleanup_time", 10);

// ADMIN ID
const adminId = "<?= $admin_id ?>";


// BLock time off
function getBlockedSlots(startTimeHHMM, totalMinutes) {
    const blocked = [];

    let [h, m] = startTimeHHMM.split(":").map(Number);
    const steps = totalMinutes / 10;

    for (let i = 0; i <= steps; i++) {
        const hh = String(h).padStart(2, "0");
        const mm = String(m).padStart(2, "0");
        blocked.push(`${hh}:${mm}:00`);

        m += 10;
        if (m >= 60) {
            m -= 60;
            h += 1;
        }
    }

    return blocked;
}


// --- Function B: Filter times by duration ---
function filterTimesByDuration(slots, duration, cleanup) {
    const total = duration + cleanup;
    const neededSlots = total / 10;

    const valid = [];

    for (let i = 0; i < slots.length; i++) {
        let ok = true;

        for (let j = 0; j < neededSlots; j++) {
            if (!slots[i + j]) {
                ok = false;
                break;
            }
        }

        if (ok) valid.push(slots[i]);
    }

    return valid;
}

function showTimes(dateStr) {
    const selectedDate = dateStr;

    // Save selected date
    sessionStorage.setItem("selected_date", dateStr);
    updateSelectionInfo();

    // Highlight selected day
    document.querySelectorAll(".day").forEach(d => d.classList.remove("selected-day"));
    const dayNum = dateStr.split("-")[2];
    const clicked = [...document.querySelectorAll(".day")].find(d => d.innerText == dayNum);
    if (clicked) clicked.classList.add("selected-day");

    // Load available slots
    let slots = availability[dateStr] || [];
    timesDiv.innerHTML = "";

    if (slots.length === 0) {
        timeMessage.textContent = "No available times";
        return;
    }

    // ⭐ Duration + cleanup (10 mins per service)
    const duration = Number(sessionStorage.getItem("service_duration")) || 0;
    const cleanup = cart.length * 10;  // 10 mins per service

    // Filter times based on duration + cleanup
    if (duration > 0) {
        slots = filterTimesByDuration(slots, duration, cleanup);
    }

    if (slots.length === 0) {
        timeMessage.textContent = "No times available for this service duration";
        return;
    }

    timeMessage.textContent = "Select a time";

    // Build time buttons
    slots.forEach(t => {
        const cleanTime = t.substring(0, 5);
        const btn = document.createElement("button");
        btn.innerText = cleanTime;

        btn.onclick = () => {
            // Highlight selected time
            document.querySelectorAll("#times button").forEach(b => b.classList.remove("selected"));
            btn.classList.add("selected");

            // Save selected time
            sessionStorage.setItem("selected_time", cleanTime);
            updateSelectionInfo();

            // Calculate blocked slots (duration + cleanup)
            const total = duration + cleanup;
            const blockedSlots = getBlockedSlots(cleanTime, total);

            // ⭐ PROTOTYPE: remove blocked slots locally
            availability[selectedDate] = availability[selectedDate].filter(
                slot => !blockedSlots.includes(slot)
            );

            // Rebuild UI
            buildCalendar();
            showTimes(selectedDate);

            //  NO REDIRECT HERE — user must click Continue
        };

        timesDiv.appendChild(btn);
    });
}
  
// CART PANEL TOGGLE

const cartIcon = document.getElementById('cart-icon');
const sideCart = document.getElementById('side-cart');
const closeCart = document.getElementById('close-cart');
const cartPanel = document.querySelector('.cart-panel');

cartIcon.addEventListener('click', e => {
  e.stopPropagation();
  sideCart.classList.add('open');
});

closeCart.addEventListener('click', () => sideCart.classList.remove('open'));
sideCart.addEventListener('click', () => sideCart.classList.remove('open'));
cartPanel.addEventListener('click', e => e.stopPropagation());


// LOAD CART + REDIRECT IF EMPTY

let cart = JSON.parse(sessionStorage.getItem("cart")) || [];

// ⭐ Redirect immediately if cart is empty
if (cart.length === 0) {
  window.location.href = "Service.php?admin_id=" + adminId;
}


// UPDATE CART + REMOVE BUTTON LOGIC

function updateSelectionInfo() {
  document.getElementById("cart-date").textContent =
    sessionStorage.getItem("selected_date") || "Not selected";

  document.getElementById("cart-time").textContent =
    sessionStorage.getItem("selected_time") || "Not selected";
}

function updateCart() {
  const cartItems = document.getElementById("cart-items");
  const totalPrice = document.getElementById("total-price");

  if (cart.length === 0) {
    cartItems.innerHTML = `<tr><td colspan="2" style="text-align:center;">Your cart is empty</td></tr>`;
    totalPrice.textContent = "£0";
    updateSelectionInfo();
    return;
  }

  cartItems.innerHTML = cart.map((item, index) => `
    <tr>
      <td>${item.name}</td>
      <td>£${item.price}
        <button class="remove-btn" data-index="${index}">Remove</button>
      </td>
    </tr>
  `).join('');

  const total = cart.reduce((sum, item) => sum + Number(item.price), 0);
  totalPrice.textContent = "£" + total;

  // REMOVE BUTTON LOGIC
  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      cart.splice(btn.dataset.index, 1);
      sessionStorage.setItem("cart", JSON.stringify(cart));
      updateCart();

      // ⭐ Redirect if last service removed
      if (cart.length === 0) {
        window.location.href = "Service.php?admin_id=" + adminId;
      }
    });
  });

  updateSelectionInfo();
}

// CART TIMER

function updateCartTimer() {
  const timerElement = document.getElementById("cart-timer");
  const timestamp = sessionStorage.getItem("cartTimestamp");

  if (!timestamp) {
    timerElement.textContent = "";
    return;
  }

  const expiryMinutes = 30;
  const now = Date.now();
  const expiryTime = parseInt(timestamp) + expiryMinutes * 60 * 1000;
  const diffMs = expiryTime - now;

  if (diffMs <= 0) {
    timerElement.textContent = "Cart expired";
    sessionStorage.removeItem("cart");
    sessionStorage.removeItem("cartTimestamp");
    cart = [];
    updateCart();
    return;
  }

  const minutes = Math.floor(diffMs / 1000 / 60);
  const seconds = Math.floor((diffMs / 1000) % 60);

  timerElement.textContent = `Cart expires in ${minutes}:${seconds.toString().padStart(2, '0')}`;
}

setInterval(updateCartTimer, 1000);

updateCart();
updateSelectionInfo();
updateCartTimer();


// LOAD AVAILABILITY (DATE → TIMES)

let availability = {};

fetch("get_client_availability.php?admin_id=" + adminId)
  .then(res => res.json())
  .then(data => {
    availability = data || {};
    buildCalendar();
    restoreSelection();
  });


// CALENDAR SYSTEM

const today = new Date();
let currentMonth = today.getMonth();
let currentYear = today.getFullYear();

const monthNames = [
  "January","February","March","April","May","June",
  "July","August","September","October","November","December"
];

const calendarHeader = document.querySelector(".calendar-header h2");
const calendar = document.getElementById("calendar");
const timesDiv = document.getElementById("times");
const timeMessage = document.getElementById("time-message");

function updateMonthLabel() {
  calendarHeader.innerText = `${monthNames[currentMonth]} ${currentYear}`;
}

updateMonthLabel();

function buildCalendar() {
  calendar.innerHTML = "";

  const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

  const firstDay = new Date(currentYear, currentMonth, 1);
  const startDay = (firstDay.getDay() + 6) % 7; // Monday = 0

  // Add empty boxes before the 1st
  for (let i = 0; i < startDay; i++) {
    const empty = document.createElement("div");
    empty.classList.add("empty");
    calendar.appendChild(empty);
  }

  // Create today's date at midnight
  const todayMid = new Date();
  todayMid.setHours(0, 0, 0, 0);

  // LOOP THROUGH DAYS
  for (let day = 1; day <= daysInMonth; day++) {

    const div = document.createElement("div");
    div.classList.add("day");
    div.textContent = day;

    // Create this date
    const thisDate = new Date(currentYear, currentMonth, day);
    thisDate.setHours(0, 0, 0, 0);

    const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, "0")}-${String(day).padStart(2, "0")}`;

    // Disable past days
    if (thisDate.getTime() < todayMid.getTime()) {
      div.classList.add("unavailable");
      calendar.appendChild(div);
      continue;
    }

    // Check availability for this date
    const slots = availability[dateStr] || [];

    if (slots.length > 0) {
      // Day has available times
      div.classList.add("available");
      div.onclick = () => showTimes(dateStr);
    } else {
      // No times available
      div.classList.add("unavailable");
    }

    // Highlight selected day
    if (sessionStorage.getItem("selected_date") === dateStr) {
      div.classList.add("selected-day");
    }

    calendar.appendChild(div);
  }
}


document.getElementById("next-month").onclick = () => {
  currentMonth++;
  if (currentMonth > 11) { currentMonth = 0; currentYear++; }
  updateMonthLabel();
  buildCalendar();
  restoreSelection();
};

document.getElementById("prev-month").onclick = () => {
  currentMonth--;
  if (currentMonth < 0) { currentMonth = 11; currentYear--; }
  updateMonthLabel();
  buildCalendar();
  restoreSelection();
};



// RESTORE SELECTION

function restoreSelection() {
  const savedDate = sessionStorage.getItem("selected_date");
  if (savedDate && availability[savedDate]) {
    showTimes(savedDate);
  } else {
    timesDiv.innerHTML = "";
    timeMessage.textContent = "Select a date";
  }
}


// CLEAR SELECTION

document.getElementById("clear-selection").addEventListener("click", () => {
  sessionStorage.removeItem("selected_date");
  sessionStorage.removeItem("selected_time");
  document.querySelectorAll(".day").forEach(d => d.classList.remove("selected-day"));
  timesDiv.innerHTML = "";
  timeMessage.textContent = "Select a date";
  updateSelectionInfo();
});

document.getElementById("continue-btn").onclick = () => {
    const date = sessionStorage.getItem("selected_date");
    const time = sessionStorage.getItem("selected_time");

    if (!date || !time) {
        alert("Please select a date and time before continuing.");
        return;
    }

    window.location.href = "Details.php?admin_id=" + adminId;
};


</script>




</body>
</html>
