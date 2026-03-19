<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';
require 'razorpay_helpers.php';

$user_id = $_SESSION['user_id'];

// Get user's orders
$orders = getUserOrders($user_id);

// Get dashboard stats (admin only - modify as needed)
$total_revenue = getTotalRevenue();
$total_orders = getTotalOrders();
$successful_payments = getSuccessfulPayments();
$failed_payments = getFailedPayments();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MyStore</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .header {
            background: #131921;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        .header .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .header a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            transition: all 0.3s;
        }

        .header a:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            color: white;
            margin-bottom: 30px;
            text-align: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .stat-card .value {
            color: #667eea;
            font-size: 32px;
            font-weight: bold;
        }

        .orders-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .orders-section h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 22px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .empty-message {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .empty-message h3 {
            margin-bottom: 10px;
            color: #666;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }

        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .orders-table tr:hover {
            background: #f9f9f9;
        }

        .order-id {
            font-weight: 600;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-success {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }

        .amount {
            color: #28a745;
            font-weight: 600;
        }

        .date {
            color: #999;
            font-size: 14px;
        }

        .action-btn {
            display: inline-block;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .action-btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .orders-table {
                font-size: 13px;
            }

            .orders-table th,
            .orders-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">📦 MyStore - Orders</div>
        <div>
            <a href="home.php">← Back to Shop</a>
        </div>
    </div>

    <div class="container">
        <h1>Your Orders</h1>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="value"><?php echo count($orders); ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Spent</h3>
                <div class="value">₹<?php 
                    $total = 0;
                    foreach ($orders as $order) {
                        if ($order['payment_status'] == 'success') {
                            $total += $order['amount'];
                        }
                    }
                    echo number_format($total, 2);
                ?></div>
            </div>
            <div class="stat-card">
                <h3>Successful Payments</h3>
                <div class="value"><?php 
                    $count = 0;
                    foreach ($orders as $order) {
                        if ($order['payment_status'] == 'success') $count++;
                    }
                    echo $count;
                ?></div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="orders-section">
            <h2>📋 Order History</h2>

            <?php if (empty($orders)): ?>
                <div class="empty-message">
                    <h3>No orders yet</h3>
                    <p>You haven't placed any orders yet. <a href="home.php" style="color: #667eea; text-decoration: underline;">Start shopping</a></p>
                </div>
            <?php else: ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><span class="order-id"><?php echo htmlspecialchars($order['order_id']); ?></span></td>
                                <td><small><?php echo htmlspecialchars(substr($order['payment_id'], 0, 15) . '...'); ?></small></td>
                                <td><span class="amount">₹<?php echo number_format($order['amount'], 2); ?></span></td>
                                <td>
                                    <?php 
                                        $ps = strtolower($order['payment_status']);
                                        $label = $ps === 'success' ? 'Success' : ($ps === 'pending' ? 'Ongoing' : 'Failed');
                                        $cls = $ps === 'success' ? 'status-success' : ($ps === 'pending' ? 'status-pending' : 'status-failed');
                                    ?>
                                    <span class="status-badge <?php echo $cls; ?>">
                                        <?php echo $label; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="date"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span><br>
                                    <small style="color: #bbb;"><?php echo date('h:i A', strtotime($order['created_at'])); ?></small>
                                </td>
                                <td>
                                    <a href="order_details.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>" class="action-btn">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
