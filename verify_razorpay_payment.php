<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

require 'db.php';
require 'razorpay_config.php';

$input = json_decode(file_get_contents('php://input'), true);

$razorpay_order_id = $input['razorpay_order_id'];
$razorpay_payment_id = $input['razorpay_payment_id'];
$razorpay_signature = $input['razorpay_signature'];
$customer_data = $input['customer_data'];

// Razorpay secret key (TEST MODE)
$key_secret = RAZORPAY_KEY_SECRET;

// Verify signature
$generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $key_secret);

if ($generated_signature !== $razorpay_signature) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment signature']);
    exit;
}

// Payment verified successfully - Create order
$user_id = $_SESSION['user_id'];
$order_id = 'ORD' . time() . rand(1000, 9999);

// Prepare order items
$order_items = [];
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $order_items[] = $item['name'] . ' (x' . $item['quantity'] . ') - ₹' . number_format($item['price'] * $item['quantity'], 2);
    $total += $item['price'] * $item['quantity'];
}
$items_text = implode(', ', $order_items);

// Add shipping
$shipping = $total > 5000 ? 0 : 100;
$total += $shipping;

// Insert order
$query = "INSERT INTO orders (order_id, user_id, items, total, payment_method, payment_status, status, 
          customer_name, customer_email, customer_phone, shipping_address, city, state, pincode, created_at) 
          VALUES (?, ?, ?, ?, 'razorpay', 'completed', 'processing', ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "sisdssssssss", 
    $order_id, 
    $user_id, 
    $items_text, 
    $total,
    $customer_data['name'],
    $customer_data['email'],
    $customer_data['phone'],
    $customer_data['address'],
    $customer_data['city'],
    $customer_data['state'],
    $customer_data['pincode']
);

if (mysqli_stmt_execute($stmt)) {
    // Clear cart
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Payment verified and order created'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order'
    ]);
}
?>
