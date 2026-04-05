<?php
session_start();
require "./Admin_system/db.php";

// getting the admin id
if (!isset($_GET['admin_id'])) {
    die("Admin not found.");
}
$admin_id = $_GET['admin_id'];

// fetching the categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetching the services
$stmt = $pdo->prepare("
    SELECT s.*, c.category_name 
    FROM services s
    JOIN categories c ON s.category_id = c.category_id
    WHERE s.admin_id = ?
    ORDER BY c.category_name
");
$stmt->execute([$admin_id]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// grouping services by category
$grouped = [];
foreach ($services as $service) {
    $grouped[$service['category_name']][] = $service;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <link rel="stylesheet" href="./css/service.css" />
  <title>Services</title>
</head>

<body>

<!-- Header -->
<header>
  <div id="cart-icon">
    <span class="material-symbols-outlined">shopping_cart</span>
  </div>
  <button class="hamburger-menu" id="hamburger-menu">&#9776;</button>
</header>


<!-- Sidebar -->
<div class="sidebar">
  <form action="#" method="GET">
    <input type="text" name="search" placeholder="Search Services..." />
  </form>

  <div class="categories">
    <h2>Categories</h2>
    <ul class="category-list">
      <li class="active">All Services</li>
      <?php foreach ($categories as $cat): ?>
        <li><?= htmlspecialchars($cat['category_name']) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- Progress Bar -->
<div class="progress-container">
  <div class="step active">Services</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Calendar</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Details</div>
  <div class="arrow">→</div>
  <div class="step uncomplete">Payment</div>
</div>

<div class="header"><h2>Services</h2></div>

<div class="services-box">
  <h2>Our services</h2>
  <div class="reminder">• Reminder: Deposits are non-refundable</div>

  <?php foreach ($grouped as $category => $items): ?>
    <div class="category-header" data-category="<?= htmlspecialchars($category) ?>">
      <span><?= htmlspecialchars($category) ?></span>
      <span class="toggle">+</span>
    </div>

    <div class="category-content">
      <table>
        <tr>
          <th>Service</th>
          <th>Time</th>
          <th>Full</th>
          <th>Deposit</th>
          <th></th>
        </tr>

        <?php foreach ($items as $service): ?>
          <tr>
            <td><?= htmlspecialchars($service['service_name']) ?></td>
            <td><?= htmlspecialchars($service['duration_minutes']) ?> mins</td>
            <td>£<?= htmlspecialchars($service['price']) ?></td>
            <td>£<?= htmlspecialchars($service['deposit_price']) ?></td>
            <td>
              <button class="add-btn"
                data-id="<?= $service['service_id'] ?>"
                data-name="<?= htmlspecialchars($service['service_name']) ?>"
                data-price="<?= $service['price'] ?>"
                data-deposit="<?= $service['deposit_price'] ?>">
                Add
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endforeach; ?>
</div>

<!-- CART -->
<div id="side-cart">
  <div class="cart-panel">
    <button id="close-cart">X</button>
    <h3>Booking Cart</h3>
    <p id="cart-timer" style="font-weight:bold; margin-top:10px;"></p>

    <div class="cart-scroll">
      <table class="cart-table">
        <thead>
          <tr>
            <th>Services</th>
            <th>Price</th>
          </tr>
        </thead>
        <tbody id="cart-items"></tbody>
      </table>
    </div>

    <!--  REQUIRED FOR CALENDAR TO READ CART -->
    <div class="cart-total">
      <strong>Total Price</strong>
      <strong id="total-price">£0</strong>
    </div>

    <a href="Calendar.php?admin_id=<?= $admin_id ?>" class="continue-btn">Continue</a>
  </div>
</div>

<script>
// SIDEBAR TOGGLE
const hamburgerMenu = document.getElementById('hamburger-menu');
const sidebar = document.querySelector('.sidebar');

hamburgerMenu.addEventListener('click', () => {
  sidebar.classList.toggle('open');
});

// CART OPEN / CLOSE
const cartIcon = document.getElementById('cart-icon');
const sideCart = document.getElementById('side-cart');
const closeCart = document.getElementById('close-cart');
const cartPanel = document.querySelector('.cart-panel');

cartIcon.addEventListener('click', e => {
  e.stopPropagation();
  sideCart.classList.add('open');
});

closeCart.addEventListener('click', () => {
  sideCart.classList.remove('open');
});

// LOAD CART
let cart = JSON.parse(sessionStorage.getItem("cart")) || [];

// TIMESTAMP
function setCartTimestamp() {
  sessionStorage.setItem("cartTimestamp", Date.now());
}

// EXPIRY CHECK
function checkCartExpiry() {
  const timestamp = sessionStorage.getItem("cartTimestamp");
  if (!timestamp) return;

  const expiryMinutes = 30;
  const now = Date.now();
  const diff = (now - timestamp) / 1000 / 60;

  if (diff > expiryMinutes) {
    cart = [];
    sessionStorage.setItem("cart", JSON.stringify(cart));
    sessionStorage.removeItem("cartTimestamp");
  }
}
checkCartExpiry();

// TIMER
function updateCartTimer() {
  const timerElement = document.getElementById("cart-timer");
  const timestamp = sessionStorage.getItem("cartTimestamp");

  if (!timestamp || cart.length === 0) {
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

// UPDATE CART UI
function updateCart() {
  const cartItems = document.getElementById("cart-items");

  if (cart.length === 0) {
    cartItems.innerHTML = `<tr><td colspan="2" style="text-align:center;">Your cart is empty</td></tr>`;
  } else {
    cartItems.innerHTML = cart.map((item, index) => `
      <tr>
        <td>${item.name}</td>
        <td>£${item.price}
          <button class="remove-btn" data-index="${index}">Remove</button>
        </td>
      </tr>
    `).join('');
  }

  // SAVE CART
  sessionStorage.setItem("cart", JSON.stringify(cart));

  // UPDATE TOTAL PRICE (DEPOSIT ONLY)
  const total = cart.reduce((sum, item) => sum + Number(item.price), 0);
  document.getElementById("total-price").textContent = "£" + total;

  // REMOVE BUTTONS
  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      cart.splice(btn.dataset.index, 1);
      sessionStorage.setItem("cart", JSON.stringify(cart));
      updateCart();
    });
  });

  updateCartTimer();
}

// ADD TO CART (FINAL VERSION)
document.querySelectorAll('.add-btn').forEach(btn => {
  btn.addEventListener('click', () => {

    cart.push({
      id: btn.dataset.id,
      name: btn.dataset.name,

      //  Deposit is what the client pays now
      price: parseFloat(btn.dataset.deposit),

      //  Full price stored separately for admin + appointment record
      full_price: parseFloat(btn.dataset.price),

      //  Duration (needed for end time)
      duration: parseFloat(btn.dataset.duration)
    });

    // Save cart
    sessionStorage.setItem("cart", JSON.stringify(cart));

    // Save service for Payment page (deposit only)
    sessionStorage.setItem("selected_service_name", btn.dataset.name);
    sessionStorage.setItem("selected_service_price", btn.dataset.deposit);

    // Reset timer
    setCartTimestamp();

    updateCart();
    sideCart.classList.add('open');
  });
});

// INITIAL LOAD
updateCart();

// ACCORDION
document.querySelectorAll('.category-header').forEach(header => {
  header.addEventListener('click', () => {
    const content = header.nextElementSibling;
    const icon = header.querySelector('.toggle');
    const isOpen = content.style.display === 'block';

    document.querySelectorAll('.category-content').forEach(c => c.style.display = 'none');
    document.querySelectorAll('.toggle').forEach(t => t.textContent = '+');

    if (!isOpen) {
      content.style.display = 'block';
      icon.textContent = '−';
    }
  });
});

</script>

</body>
</html>
