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

// Fetch clients for this admin
$stmt = $pdo->prepare("
    SELECT client_id, first_name, last_name, email, phone_number
    FROM clients
    WHERE admin_id = ?
    ORDER BY client_id ASC
");
$stmt->execute([$admin_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/client.css">
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
            <h1>Client Management</h1>
            <p>Add, edit or delete clients.</p>
            <button class="add-btn" onclick="openAddPopup()">Add Client</button>
        </div>

        <div class="client-table">
            <h2>Clients</h2>

            <table>
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Phone No</th>
                    <th>Consent</th>
                    <th>Edit/Delete</th>
                    <th class="mobile-extra">More</th>
                </tr>

                <?php if (!empty($clients)): ?>
                    <?php $i = 1; ?>
                    <?php foreach ($clients as $cli): ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td><?= htmlspecialchars($cli['first_name']) ?></td>
                            <td><?= htmlspecialchars($cli['last_name']) ?></td>
                            <td><?= htmlspecialchars($cli['email']) ?></td>
                            <td><?= htmlspecialchars($cli['phone_number']) ?></td>

                            <!-- Consent Button -->
                            <td>
                                <button class="consent-btn"
                                    onclick="openConsentPopup(
                                        '<?= $cli['first_name'] ?>',
                                        '<?= $cli['last_name'] ?>'
                                    )">
                                    View
                                </button>
                            </td>

                            <!-- Edit + Delete Buttons -->
                            <td>
                                <button class="edit-btn"
                                    onclick="openEditPopup(
                                        <?= $cli['client_id'] ?>,
                                        '<?= $cli['first_name'] ?>',
                                        '<?= $cli['last_name'] ?>',
                                        '<?= $cli['email'] ?>',
                                        '<?= $cli['phone_number'] ?>'
                                    )">
                                    Edit
                                </button>

                                <button class="delete-btn"
                                    onclick="openDeletePopup(
                                        <?= $cli['client_id'] ?>,
                                        '<?= $cli['first_name'] ?>',
                                        '<?= $cli['last_name'] ?>'
                                    )">
                                    Delete
                                </button>
                            </td>
                            <td class="mobile-extra">
                                <button class="extra-btn" onclick="toggleExtra(this)">Additional Fields</button>

                                <div class="extra-fields">
                                    <p><strong>Email:</strong> <?= htmlspecialchars($cli['email']) ?></p>
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($cli['phone_number']) ?></p>
                                    <p><strong>Consent:</strong></p>
                                    <button class="consent-btn"
                                    onclick="openConsentPopup(
                                    '<?= $cli['first_name'] ?>',
                                    '<?= $cli['last_name'] ?>'
                                    )">View</button>

                                </div>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7">No clients found.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- ADD CLIENT POPUP -->
<div id="add-popup" class="popup">
    <div class="popup-content">
        <a href="Clients.php" class="close-btn" onclick="closePopup('add-popup')">&times;</a>
        <h2>Add Client</h2>

        <form action="AddClient.php" method="POST">
            <label>First Name</label>
            <input type="text" name="first_name" required>

            <label>Last Name</label>
            <input type="text" name="last_name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Phone No</label>
            <input type="text" name="phone_number" required>

            <button type="submit" class="popup-btn">Add Client</button>
        </form>
    </div>
</div>

<!-- CONSENT POPUP -->
<div id="consent-popup" class="popup">
    <div class="popup-content">
        <a href="Clients.php" class="close-btn" onclick="closePopup('consent-popup')">&times;</a>
        <h2>Consent Form</h2>

        <p>
            <strong id="consent-name"></strong> consents to us securely storing their personal 
            information for <strong>6 years</strong> from their last appointment.
        </p>

        <p>
            After 6 years with no new appointments, this data will be automatically deleted.
        </p>
    </div>
</div>

<!-- EDIT POPUP -->
<div id="edit-popup" class="popup">
    <div class="popup-content">
        <a href="Clients.php" class="close-btn" onclick="closePopup('edit-popup')">&times;</a>
        <h2>Edit Client</h2>

        <form action="Edit_client.php" method="POST">
            <input type="hidden" id="edit-id" name="client_id">

            <label>First Name</label>
            <input type="text" id="edit-first" name="first_name">

            <label>Last Name</label>
            <input type="text" id="edit-last" name="last_name">

            <label>Email</label>
            <input type="email" id="edit-email" name="email">

            <label>Phone No</label>
            <input type="text" id="edit-phone" name="phone_number">

            <button type="submit" class="popup-btn">Update</button>
        </form>
    </div>
</div>

<!-- DELETE POPUP -->
<div id="delete-popup" class="popup">
    <div class="popup-content">
        <a href="Clients.php" class="close-btn" onclick="closePopup('delete-popup')">&times;</a>
        <h2>Delete Client</h2>

        <p>Are you sure you want to delete <strong id="delete-name"></strong>?</p>

        <form action="Delete_client.php" method="POST">
            <input type="hidden" id="delete-id" name="client_id">
            <button type="submit" class="popup-btn">Yes, Delete</button>
        </form>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
    function toggleMenu() {
    console.log('toggleMenu fired');

    const menu = document.querySelector('.side-nav');
    const overlay = document.querySelector('.overlay');

    console.log('before:', menu.className);
    menu.classList.toggle('open');
    overlay.classList.toggle('show');
    console.log('after:', menu.className);
}
function toggleExtra(btn) {
    const box = btn.nextElementSibling;
    box.classList.toggle("show");
}


    /* Opens the add client popup */
function openAddPopup() {
    document.getElementById("add-popup").style.display = "flex";
}
 /* Opens the consent  popup */
function openConsentPopup(first, last) {
    document.getElementById("consent-name").innerText = first + " " + last;
    document.getElementById("consent-popup").style.display = "flex";
}

 /* Opens the edit client popup */
function openEditPopup(id, first, last, email, phone) {
    document.getElementById("edit-id").value = id;
    document.getElementById("edit-first").value = first;
    document.getElementById("edit-last").value = last;
    document.getElementById("edit-email").value = email;
    document.getElementById("edit-phone").value = phone;

    document.getElementById("edit-popup").style.display = "flex";
}
 /* Opens the delete client popup */
function openDeletePopup(id, first, last) {
    document.getElementById("delete-id").value = id;
    document.getElementById("delete-name").innerText = first + " " + last;

    document.getElementById("delete-popup").style.display = "flex";
}

 /* Opens the close client popup */
function closePopup(id) {
    document.getElementById(id).style.display = "none";
}
</script>

</body>
</html>
