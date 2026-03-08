<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

require_once "./db.php";

$admin_id = $_SESSION["admin_id"];

// Generate 10-minute slots from 09:00 to 17:00
function generateTimeSlots() {
    $slots = [];
    $start = strtotime("09:00");
    $end = strtotime("17:00");

    while ($start <= $end) {
        $slots[] = date("H:i", $start);
        $start = strtotime("+10 minutes", $start);
    }

    return $slots;
}

// Fetch availability for logged-in admin 
$stmt = $pdo->prepare("
    SELECT available_date, status
    FROM availability
    WHERE admin_id = ?
");
$stmt->execute([$admin_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$availability = [];
foreach ($rows as $row) {
    $status = strtolower(trim($row['status']));
    if ($status == 1 || $status === "1" || $status === "available") {
        $availability[$row['available_date']] = "available";
    } else {
        $availability[$row['available_date']] = "unavailable";
    }
}
?>
<!DOCTYPE html>
<html lang ="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href="./css/admin-calendar.css">
</head>

<body>

    <!-- TOP NAV -->
    <div class="top-nav">
        <h1>Admin Dashboard</h1>
        <ul>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="Setting.php">Settings</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- SIDE NAV -->
    <div class="side-nav">
        <a href="Admin-Calendar.php" class="active">Calendar</a>
        <a href="Categories.php">Categories</a>
        <a href="Services.php">Services</a>
        <a href="Clients.php">Clients</a>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- HEADER -->
        <div class="header">
            <h2>Calendar</h2>
            <p>Manage your schedule by selecting availability and times</p>

            <div class="legend">
                <div class="legend-item">
                    <span class="legend-box unavailable"></span> Unavailable
                </div>
                <div class="legend-item">
                    <span class="legend-box available"></span> Available
                </div>
            </div>
        </div>

        <!-- CALENDAR CARD -->
        <div class="card">
            <div class="calendar-layout">

                <div class="calendar">
                    <div class="calendar-header">
                        <button class="month-btn" onclick="changeMonth(-1)">&#8592;</button>
                        <h2 id="month-title"></h2>
                        <button class="month-btn" onclick="changeMonth(1)">&#8594;</button>
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

                    <!-- JS will fill this -->
                    <div class="calendar-grid" id="calendar-grid"></div>

                </div>

                <div class="time-panel">
                    <h3>Available Times</h3>
                    <p id="selected-date">Select a date</p><br>
                    <div class="times" id="times"></div>
                </div>

            </div>
        </div>
    </div>

<script>

// Display by current month

const today = new Date();
let currentYear = today.getFullYear();
let currentMonth = today.getMonth();


const monthNames = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
];

// Availability from PHP
const availability = <?= json_encode($availability) ?>;

// Time slots from PHP
const timeSlots = <?= json_encode(generateTimeSlots()) ?>;

function renderCalendar() {
    document.getElementById("month-title").innerText =
        monthNames[currentMonth] + " " + currentYear;

    buildCalendarGrid();
}

function changeMonth(direction) {
    currentMonth += direction;

    if (currentMonth < 0) {
        currentMonth = 11;
        currentYear--;
    } else if (currentMonth > 11) {
        currentMonth = 0;
        currentYear++;
    }

    renderCalendar();
}


// Build calendar grid

function buildCalendarGrid() {
    const grid = document.getElementById("calendar-grid");
    grid.innerHTML = "";

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();

    const offset = firstDay === 0 ? 6 : firstDay - 1;

    for (let i = 0; i < offset; i++) {
        grid.appendChild(document.createElement("div"));
    }

    for (let d = 1; d <= daysInMonth; d++) {
        const date = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;

        const dayDiv = document.createElement("div");
        dayDiv.classList.add("day");

        if (availability[date] === "available") {
            dayDiv.classList.add("available-day");
        } else {
            dayDiv.classList.add("unavailable-day");
        }

        dayDiv.innerHTML = `<span class="day-number">${d}</span>`;
        const today = new Date();
        const isToday =
        today.getFullYear() === currentYear &&
        today.getMonth() === currentMonth &&
        today.getDate() === d;
        if (isToday) {
            dayDiv.classList.add("today");
        }


        if (availability[date] === "available") {
            const btn = document.createElement("button");
            btn.innerText = "Times";
            btn.classList.add("times-btn");
            btn.onclick = (e) => {
                e.stopPropagation();
                openTimes(date);
            };
            dayDiv.appendChild(btn);
        }

        dayDiv.onclick = () => toggleDay(date, dayDiv);

        grid.appendChild(dayDiv);
    }
}

window.onload = renderCalendar;

// Day toggle
function toggleDay(date, element) {
    const isNowAvailable = element.classList.contains("unavailable-day");

    element.classList.toggle("available-day", isNowAvailable);
    element.classList.toggle("unavailable-day", !isNowAvailable);

    const oldBtn = element.querySelector(".times-btn");
    if (oldBtn) oldBtn.remove();

    if (isNowAvailable) {
        const btn = document.createElement("button");
        btn.innerText = "Times";
        btn.classList.add("times-btn");
        btn.onclick = (e) => {
            e.stopPropagation();
            openTimes(date);
        };
        element.appendChild(btn);
    }

    fetch("update_availability.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `date=${encodeURIComponent(date)}&status=${isNowAvailable ? 1 : 0}`
    });
}


// Times panel

const selectedDateText = document.getElementById("selected-date");
const timesDiv = document.getElementById("times");

function openTimes(date) {
    selectedDateText.innerText = date;
    timesDiv.innerHTML = "";

    fetch("get_times.php?date=" + encodeURIComponent(date))
        .then(res => res.json())
        .then(data => {
            const saved = data.times || [];

            timeSlots.forEach(t => {
                const btn = document.createElement("button");
                btn.innerText = t;

                if (saved.includes(t)) {
                    btn.classList.add("available-time");
                }

                btn.onclick = () => {
                    btn.classList.toggle("available-time");
                };

                timesDiv.appendChild(btn);
            });

            const saveBtn = document.createElement("button");
            saveBtn.innerText = "Save Times";
            saveBtn.classList.add("save-times-btn");
            saveBtn.onclick = () => saveTimes(date);
            timesDiv.appendChild(saveBtn);
        });
}

function saveTimes(date) {
    const selected = [];

    document.querySelectorAll(".times button.available-time").forEach(btn => {
        selected.push(btn.innerText);
    });

    fetch("save_times.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `date=${encodeURIComponent(date)}&times=${encodeURIComponent(JSON.stringify(selected))}`
    }).then(() => {
        alert("Times saved");
    });
}
</script>

</body>
</html>
