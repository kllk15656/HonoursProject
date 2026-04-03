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

//Fetch categorys for logged in admin

$stmt = $pdo->prepare("
    SELECT category_id, category_name
    From categories
    WHERE admin_id = ?
    ORDER BY category_id ASC
");

$stmt-> execute([$admin_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);




?>
<!DOCTYPE html>
<html lang ="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href="./css/categories.css">
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
        function openEditPopup (id,name){
            document.getElementById("editCategoryId").value = id;
            document.getElementById("editCategoryName").value =name;
            document.getElementById("editPopup").style.display="flex";
        }
         function openDeletePopup (id){
          
            document.getElementById("deleteCategoryId").value =id;
            document.getElementById("deletePopup").style.display="flex";
        }
        function closePopup(popupId) {
            document.getElementById(popupId).style.display = "none";
        }

    </script>

</head>

<body>
    
    <!-- top nav-->
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


    <!-- Main content-->
     <div class="main-content">
        <div class="card">
            <div class="preview">
            <h1>Categories Management</h1>
            <p> Add, edit  or delete and manage your business Categories. </p>
            <a href="#addPopup" class="add-btn">Add Service</a>
            </div>

            <div class="categories-table">
                <h2>Categories</h2>

                <table>
                    <tr> 
                        <th> Category id</th> 
                        <th>Category Name</th> 
                        <th>Edit / Delete</th> 
                    </tr>
                    <?php if (!empty($categories)): ?> 
                        <?php $i = 1; ?>
                        <?php foreach ($categories as $cat): ?> 
                            <tr> 
                                <td><?php echo $i ?></td> 
                                <td><?= htmlspecialchars($cat['category_name']) ?></td> 
                                <td> <button class="edit-btn" onclick="openEditPopup(<?= $cat['category_id'] ?>, '<?= $cat['category_name'] ?>')"> Edit </button> 
                                <button class="delete-btn" onclick="openDeletePopup(<?= $cat['category_id'] ?>)"> Delete </button> </td>
                             </tr> 
                             <?php $i++; ?>
                             <?php endforeach; ?> 
                             <?php else: ?> 
                                <tr>
                                    <td colspan="3">No categories found</td>
                                 </tr> 

                    <?php endif; ?>

                </table>
                

            </div>
        </div>
     </div>
     <!-- ADD POPUP -->
<div id="addPopup" class="popup">
    <div class="popup-content">
        <a href="Categories.php" class="close-btn">&times;</a>
        <h3>Add Category</h3>

        <form action="Add_category.php" method="POST">
            <label>Category Name</label>
            <input type="text" name="category_name" required>

            <button type="submit" class="popup-btn">Add Category</button>
        </form>
    </div>
</div>

<!-- EDIT POPUP -->
<div id="editPopup" class="popup">
    <div class="popup-content">
        <span class="close-btn" onclick="closePopup('editPopup')">&times;</span>
        <h3>Edit Category</h3>

        <form action="Edit_category.php" method="POST">
            <input type="hidden" name="category_id" id="editCategoryId">

            <label>Category Name</label>
            <input type="text" name="category_name" id="editCategoryName" required>

            <button type="submit" class="popup-btn">Save Changes</button>
        </form>
    </div>
</div>

<!-- DELETE POPUP -->
<div id="deletePopup" class="popup">
    <div class="popup-content">
        <span class="close-btn" onclick="closePopup('deletePopup')">&times;</span>
        <h3>Delete Category</h3>
        <p>Are you sure you want to delete this category?</p>

        <form action="Delete_category.php" method="POST">
            <input type="hidden" name="category_id" id="deleteCategoryId">

            <button type="submit" class="popup-btn" style="background:#d83030;color:white;">Delete</button>
        </form>
    </div>
</div>
            
    </body>
</html>



