<?php
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit();
}

// Connect to database
require "./db.php";

// Logged-in admin ID
$admin_id = $_SESSION["admin_id"];

// Fetch admin business info including website_url
$stmt = $pdo->prepare("SELECT business_name, website_url FROM admins WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Extract website URL
$website_url = $admin['website_url'] ?? "";

// Default booking link (your system)
$public_link = "https://Welcome.php?admin_id=" . $admin_id;

// If admin has their own website, prepend it
if (!empty($website_url)) {
    $website_url = rtrim($website_url, "/");
    $public_link = $website_url . "/Welcome.php?admin_id=" . $admin_id;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/dashboard.css">
    <title>Admin Dashboard</title>
</head>

<body>

    <!-- Top Navigation -->
    <div class="top-nav">
        <h1>Admin Dashboard</h1>
         <div class="hamburger" onclick="toggleMenu()">
        <img src="./images/menu.png" alt="Menu">
    </div>
    </div>

    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="Admin-Calendar.php">Calendar</a>
        <a href="Categories.php">Categories</a>
        <a href="Services.php">Services</a>
        <a href="Clients.php">Clients</a>

        <p class="mobile-nav-label">Navigation</p>

        <div class="mobile-nav-links"> 
            <a href="Dashboard.php">Dashboard</a>
            <a href="Settings.php">Settings</a>
            <a href="Logout.php">Log Out</a>
        </div>
    </div>
        <div class="overlay" onclick="toggleMenu()"></div>


    <!-- Main Content -->
    <div class="main-content">
    <div class="card">

        <!-- Welcome Section -->
        <div class="preview">
            <h1>Welcome to your Dashboard</h1>
            <p>This is your personal dashboard. You can view your client page from here.</p>

            <a href="../Welcome.php?admin_id=<?= $_SESSION['admin_id'] ?>" class="btn">
                Client Page
            </a>

            <p class="public-link-label">Your public booking link:</p>

            <input 
                type="text" 
                class="public-link-input"
                value="<?= htmlspecialchars($public_link) ?>" 
                readonly
            >
        </div>


            <!-- Recent Bookings -->
            <div class="recent-table">
                <h2>Recent Booking</h2>

                <div class="info">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                        <span style="font-weight: bold; font-size: 14px;">New booking will appear</span>
                        <span style="font-size: 15px;">&#8594;</span>
                        <div style="width: 20px; height: 20px; background-color: #d3bb31; border-radius: 4px; border: 1px solid #ccc;"></div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Services</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $sql = "
                        SELECT 
                            a.appointment_id,
                            CONCAT(c.first_name, ' ', c.last_name) AS name,
                            a.date AS appointment_date,
                            DATE_FORMAT(a.start_time, '%h:%i %p') AS time,
                            GROUP_CONCAT(aps.service_name ORDER BY aps.order_index SEPARATOR ', ') AS services,
                            a.is_seen
                        FROM appointments a
                        JOIN clients c ON a.client_id = c.client_id
                        LEFT JOIN appointment_services aps ON a.appointment_id = aps.appointment_id
                        WHERE a.admin_id = :admin_id
                        GROUP BY a.appointment_id
                        ORDER BY a.created_at DESC
                        LIMIT 5;
                        ";

                        $stmt = $pdo->prepare($sql);
                        $stmt->execute([":admin_id" => $_SESSION["admin_id"]]);
                        $recentBookings = $stmt->fetchAll();

                        if ($recentBookings) {
                            foreach ($recentBookings as $row) {

                                $rowClass = ($row['is_seen'] == 0) ? 'new-booking' : '';
                                $label = ($row['is_seen'] == 0) ? "New Booking" : "";

                                echo "<tr class='{$rowClass}' data-id='{$row['appointment_id']}'>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['appointment_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['services']) . "</td>";
                                echo "<td>" . htmlspecialchars($label) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No recent bookings found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Upcoming Bookings -->
            <div class="upcoming-table">
                <h2>Upcoming Booking</h2>

                <div class="info">
                    <div style="display: flex; align-items: center; gap: 5px; margin-bottom: 15px;">
                        <span style="font-weight: bold; font-size: 14px;">Today Appointment</span>
                        <span style="font-size: 10px;">&#8594;</span>
                        <div style="width: 20px; height: 20px; background-color: #3185d3; border-radius: 4px; border: 1px solid #ccc;"></div>

                        <span style="font-weight: bold; font-size: 14px; ">This Week Appointment</span>
                        <span style="font-size: 1px;">&#8594;</span>
                        <div style="width: 20px; height: 20px; background-color: #a031d3; border-radius: 4px; border: 1px solid #ccc;"></div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Reminder</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $sqlUpcoming = "
                        SELECT 
                            CONCAT(c.first_name, ' ', c.last_name) AS name,
                            a.date AS appointment_date,
                            DATE_FORMAT(a.start_time, '%h:%i %p') AS time,
                            GROUP_CONCAT(aps.service_name ORDER BY aps.order_index SEPARATOR ', ') AS services
                        FROM appointments a
                        JOIN clients c ON a.client_id = c.client_id
                        LEFT JOIN appointment_services aps ON a.appointment_id = aps.appointment_id
                        WHERE a.admin_id = :admin_id
                        AND a.date >= CURDATE()
                        GROUP BY a.appointment_id
                        ORDER BY a.date, a.start_time;
                        ";

                        $stmt = $pdo->prepare($sqlUpcoming);
                        $stmt->execute([":admin_id" => $_SESSION["admin_id"]]);
                        $upcoming = $stmt->fetchAll();

                        if ($upcoming) {

                            $today = date('Y-m-d');
                            $weekAhead = date('Y-m-d', strtotime('+7 days'));

                            foreach ($upcoming as $row) {

                                if ($row['appointment_date'] === $today) {
                                    $rowClass = 'today';
                                    $label = "Today";
                                } elseif ($row['appointment_date'] <= $weekAhead) {
                                    $rowClass = 'week';
                                    $label = "This Week";
                                } else {
                                    $rowClass = '';
                                    $label = "";
                                }

                                echo "<tr class='{$rowClass}'>";
                                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['appointment_date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                                echo "<td>" . htmlspecialchars($label) . "</td>";
                                echo "<td><button class='remind-btn'>Reminder</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No upcoming appointments.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <script>
document.querySelectorAll(".recent-table tr.new-booking").forEach(row => {
    row.addEventListener("click", function () {
        let id = this.dataset.id;

        fetch("mark_seen.php?id=" + id)
            .then(response => response.text())
            .then(data => {
                this.classList.remove("new-booking");
                this.querySelector("td:last-child").textContent = "";
            });
    });
});

function toggleMenu() {
    console.log('toggleMenu fired');

    const menu = document.querySelector('.side-nav');
    const overlay = document.querySelector('.overlay');

    console.log('before:', menu.className);
    menu.classList.toggle('open');
    overlay.classList.toggle('show');
    console.log('after:', menu.className);
}

</script>


</body>
</html>
