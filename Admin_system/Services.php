<?php
session_start();

// If admins not logged in then redirect to Login.php
if (!isset($_SESSION["admin_id"])){
    header("Location: Login.php");
    exit;
}

// Requring database connection
require_once "./db.php";

// Logged in admin id
$admin_id = $_SESSION["admin_id"];

// Fetch services for logged in admin
$stmt = $pdo->prepare("
SELECT 
    s.service_id,
    s.service_name,
    s.duration_minutes,
    s.price,
    s.deposit_price,
    c.category_id,
    c.category_name
FROM services s
JOIN categories c ON s.category_id = c.category_id
WHERE s.admin_id = ?
ORDER BY c.category_id ASC, s.service_name ASC
");
$stmt->execute([$admin_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories for dropdowns
$stmt2 = $pdo->prepare("
    SELECT category_id, category_name
    FROM categories
    WHERE admin_id = ?
    ORDER BY category_name ASC
");
$stmt2->execute([$admin_id]);
$categories = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/services.css">

    <script>
        function openEditPopup(id, name, duration, price, deposit, categoryId) {
            document.getElementById("editServiceId").value = id;
            document.getElementById("editServiceName").value = name;
            document.getElementById("editDuration").value = duration;
            document.getElementById("editPrice").value = price;
            document.getElementById("editDeposit").value = deposit;
            document.getElementById("editCategory").value = categoryId;

            document.getElementById("editPopup").style.display = "flex";
        }

        function openDeletePopup(id, name) {
            document.getElementById("deleteServiceId").value = id;
            document.getElementById("deleteServiceName").innerText = name;

            document.getElementById("deletePopup").style.display = "flex";
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = "none";
        }
    </script>
</head>

<body>

    <!-- top nav -->
    <div class="top-nav">
        <h1>Admin Dashboard</h1>
        <ul>
            <li><a href="Dashboard.php">Dashboard</a></li>
            <li><a href="Setting.php">Settings</a></li>
            <li><a href="Logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- side nav -->
    <div class="side-nav">
        <a href="Admin-Calendar.php">Calendar</a>
        <a href="Categories.php">Categories</a>
        <a href="Services.php">Services</a>
        <a href="Clients.php">Clients</a>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <div class="card">
            <div class="preview">
                <h1>Services Management</h1>
                <p>Add, edit or delete and manage your business services.</p>
                <a href="#addPopup" class="add-btn">Add Service</a>
            </div>

            <div class="service-table">
                <h2>Services</h2>

                <table>
                    <tr>
                       
                        <th>Service Name</th>
                        <th>Duration Minutes</th>
                        <th>Price</th>
                        <th>Deposit Price</th>
                        <th>Edit / Delete</th>
                    </tr>

                    <?php
                    $currentCategory = null;
                    $i = 1;

                    foreach ($services as $ser):
                        if ($currentCategory !== $ser['category_name']):
                            $currentCategory = $ser['category_name'];
                    ?>
                        <tr class="category-header">
                            <td colspan="6"><strong><?= $currentCategory ?></strong></td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        
                        <td><?= htmlspecialchars($ser['service_name']) ?></td>
                        <td><?= htmlspecialchars($ser['duration_minutes']) ?></td>
                        <td>£<?= htmlspecialchars($ser['price']) ?></td>
                        <td>£<?= htmlspecialchars($ser['deposit_price']) ?></td>

                        <td>
                            <button class="edit-btn"
                                onclick="openEditPopup(
                                    <?= $ser['service_id'] ?>,
                                    '<?= addslashes($ser['service_name']) ?>',
                                    <?= $ser['duration_minutes'] ?>,
                                    <?= $ser['price'] ?>,
                                    <?= $ser['deposit_price'] ?>,
                                    <?= $ser['category_id'] ?>
                                )">
                                Edit
                            </button>

                            <button class="delete-btn"
                                onclick="openDeletePopup(
                                    <?= $ser['service_id'] ?>,
                                    '<?= addslashes($ser['service_name']) ?>'
                                )">
                                Delete
                            </button>
                        </td>
                    </tr>

                    <?php $i++; endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- ADD POPUP -->
    <div id="addPopup" class="popup">
        <div class="popup-content">

                        <!--  return the user to service -- error deposit not auto adding -->
            <a href="Services.php" class="close-btn">&times;</a>
            <h3>Add Service</h3>
                            
            <!--  Send news service data-->
            <form action="Add_service.php" method="POST">
                <label>Service Name</label>
                <input type="text" name="service_name" required>

                <label>Duration (minutes)</label>
                <input type="number" name="duration_minutes" required>

                <label>Price (£)</label>
                <input type="number" step="0.01" name="price" required>
                <!--  Calucate the percentage  when the users enters and Java script auto caluate the price-->
                <label>Deposit (%)</label>
                <input type="number" id="addDepositPercent"
                    oninput="calculateDepositPrice('addPrice', 'addDepositPercent', 'addDeposit')"
                    placeholder="e.g. 50">
                <!--  Deposit will auto refill by JavaScript-->
                <label>Deposit (£)</label>
                <input type="number" step="0.01" name="deposit_price" id="addDeposit" required>


                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="popup-btn">Add Service</button>
            </form>
        </div>
    </div>

    <!-- EDIT POPUP -->
    <div id="editPopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('editPopup')">&times;</span>
            <h3>Edit Service</h3>

            <form action="Edit_service.php" method="POST">
                <input type="hidden" name="service_id" id="editServiceId">

                <label>Service Name</label>
                <input type="text" name="service_name" id="editServiceName" required>

                <label>Duration (minutes)</label>
                <input type="number" name="duration_minutes" id="editDuration" required>

                <label>Price (£)</label>
                <input type="number" step="0.01" name="price" id="editPrice" required>
                
                
                <label>Deposit (%)</label>
                <input type="number" id="editDepositPercent"
                oninput="calculateDepositPrice('editPrice', 'editDepositPercent', 'editDeposit')"
                placeholder="e.g. 50">


                <label>Deposit (£)</label>
                <input type="number" step="0.01" name="deposit_price" id="editDeposit" required>

              

                <label>Category</label>
                <select name="category_id" id="editCategory" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id'] ?>">
                            <?= htmlspecialchars($cat['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="popup-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <!-- DELETE POPUP -->
    <div id="deletePopup" class="popup">
        <div class="popup-content">
            <span class="close-btn" onclick="closePopup('deletePopup')">&times;</span>
            <h3>Delete Service</h3>

            <p>Are you sure you want to delete: <strong id="deleteServiceName"></strong>?</p>

            <form action="Delete_service.php" method="POST">
                <input type="hidden" name="service_id" id="deleteServiceId">

                <button type="submit" class="popup-btn" style="background:#d83030;color:white;">
                    Delete
                </button>
            </form>
        </div>
    </div>

    <script>
        function calculateDepositPrice(priceInputId, percentInputId, depositInputId) { 

            const priceField = document.getElementById(priceInputId); 
            const percentField = document.getElementById(percentInputId);
            const depositField = document.getElementById(depositInputId);

            const price = parseFloat(document.getElementById(priceInputId).value); 
            const percent = parseFloat(document.getElementById(percentInputId).value); 

            // if the field is empty or invaild
            if (isNaN(price) || isNaN(percent) || percent < 0) 
                { depositField.value = ""; return;

                }
                // Calculate deposit
                const deposit = (price * percent) / 100;
                //  // Update deposit field 
                depositField.value = deposit.toFixed(2);
            }
    </script>

</body>
</html>
