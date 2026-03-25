<?php
session_start();
require "./Admin_system/db.php";

// getting the admin id
if (!isset($_GET['admin_id'])) {
    die("Admin not found.");
}
$admin_id = $_GET['admin_id'];

// fetching the categorys
$stmt = $pdo->prepare("SELECT * FROM categories WHERE admin_id = ?");
$stmt->execute([$admin_id]);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetching the sevices
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
  <title>Categories</title>
  <style>
    .material-symbols-outlined {
      font-variation-settings:
      'FILL' 0,
      'wght' 400,
      'GRAD' 0,
      'opsz' 24;
    }
    /* accordion default state */
    .category-content {
      display: none;
    }
    .category-header {
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
  </style>
</head>

<body>

<!-- Header -->
<header>
  <div id="cart-icon">
    <span class="material-symbols-outlined">shopping_cart</span>
  </div>

  <button class="hamburger-menu" id="hamburger-menu">
    &#9776;
  </button>
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

<!-- Main -->
  <div class="progress-container">
    <div class="step active">Services</div>
    <div class="arrow">→</div>
    <div class="step uncomplete">Calendar</div>
    <div class="arrow">→</div>
    <div class="step uncomplete">Details</div>
    <div class="arrow">→</div>
    <div class="step uncomplete">Payment</div>
  </div>


 <div class="header">
  <h2>Details</h2>
</div>

  <div class="services-box">
    <h2>Our services</h2>
    <div class="reminder">
      • Reminder: Deposits are non-refundable
    </div>

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
              data-deposit="<?= $service['deposit_price'] ?>">Add</button>
            </td>
          </tr>
          <?php endforeach; ?>
      </table>
    </div>
  <?php endforeach; ?>



  </div>
</div>

<!-- Cart -->
<div id="side-cart">
  <div class="cart-panel">
    <button id="close-cart">X</button>
    <h3>Booking Cart</h3>
    <p>Your cart is empty</p>

    <div class="cart-total">
      <strong>Total Price</strong>
      <strong>£0</strong>
    </div>

    <a href="Calendar.html" class="continue-btn">Continue</a>
  </div>
</div>

<!-- JS -->
<script>
  // Hamburger menu
  const hamburgerMenu = document.getElementById('hamburger-menu');
  const sidebar = document.querySelector('.sidebar');

  hamburgerMenu.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });

  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && e.target !== hamburgerMenu) {
      sidebar.classList.remove('open');
    }
  });

  // Pass admin_id from PHP to JS
  const adminId = <?= $admin_id ?>;

  // Cart
  const cartIcon = document.getElementById('cart-icon');
  const sideCart = document.getElementById('side-cart');
  const closeCart = document.getElementById('close-cart');
  const cartPanel = document.querySelector('.cart-panel');

  cartIcon.addEventListener('click', e => {
    e.stopPropagation();
    sideCart.classList.add('open');
  });

  closeCart.addEventListener('click', e => {
    e.stopPropagation();
    sideCart.classList.remove('open');
  });

  sideCart.addEventListener('click', () => {
    sideCart.classList.remove('open');
  });

  cartPanel.addEventListener('click', e => {
    e.stopPropagation();
  });

  // Cart array
  let cart = [];

  // Add to cart logic
  document.querySelectorAll('.add-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const service = {
        id: btn.dataset.id,
        name: btn.dataset.name,
        price: parseFloat(btn.dataset.price),
        deposit: parseFloat(btn.dataset.deposit)
      };

      cart.push(service);
      updateCart();
    });
  });

  // Update cart UI
  function updateCart() {
    let itemsHTML = cart.map((item, index) => `
      <div class="cart-item">
        <span>${item.name}</span>
        <span>£${item.price}</span>
        <button class="remove-btn" data-index="${index}">Remove</button>
      </div>
    `).join('');

    const total = cart.reduce((sum, item) => sum + item.price, 0);

    cartPanel.innerHTML = `
      <button id="close-cart">X</button>
      <h3>Booking Cart</h3>

      ${itemsHTML}

      <div class="cart-total">
        <strong>Total Price</strong>
        <strong>£${total}</strong>
      </div>

      <a href="Calendar.php?admin_id=${adminId}" class="continue-btn">Continue</a>
    `;

    // Reattach close button after re-render
    document.getElementById('close-cart').addEventListener('click', () => {
      sideCart.classList.remove('open');
    });
  
    document.querySelectorAll('.remove-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const index = btn.dataset.index;
        cart.splice(index, 1);   // remove item
        updateCart();            // refresh cart
      });
  });
  sessionStorage.setItem("cart", JSON.stringify(cart));

}

  // Accordion
  const headers = document.querySelectorAll('.category-header');

  headers.forEach(header => {
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

  // Category list active state
  const categories = document.querySelectorAll('.category-list li');

  categories.forEach(item => {
    item.addEventListener('click', () => {
      // highlight active
      categories.forEach(li => li.classList.remove('active'));
      item.classList.add('active');
      const selected = item.textContent.trim();

      document.querySelectorAll('.category-header').forEach(header=> {
        const categoryName = header.dataset.category;
        const content = header.nextElementSibling;
        const icon = header.querySelector('.toggle');

        if (categoryName === selected || selected === "All Services"){
          // Open this category
          content.style.display ='block';
          icon.textContent= '-';
        }else{
          //close others
          content.style.display ='none';
          icon.textContent='+';
        }
      });
    });
  });
</script>


</body>
</html>
