<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header("Location: home.php");
    exit;
}

require 'db.php';

$order_id = mysqli_real_escape_string($conn, $_GET['order_id']);

// Fetch order details
$query = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Placed - BuyIt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            overflow: hidden;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .success-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .success-header p {
            font-size: 16px;
            opacity: 0.95;
        }

        .order-details {
            padding: 40px 30px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .detail-label {
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
            text-align: right;
        }

        .order-id {
            background: #f0f4ff;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }

        .order-id-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
        }

        .order-id-value {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            letter-spacing: 1px;
        }

        .total-amount {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }

        .total-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .total-value {
            font-size: 36px;
            font-weight: 700;
            color: #667eea;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        .info-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 14px;
            color: #856404;
        }

        @media (max-width: 768px) {
            .success-header h1 {
                font-size: 24px;
            }

            .order-id-value {
                font-size: 18px;
            }

            .total-value {
                font-size: 28px;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <div class="success-icon">✓</div>
            <h1>Order Placed Successfully!</h1>
            <p>Thank you for shopping with BuyIt</p>
        </div>

        <div class="order-details">
            <div class="order-id">
                <div class="order-id-label">ORDER ID</div>
                <div class="order-id-value"><?php echo htmlspecialchars($order['order_id']); ?></div>
            </div>

            <div class="detail-row">
                <span class="detail-label">Customer Name:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['customer_email']); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Phone:</span>
                <span class="detail-value"><?php echo htmlspecialchars($order['customer_phone']); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">
                    <?php 
                    $methods = [
                        'upi' => 'UPI Payment',
                        'netbanking' => 'Net Banking',
                        'card' => 'Card Payment',
                        'cod' => 'Cash on Delivery'
                    ];
                    echo $methods[$order['payment_method']] ?? 'N/A';
                    ?>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Order Status:</span>
                <span class="detail-value">
                    <?php 
                        $status_label = $order['status'];
                        if (strtolower($order['status']) === 'processing') {
                            $status_label = 'Delivery In Progress';
                        }
                    ?>
                    <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                        <?php echo $status_label; ?>
                    </span>
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value">
                    <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span>
                </span>
            </div>

            <div class="total-amount">
                <div class="total-label">Total Amount</div>
                <div class="total-value">₹<?php echo number_format($order['total'], 2); ?></div>
            </div>

            <?php if ($order['payment_method'] === 'cod'): ?>
                <div class="info-box">
                    💵 <strong>Cash on Delivery</strong><br>
                    Please keep exact change ready when your order arrives.
                </div>
            <?php else: ?>
                <div class="info-box" style="background: #d4edda; border-color: #28a745; color: #155724;">
                    ✓ <strong>Payment Received</strong><br>
                    Your payment has been processed successfully.
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
                <a href="home.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</body>
</html>
