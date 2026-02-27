<?php
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit();
}

// Connect to database
require "./db.php";
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
        <ul>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="Setting.php">Settings</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Side Navigation -->
    <div class="side-nav">
        <a href="Admin-Calendar.php">Calendar</a>
        <a href="Categories.php">Categories</a>
        <a href="Services.php">Services</a>
        <a href="Clients.php">Clients</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card">

            <!-- Welcome Section -->
            <div class="preview">
                <h1>Welcome to your Dashboard</h1>
                <p>This is your personal dashboard. You can view your client page from here.</p>
                <a href="../Welcome.html" class="btn">Client Page</a>
            </div>

            <!-- Recent Bookings -->
            <div class="recent-table">
                <h2>Recent Booking</h2>

                <div class="info">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                        <span style="font-weight: bold;">New booking will appear</span>
                        <span style="font-size: 20px;">&#8594;</span>
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
                        // Fetch recent bookings 
                        $sql = "
                            SELECT 
                                a.appointment_id,
                                CONCAT(c.first_name, ' ', c.last_name) AS name,
                                a.appointment_date,
                                DATE_FORMAT(a.appointment_time, '%h:%i %p') AS time,
                                GROUP_CONCAT(s.service_name SEPARATOR ', ') AS services,
                                a.is_seen
                            FROM appointments a
                            JOIN clients c ON a.client_id = c.client_id
                            JOIN appointment_services aps ON aps.appointment_id = a.appointment_id
                            JOIN services s ON s.service_id = aps.service_id
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
                        <span style="font-weight: bold;">Today Appointment</span>
                        <span style="font-size: 14px;">&#8594;</span>
                        <div style="width: 20px; height: 20px; background-color: #3185d3; border-radius: 4px; border: 1px solid #ccc;"></div>

                        <span style="font-weight: bold;">This Week Appointment</span>
                        <span style="font-size: 14px;">&#8594;</span>
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
                        // Fetch upcoming bookings
                        $sqlUpcoming = "
                            SELECT 
                                CONCAT(c.first_name, ' ', c.last_name) AS name,
                                a.appointment_date,
                                DATE_FORMAT(a.appointment_time, '%h:%i %p') AS time
                            FROM appointments a
                            JOIN clients c ON a.client_id = c.client_id
                            WHERE a.admin_id = :admin_id
                            AND a.appointment_date >= CURDATE()
                            ORDER BY a.appointment_date, a.appointment_time;
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

</body>
</html>
