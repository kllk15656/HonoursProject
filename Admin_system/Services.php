<?php
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION["admin_id"])) {
    header("Location: Login.php");
    exit;
}

require_once "./db.php";

// Logged-in admin ID
$admin_id = $_SESSION["admin_id"];

// Fetch services
$stmt = $pdo->prepare("
    SELECT service_id, service_name, duration_minutes, price, deposit_price
    FROM services
    WHERE admin_id = ?
    ORDER BY service_name ASC
");
$stmt->execute([$admin_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/services.css">
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
    <a href="Services.php" class="active">Services</a>
    <a href="Clients.php">Clients</a>

    <p class="mobile-nav-label">Navigation</p>

    <div class="mobile-nav-links"> 
        <a href="dashboard.php">Dashboard</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Log Out</a>
    </div>
</div>

<div class="overlay" onclick="toggleMenu()"></div>

<!-- Main Content -->
<div class="main-content">
    <div class="card">
        <div class="preview">
            <h1>Services Management</h1>
            <p>Add, edit or delete and manage your business services.</p>
            <button class="add-btn" onclick="openAddPopup()">Add Service</button>
        </div>

        <div class="service-table">
            <h2>Services</h2>

            <table>
                <tr>
                    <th>Service Name</th>
                    <th class="desktop-only">Duration</th>
                    <th class="desktop-only">Price</th>
                    <th class="desktop-only">Deposit</th>
                    <th>Edit</th>
                    <th>Delete</th>
                    <th class="mobile-extra">More</th>
                </tr>
                

                <?php if (!empty($services)): ?>
                    <?php foreach ($services as $srv): ?>
                        <tr>
                            <td><?= htmlspecialchars($srv['service_name']) ?></td>

                            <!-- Desktop-only fields -->
                            <td class="desktop-only"><?= htmlspecialchars($srv['duration_minutes']) ?> mins</td>
                            <td class="desktop-only">£<?= number_format($srv['price'], 2) ?></td>
                            <td class="desktop-only">£<?= number_format($srv['deposit_price'], 2) ?></td>

                            <!-- Edit -->
                            <td>
                                <button class="edit-btn"
                                    onclick="openEditPopup(
                                        <?= $srv['service_id'] ?>,
                                        '<?= $srv['service_name'] ?>',
                                        '<?= $srv['duration_minutes'] ?>',
                                        '<?= $srv['price'] ?>',
                                        '<?= $srv['deposit_price'] ?>'
                                    )">
                                    Edit
                                </button>
                            </td>

                            <!-- Delete -->
                            <td>
                                <button class="delete-btn"
                                    onclick="openDeletePopup(
                                        <?= $srv['service_id'] ?>,
                                        '<?= $srv['service_name'] ?>'
                                    )">
                                    Delete
                                </button>
                            </td>

                            <!-- MOBILE EXTRA FIELDS -->
                            <td class="mobile-extra">
                                <button class="extra-btn" onclick="toggleExtra(this)">Additional Fields</button>

                                <div class="extra-fields">
                                    <p><strong>Duration:</strong> <?= htmlspecialchars($srv['duration_minutes']) ?> mins</p>
                                    <p><strong>Price:</strong> £<?= number_format($srv['price'], 2) ?></p>
                                    <p><strong>Deposit:</strong> £<?= number_format($srv['deposit_price'], 2) ?></p>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7">No services found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- ADD SERVICE POPUP -->
<div id="add-popup" class="popup">
    <div class="popup-content">
        <a href="Services.php" class="close-btn">&times;</a>
        <h2>Add Service</h2>

        <form action="Add_service.php" method="POST">
            <label>Service Name</label>
            <input type="text" name="service_name" required>

            <label>Duration (minutes)</label>
            <input type="number" name="duration_minutes" required>

            <label>Price (£)</label>
            <input type="number" step="0.01" name="price" required>

            <label>Deposit (£)</label>
            <input type="number" step="0.01" name="deposit_price" required>

            <button type="submit" class="popup-btn">Add Service</button>
        </form>
    </div>
</div>

<!-- EDIT SERVICE POPUP -->
<div id="edit-popup" class="popup">
    <div class="popup-content">
        <a href="Services.php" class="close-btn">&times;</a>
        <h2>Edit Service</h2>

        <form action="Edit_service.php" method="POST">
            <input type="hidden" id="edit-id" name="service_id">

            <label>Service Name</label>
            <input type="text" id="edit-name" name="service_name">

            <label>Duration (minutes)</label>
            <input type="number" id="edit-duration" name="duration_minutes">

            <label>Price (£)</label>
            <input type="number" step="0.01" id="edit-price" name="price">

            <label>Deposit (£)</label>
            <input type="number" step="0.01" id="edit-deposit" name="deposit_price">

            <button type="submit" class="popup-btn">Update</button>
        </form>
    </div>
</div>

<!-- DELETE SERVICE POPUP -->
<div id="delete-popup" class="popup">
    <div class="popup-content">
        <a href="Services.php" class="close-btn">&times;</a>
        <h2>Delete Service</h2>

        <p>Are you sure you want to delete <strong id="delete-name"></strong>?</p>

        <form action="Delete_service.php" method="POST">
            <input type="hidden" id="delete-id" name="service_id">
            <button type="submit" class="popup-btn">Yes, Delete</button>
        </form>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
function toggleMenu() {
    const menu = document.querySelector('.side-nav');
    const overlay = document.querySelector('.overlay');
    menu.classList.toggle('open');
    overlay.classList.toggle('show');
}

function toggleExtra(btn) {
    const box = btn.nextElementSibling;
    box.classList.toggle("show");
}

function openAddPopup() {
    document.getElementById("add-popup").style.display = "flex";
}

function openEditPopup(id, name, duration, price, deposit) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-name").value = name;
    document.getElementById("edit-duration").value = duration;
    document.getElementById("edit-price").value = price;
    document.getElementById("edit-deposit").value = deposit;
    document.getElementById("edit-popup").style.display = "flex";
}

function openDeletePopup(id, name) {
    document.getElementById("delete-id").value = id;
    document.getElementById("delete-name").innerText = name;
    document.getElementById("delete-popup").style.display = "flex";
}
</script>

</body>
</html>
