<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: home.php");
    exit;
}

require 'db.php';

// Get form data
$user_id = $_SESSION['user_id'];
$name = mysqli_real_escape_string($conn, $_POST['name']);
$email = mysqli_real_escape_string($conn, $_POST['email']);
$phone = mysqli_real_escape_string($conn, $_POST['phone']);
$address = mysqli_real_escape_string($conn, $_POST['address']);
$city = mysqli_real_escape_string($conn, $_POST['city']);
$state = mysqli_real_escape_string($conn, $_POST['state']);
$pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
$payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
$total_amount = floatval($_POST['total_amount']);

if ($payment_method !== 'cod') {
    header("Location: checkout.php");
    exit;
}

// Prepare order items
$order_items = [];
foreach ($_SESSION['cart'] as $item) {
    $order_items[] = $item['name'] . ' (x' . $item['quantity'] . ') - ₹' . number_format($item['price'] * $item['quantity'], 2);
}
$items_text = implode(', ', $order_items);

// Generate unique order ID
$order_id = 'ORD' . time() . rand(1000, 9999);

// Determine payment status based on method
$payment_status = 'pending';
$order_status = 'confirmed';

// Insert order into database
$query = "INSERT INTO orders (order_id, user_id, items, total, payment_method, payment_status, status, 
          customer_name, customer_email, customer_phone, shipping_address, city, state, pincode, created_at) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sissssssssssss", 
    $order_id, $user_id, $items_text, $total_amount, $payment_method, 
    $payment_status, $order_status, $name, $email, $phone, $address, $city, $state, $pincode
);

if (mysqli_stmt_execute($stmt)) {
    // Clear cart
    $_SESSION['cart'] = [];
    
    // Redirect to order success page
    header("Location: order_success.php?order_id=" . $order_id);
    exit;
} else {
    // Error handling
    $_SESSION['error'] = "Order placement failed. Please try again.";
    header("Location: checkout.php");
    exit;
}
?>
