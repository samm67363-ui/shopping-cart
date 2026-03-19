<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'db.php';

// Fetch user orders from database
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = [];

while($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders - BuyIt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #09090b;
            color: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header {
            background: #131921;
            color: white;
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #f0c14b;
            cursor: pointer;
        }

        .back-btn {
            background: #f0c14b;
            color: #131921;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 32px;
        }

        .empty-state {
            background: white;
            border-radius: 12px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            color: #666;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .shop-now-btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }

        .shop-now-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }

        .order-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 15px;
        }

        .order-id {
            font-weight: 600;
            color: #333;
        }

        .order-date {
            color: #666;
            font-size: 14px;
        }

        .order-status {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-shipped {
            background: #d4edda;
            color: #155724;
        }

        .status-delivered {
            background: #d4edda;
            color: #155724;
        }

        .order-items {
            margin-top: 15px;
        }

        .order-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
            text-align: right;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }

            h1 {
                font-size: 24px;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo" onclick="window.location.href='home.php'">BuyIt</div>
        <a href="home.php" class="back-btn">← Back to Home</a>
    </div>

    <div class="container">
        <h1>My Orders</h1>
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order">
                    <h3>Order #<?php echo $order['id']; ?></h3>
                    <p>Status: <span class="status"><?php echo $order['status']; ?></span></p>
                    <p>Total: ₹<?php echo number_format($order['total'], 2); ?></p>
                    <p>Placed on: <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>