<?php
/**
 * Razorpay Payment Helper Functions
 * Use these functions to manage orders and payments
 */

require 'db.php';

/**
 * Get all orders for a user
 */
function getUserOrders($user_id) {
    global $conn;
    $query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get single order details with items
 */
function getOrderDetails($order_id, $user_id) {
    global $conn;
    
    // Get order info
    $query = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $order_id, $user_id);
    mysqli_stmt_execute($stmt);
    $order = mysqli_stmt_get_result($stmt)->fetch_assoc();
    
    if (!$order) return null;
    
    // Get order items
    $query = "SELECT * FROM order_items WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $order_id);
    mysqli_stmt_execute($stmt);
    $order['items'] = mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    
    return $order;
}

/**
 * Get total revenue from all orders
 */
function getTotalRevenue() {
    global $conn;
    $query = "SELECT SUM(amount) as total FROM orders WHERE payment_status = 'success'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

/**
 * Get total orders
 */
function getTotalOrders() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM orders WHERE payment_status = 'success'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

/**
 * Get total successful payments
 */
function getSuccessfulPayments() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM orders WHERE payment_status = 'success'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

/**
 * Get failed payments
 */
function getFailedPayments() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM orders WHERE payment_status = 'failed'";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

/**
 * Update order status
 */
function updateOrderStatus($order_id, $status) {
    global $conn;
    $query = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $status, $order_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Get orders by date range
 */
function getOrdersByDateRange($start_date, $end_date) {
    global $conn;
    $query = "SELECT * FROM orders WHERE created_at BETWEEN ? AND ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Search orders by order ID or payment ID
 */
function searchOrders($search_term) {
    global $conn;
    $search = "%$search_term%";
    $query = "SELECT * FROM orders WHERE order_id LIKE ? OR payment_id LIKE ? OR customer_email LIKE ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sss", $search, $search, $search);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get daily sales statistics
 */
function getDailySalesStats($days = 7) {
    global $conn;
    $query = "SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(amount) as revenue 
              FROM orders 
              WHERE payment_status = 'success' AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
              GROUP BY DATE(created_at)
              ORDER BY date DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $days);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Create tables if they don't exist
 */
function createOrderTables() {
    global $conn;
    
    // Orders table
    $orders_table = "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        order_id VARCHAR(100) UNIQUE NOT NULL,
        payment_id VARCHAR(100) NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        payment_status VARCHAR(50) DEFAULT 'pending',
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
        INDEX(payment_id),
        INDEX(payment_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($conn, $orders_table);
    
    // Order items table
    $items_table = "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id VARCHAR(100),
        product_name VARCHAR(255),
        price DECIMAL(10, 2),
        quantity INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(order_id),
        INDEX(order_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($conn, $items_table);
    
    return true;
}

/**
 * Example usage in your code:
 * 
 * // Get user's orders
 * $orders = getUserOrders($_SESSION['user_id']);
 * 
 * // Get specific order details
 * $order = getOrderDetails('ORDER_123', $_SESSION['user_id']);
 * 
 * // Get dashboard stats
 * $total_revenue = getTotalRevenue();
 * $total_orders = getTotalOrders();
 * $successful = getSuccessfulPayments();
 * $failed = getFailedPayments();
 * 
 * // Get statistics
 * $stats = getDailySalesStats(7); // Last 7 days
 * 
 * // Search orders
 * $results = searchOrders('ORDER_');
 */
?>
