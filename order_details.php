<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: my_orders.php");
    exit;
}

require 'db.php';
require 'razorpay_helpers.php';

$user_id = $_SESSION['user_id'];
$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Get order details
$order = getOrderDetails($order_id, $user_id);

if (!$order) {
    header("Location: my_orders.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - MyStore</title>
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
            margin-bottom: 30px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header a {
            color: white;
            text-decoration: none;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .card h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 20px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row label {
            color: #666;
            font-weight: 600;
        }

        .info-row value {
            color: #333;
            font-weight: 500;
        }

        .info-row.highlight {
            background: #f8f9fa;
            padding: 12px;
            margin: 10px -25px 0;
            border-radius: 8px;
            border: none;
            margin-left: -25px;
            margin-right: -25px;
            padding-left: 25px;
            padding-right: 25px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
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

        .items-list {
            margin: 20px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 8px;
        }

        .item-name {
            font-weight: 600;
            color: #333;
        }

        .item-price {
            color: #667eea;
            font-weight: 600;
        }

        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            color: #333;
        }

        .summary-total {
            border-top: 2px solid #e0e0e0;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }

        .address-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="font-size: 24px; font-weight: bold;">📦 Order Details</div>
        <a href="my_orders.php">← Back to Orders</a>
    </div>

    <div class="container">
        <div>
            <!-- Order Information -->
            <div class="card">
                <h2>📋 Order Information</h2>
                
                <div class="info-row">
                    <label>Order ID:</label>
                    <value><?php echo htmlspecialchars($order['order_id']); ?></value>
                </div>

                <div class="info-row">
                    <label>Payment ID:</label>
                    <value><small><?php echo htmlspecialchars($order['payment_id']); ?></small></value>
                </div>

                <div class="info-row">
                    <label>Status:</label>
                    <value>
                        <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </value>
                </div>

                <div class="info-row">
                    <label>Order Date:</label>
                    <value><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></value>
                </div>

                <div class="info-row highlight">
                    <label>Total Amount:</label>
                    <value style="color: #28a745; font-size: 18px;">₹<?php echo number_format($order['amount'], 2); ?></value>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="card">
                <h2>📍 Shipping Address</h2>
                
                <div class="address-box">
                    <div style="font-weight: 600; margin-bottom: 8px;"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                    <div><?php echo htmlspecialchars($order['customer_address']); ?></div>
                    <div><?php echo htmlspecialchars($order['customer_city']); ?>, <?php echo htmlspecialchars($order['customer_state']); ?> <?php echo htmlspecialchars($order['customer_pincode']); ?></div>
                    <div style="margin-top: 10px; color: #666;">
                        <div>📧 <?php echo htmlspecialchars($order['customer_email']); ?></div>
                        <div>📱 <?php echo htmlspecialchars($order['customer_phone']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card">
                <h2>📦 Items Ordered</h2>
                
                <div class="items-list">
                    <?php 
                    $total = 0;
                    foreach ($order['items'] as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                        <div class="item-row">
                            <div>
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <small style="color: #999;">₹<?php echo number_format($item['price'], 2); ?> × <?php echo $item['quantity']; ?></small>
                            </div>
                            <div class="item-price">₹<?php echo number_format($item_total, 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span>Free</span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span>₹<?php echo number_format($order['amount'], 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div>
            <!-- Quick Info -->
            <div class="card">
                <h2>ℹ️ Quick Info</h2>
                
                <div style="line-height: 1.8; color: #666; font-size: 14px;">
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #333;">Order Status:</strong><br>
                        <?php if ($order['payment_status'] == 'success'): ?>
                            <span style="color: #28a745;">✅ Payment Successful</span>
                        <?php elseif ($order['payment_status'] == 'pending'): ?>
                            <span style="color: #ffc107;">⏳ Payment Pending</span>
                        <?php else: ?>
                            <span style="color: #dc3545;">❌ Payment Failed</span>
                        <?php endif; ?>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong style="color: #333;">Order Placed:</strong><br>
                        <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong style="color: #333;">Items:</strong><br>
                        <?php 
                        $count = 0;
                        foreach ($order['items'] as $item) {
                            $count += $item['quantity'];
                        }
                        echo $count . ' item(s)';
                        ?>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong style="color: #333;">Payment Method:</strong><br>
                        Razorpay (Card/UPI/Net Banking)
                    </div>
                </div>

                <button onclick="window.print();" class="btn" style="background: #6c757d;">🖨️ Print Invoice</button>
            </div>

            <!-- Help -->
            <div class="card" style="margin-top: 20px;">
                <h2>❓ Need Help?</h2>
                <p style="color: #666; font-size: 14px; line-height: 1.6;">
                    If you have any questions about this order, please contact our support team.
                </p>
                <a href="mailto:support@mystore.com" class="btn" style="width: 100%; text-align: center;">📧 Contact Support</a>
            </div>
        </div>
    </div>
</body>
</html>
