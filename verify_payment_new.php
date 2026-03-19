<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, log them instead
ini_set('log_errors', 1);

session_start();

try {
    require 'db.php';
    
    // Get JSON data from request
    $json_data = file_get_contents('php://input');
    $input = json_decode($json_data, true);
    
    // Log the received data
    error_log("Received data: " . print_r($input, true));
    
    // Validate required fields
    if (!$input) {
        throw new Exception('No data received');
    }
    
    if (!isset($input['order_id']) || !$input['order_id']) {
        throw new Exception('Order ID is missing');
    }
    
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }
    
    // Get data
    $user_id = $_SESSION['user_id'];
    $order_id = $input['order_id'];
    $payment_id = $input['razorpay_payment_id'] ?? 'test_' . time();
    $total_amount = $input['total_amount'] ?? 0;
    
    // Verify Razorpay Signature (IMPORTANT: Required for real payments)
    if (isset($input['razorpay_signature']) && $input['razorpay_signature']) {
        require 'razorpay_config.php';
        
        // Create signature string
        $razorpay_order_id = $input['razorpay_order_id'] ?? $order_id;
        $signature_data = "$razorpay_order_id|$payment_id";
        $generated_signature = hash_hmac('sha256', $signature_data, RAZORPAY_KEY_SECRET);
        $received_signature = $input['razorpay_signature'];
        
        error_log("Signature Verification:");
        error_log("Generated: $generated_signature");
        error_log("Received: $received_signature");
        
        // Verify signature (strict verification for production)
        if ($generated_signature !== $received_signature) {
            error_log("Signature mismatch - Payment verification failed");
            throw new Exception('Payment verification failed. Invalid signature.');
        }
        
        error_log("Signature verified successfully");
    }
    
    // Get customer data
    $customer_name = $input['name'] ?? '';
    $customer_email = $input['email'] ?? '';
    $customer_phone = $input['phone'] ?? '';
    $customer_address = $input['address'] ?? '';
    $customer_city = $input['city'] ?? '';
    $customer_state = $input['state'] ?? '';
    $customer_pincode = $input['pincode'] ?? '';
    
    // Escape data
    $customer_name = mysqli_real_escape_string($conn, $customer_name);
    $customer_email = mysqli_real_escape_string($conn, $customer_email);
    $customer_phone = mysqli_real_escape_string($conn, $customer_phone);
    $customer_address = mysqli_real_escape_string($conn, $customer_address);
    $customer_city = mysqli_real_escape_string($conn, $customer_city);
    $customer_state = mysqli_real_escape_string($conn, $customer_state);
    $customer_pincode = mysqli_real_escape_string($conn, $customer_pincode);
    $order_id = mysqli_real_escape_string($conn, $order_id);
    $payment_id = mysqli_real_escape_string($conn, $payment_id);
    
    // Create orders table if not exists
    $create_orders_table = "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        order_id VARCHAR(100) UNIQUE NOT NULL,
        payment_id VARCHAR(100) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        status VARCHAR(50) DEFAULT 'completed',
        payment_status VARCHAR(50) DEFAULT 'success',
        customer_name VARCHAR(255),
        customer_email VARCHAR(255),
        customer_phone VARCHAR(20),
        customer_address TEXT,
        customer_city VARCHAR(100),
        customer_state VARCHAR(100),
        customer_pincode VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX(user_id),
        INDEX(order_id),
        INDEX(payment_id)
    )";
    
    if (!mysqli_query($conn, $create_orders_table)) {
        throw new Exception('Failed to create orders table: ' . mysqli_error($conn));
    }
    
    // Insert order
    $insert_query = "INSERT INTO orders (user_id, order_id, payment_id, amount, status, payment_status, customer_name, customer_email, customer_phone, customer_address, customer_city, customer_state, customer_pincode) 
                     VALUES ('$user_id', '$order_id', '$payment_id', '$total_amount', 'completed', 'success', '$customer_name', '$customer_email', '$customer_phone', '$customer_address', '$customer_city', '$customer_state', '$customer_pincode')";
    
    if (!mysqli_query($conn, $insert_query)) {
        error_log("Insert failed: " . mysqli_error($conn));
        throw new Exception('Failed to save order: ' . mysqli_error($conn));
    }
    
    error_log("Order inserted successfully: $order_id");
    
    // Save cart items to order_items table
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        // Create order_items table if not exists
        $create_items_table = "CREATE TABLE IF NOT EXISTS order_items (
            id INT PRIMARY KEY AUTO_INCREMENT,
            order_id VARCHAR(100),
            product_name VARCHAR(255),
            price DECIMAL(10, 2),
            quantity INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES orders(order_id),
            INDEX(order_id)
        )";
        
        mysqli_query($conn, $create_items_table);
        
        // Insert items
        foreach ($_SESSION['cart'] as $item) {
            $product_name = mysqli_real_escape_string($conn, $item['name']);
            $product_price = floatval($item['price']);
            $quantity = intval($item['quantity']);
            
            $item_query = "INSERT INTO order_items (order_id, product_name, price, quantity) 
                          VALUES ('$order_id', '$product_name', '$product_price', '$quantity')";
            
            if (!mysqli_query($conn, $item_query)) {
                error_log("Item insert failed: " . mysqli_error($conn));
            }
        }
    }
    
    // Clear the cart session
    $_SESSION['cart'] = [];
    
    // Return success
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'payment_id' => $payment_id,
        'message' => 'Payment successful!'
    ]);
    
} catch (Exception $e) {
    error_log("Error in verify_payment.php: " . $e->getMessage());
    
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit;
?>
