<?php
session_start();
require 'db.php';
require 'razorpay_config.php';

header('Content-Type: application/json');

try {
    // Check user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Check cart exists
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        throw new Exception('Cart is empty');
    }

    // Calculate amount dynamically from actual cart data
    $subtotal = 0;
    $cart_items = [];
    
    foreach ($_SESSION['cart'] as $item) {
        $price = floatval($item['price']);
        $quantity = intval($item['quantity']);
        $item_total = $price * $quantity;
        
        $subtotal += $item_total;
        $cart_items[] = [
            'name' => $item['name'],
            'price' => $price,
            'quantity' => $quantity,
            'total' => $item_total
        ];
    }

    // Apply shipping logic (same as checkout.php)
    $shipping = $subtotal > 5000 ? 0 : 100;
    $total = $subtotal + $shipping;
    
    error_log("===== RAZORPAY ORDER CREATION =====");
    error_log("Subtotal: ₹$subtotal");
    error_log("Shipping: ₹$shipping");
    error_log("Total: ₹$total");
    error_log("Items: " . json_encode($cart_items));
    
    if ($total <= 0) {
        throw new Exception('Invalid cart total: ₹' . $total);
    }

    // Convert to paise
    $amount_paise = intval($total * 100);
    $order_receipt = 'ORDER_' . uniqid();

    error_log("Amount in Paise: $amount_paise");
    error_log("Using API Keys: " . RAZORPAY_KEY_ID);

    // Create Razorpay order using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    
    // Prepare POST data
    $post_data = [
        'amount' => $amount_paise,
        'currency' => 'INR',
        'receipt' => $order_receipt,
        'notes' => [
            'user_id' => $_SESSION['user_id'],
            'subtotal' => $subtotal,
            'shipping' => $shipping
        ]
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    
    // Set authentication header
    $auth_string = RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET;
    $auth_header = 'Authorization: Basic ' . base64_encode($auth_string);
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        $auth_header,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    error_log("Razorpay Response Code: $http_code");
    
    $response_preview = strlen($response) > 300 ? substr($response, 0, 300) . "..." : $response;
    error_log("Razorpay Response: " . $response_preview);

    if ($curl_error) {
        error_log("cURL Error: $curl_error");
        throw new Exception('Connection error: ' . $curl_error);
    }

    // Always try to decode the response
    $razorpay_order = json_decode($response, true);
    
    if ($razorpay_order === null) {
        error_log("Failed to decode Razorpay response as JSON");
        error_log("Raw response: " . $response);
        throw new Exception('Invalid JSON response from Razorpay');
    }

    // Check if request was successful (Razorpay returns 201 for creation, but sometimes 200)
    if ($http_code !== 201 && $http_code !== 200) {
        $error_msg = isset($razorpay_order['error']['description']) 
            ? $razorpay_order['error']['description'] 
            : 'HTTP ' . $http_code;
        error_log("Razorpay Error: $error_msg");
        error_log("Full Response: " . json_encode($razorpay_order));
        throw new Exception('Failed to create order: ' . $error_msg);
    }

    // Check if we got a valid order ID
    if (!isset($razorpay_order['id'])) {
        error_log("Invalid Razorpay Response - No ID found: " . json_encode($razorpay_order));
        // If there's an error in the response
        if (isset($razorpay_order['error'])) {
            throw new Exception('Razorpay Error: ' . $razorpay_order['error']['description']);
        }
        throw new Exception('Invalid response from Razorpay server');
    }

    error_log("✅ Order Created Successfully: " . $razorpay_order['id']);
    error_log("===== END =====");

    // Return success response
    echo json_encode([
        'success' => true,
        'order_id' => $razorpay_order['id'],
        'amount' => $amount_paise,
        'amount_display' => '₹' . number_format($total, 2),
        'key_id' => RAZORPAY_KEY_ID,
        'cart_summary' => [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $total
        ]
    ]);

} catch (Exception $e) {
    error_log("❌ Order Creation Failed: " . $e->getMessage());
    error_log("===== END =====");
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>