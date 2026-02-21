<?php
session_start();
// if admin is not logged in , redirect to login page
if (!isset($_SESSION["admin_id"])){
    header("Location: Login.php");
}

//Connects to database
require "./db.php";

?>



<!DOCTYPE html>
<html lang ="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href="./css/dashboard.css">

</head>

<body>
    <!-- top nav-->
    <div class=" top-nav">
        <h1> Admin Dashboard</h1>
        <ul>
            <li> <a href="Dashboard.php">Dashboard</a></li>
            <li><a href="Setting.php">Settings</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </div>
    <!-- side nav-->
    <div class="side-nav">
        <a href="Admin-Calendar.php">Calendar</a>
        <a href="Categories.php">Categories</a>
        <a href="Services.php">Services</a>
        <a href="Clients.php">Clients</a>
    </div>
    <!-- Main content-->
     <div class="main-content">
        <div class="card">
            <div class="preview">
            <h1>Welcome to your Dashobard</h1>
            <p> This is your personal dashboard.
                You can view your client page from here
            </p> 
            <a href="../Welcome.html" class="btn"> Client page</a>
            </div>

            <div class="recent-table">
            <h2> Recent Booking</h2>
             <div class="info">
                <div style="display: flex; align-items: center; gap: 8px; font-family: Arial, sans-serif; margin-bottom: 15px;">
                    <span style="font-weight: bold;">New booking will appear </span>
                    <span style="font-size: 20px;">&#8594;</span>
                    <div style="width: 20px; height: 20px; background-color: #d3bb31; border-radius: 4px; border: 1px solid #ccc;"></div>
                </div>
            </div>
            <table>
                <?php
                // This aql query will get recent appointments by the clients
                // full name, appointment date, time list of services.
                
                $sql="
                SELECT 
                    a.appointment_id
                    CONCAT(c.first_name, '',c.last_name) AS name,
                    a.appointment_date,
                    DATE_FORMAT(a.appointment_time, '%h:%i %p) AS time,
                    GROUP_CONCAT(s.service_name SEPARATER',') AS services,
                    as.is_seen

                    FROM appointment a
                    JOIN client c ON a.client_id = client_id
                    JOIN appointment_service aps ON aps.appointment_id = a.appointment_id
                    JOIN services s ON s.service_id = aps.service_id
                    WHERE a.admin_id = :admin_id
                    GROUP BY a.appointment_id
                    ORDER BY a.created_at DES
                    LIMIT 5;
                ";
                //prepare and execute the query
                $stmt = $pdo->prepare($sql);
                $stmt->execute([":admin_id" => $_SESSION["admin-id"]]);

                //Fetching results
                $recentBookings = $stmt->fetchAll();
                
                ?>
                <tbody>
                    <?php 
                        //displaying  recent bookings
                    if ($recentBookings){
                        foreach ($recentBookings as $row){
                             
                            //highlight new bookings
                            $rowClass = ($row['is_seen'] == 0) ? 'new-booking' : '';

                            echo "<tr class ='{$rowClass}' data-id ='{$row['appointment_id']}'>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>"; 
                            echo "<td>" . htmlspecialchars($row['appointment_date']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                             echo "<td>" . htmlspecialchars($row['services']) . "</td>";


                            echo ",/tr>";
                        }
                        } else{
                            echo "<tr><td colspan='4'>No recent bookings found.</td></tr>";
                        } 
                    ?>
                </tbody>
            </table>
            </div>

            <div class="upcoming-table">
                <h2> Upcoming Booking</h2>
                <div class="info">
                <div style="display: flex; align-items: center; gap: 5px; font-family: Arial, sans-serif; margin-bottom: 15px;">
                    <span style="font-weight: bold;">Today Appointment </span>
                    <span style="font-size: 14px;">&#8594;</span>
                    <div style="width: 20px; height: 20px; background-color: #3185d3; border-radius: 4px; border: 1px solid #ccc;"></div>
                    <span style="font-weight: bold;">This Week Appointment </span>
                    <span style="font-size: 14px;">&#8594;</span>
                    <div style="width: 20px; height: 20px; background-color: #a031d3; border-radius: 4px; border: 1px solid #ccc;"></div>
                </div>
                
                
                <table>
                    <tr>
                        <th> Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reminder</th>
                    </tr>
                    <tr>
                        <td>Anna White</td>
                        <td>2026-02-17</td>
                        <td>11:00 AM</td>
                        <td><button class="remind-btn">Reminder</button></td>
                    </tr>
                    <tr>
                        <td> James Green</td>
                        <td>2026-02-15</td>
                        <td>2:00 PM</td>
                        <td><button class="remind-btn">Reminder</button></td>
                    </tr>
                    <tr>
                        <td>Lucy Adams</td>
                        <td>2026-02-16</td>
                        <td>4:30 PM</td>
                        <td><button class="remind-btn">Reminder</button></td>
                    </tr>
                </table>
            </div>
        </div>
     </div>
</body>
</html>