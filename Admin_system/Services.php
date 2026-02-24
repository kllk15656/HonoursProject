<!DOCTYPE html>
<html lang ="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel ="stylesheet" href="./css/services.css">

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
            <h1>Services Management</h1>
            <p> Add, edit  or delete and manage your business services. </p>
            <a href="#add-popup" class="add-btn">Add Service</a>
            </div>
            <div id="add-popup" class="popup">
          <div class=" popup-content">
            <a href="Services.html" class="close-btn">&times;</a>
            <h2> Add Services</h2>
            <form>
            <label> Service Name</label>
            <input type="text" placeholder="Enter service name" required>
            <label>Durcation</label>
            <input type="text" placeholder="e.g. 60 mins" required>
            <label>Deposit</label>
            <input type="number" placeholder=" Deposit amount" required>
            <lablel> Price</label>
            <input type="number" placeholder="Service price"required>
              <button type="submit" class="popup-btn"required>
                Save
              </button>

            </form>
          </div>

        </div>
            <div class="service-table">
                <h2> Service's</h2>
                <table>
                <tr>
                    <th> Service Name</th>
                    <th>Durcation</th>
                    <th> Deposit</th>
                    <th> Price</th>
                    <th>Edit/Delete</th>
                </tr>
                <tr>
                    <td>Facial</td>
                    <td>60 mins</td>
                    <td>£ 20</td>
                    <td>£ 40</td>
                    <td> <a href="#edit-popup" class="edit-btn">Edit</a>
                        <a href="#delete-popup" class="delete-btn">Delete</a></td>
                </tr>
                <tr>
                    <td>Lash</td>
                    <td>90 mins</td>
                    <td>£ 27.50</td>
                    <td>£ 55</td>
                    <td> <a href="#edit-popup" class="edit-btn">Edit</a>
                        <a href="#delete-popup" class="delete-btn">Delete</a></td>
                </tr>
                <tr>
                    <td>Make up</td>
                    <td>60 mins</td>
                    <td>£ 25</td>
                    <td>£ 50</td>
                    <td> <a href="#edit-popup" class="edit-btn">Edit</a>
                        <a href="#delete-popup" class="delete-btn">Delete</a></td>
                </tr>
                </table>
                <!-- Edit Popup-->
                 <div id="edit-popup" class="popup">
                    <div class="popup-content">
                        <a href="Services.html" class="close-btn">&times;</a>
                        <h2>Edit Services</h2>
                        <form>

                        <label>Service Name</label>
                        <input type="text" value="Facial">
                        <label> Durcation</label>
                        <input type="text" value="60 mins">
                        <label>Deposit</label>
                        <input type="number" value="20">
                        <label> Price</label>
                        <input type="number" value="40">
                        <button type="submit" class="popup-btn">Update</button>
                        </form>
                    </div>
                 </div>
                 <!-- Delete popup-->
                  <div id="delete-popup" class="popup">
                    <div class="popup-content">
                        <a href="Services.html" class="close-btn">&times;</a>
                        <h2>Delete Service</h2>
                        <p> Are you sure you want to delete this service?</p>
                        <button class="popup-btn">Yes, Delete</button>
                    </div>
                  </div>
            </div>
        </div>
     </div>
</body>
</html>