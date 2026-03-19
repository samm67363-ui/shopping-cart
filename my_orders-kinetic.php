<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login-kinetic.html");
    exit;
}

require 'db.php';

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - BuyTi</title>
    <link rel="stylesheet" href="modern-style.css">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(9, 9, 11, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(124, 58, 237, 0.2);
        }

        .orders-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .orders-title {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 40px;
            background: linear-gradient(135deg, var(--electric-purple), var(--purple-glow), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .order-card {
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            animation: slideUp 0.5s ease-out;
            cursor: pointer;
        }

        .order-card:hover {
            transform: translateY(-5px);
            background: var(--glass-white-hover);
            border-color: var(--electric-purple);
            box-shadow: 0 15px 40px rgba(124, 58, 237, 0.3);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-id {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: var(--electric-purple);
        }

        .order-status {
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-completed {
            background: rgba(34, 197, 94, 0.2);
            color: #22c55e;
        }

        .status-pending {
            background: rgba(251, 146, 60, 0.2);
            color: #fb923c;
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
        }

        .order-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }

        .detail-item {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .detail-label {
            color: var(--text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            color: var(--text-primary);
            font-weight: 600;
            font-size: 16px;
        }

        .order-amount {
            font-size: 20px;
            font-weight: 700;
            color: var(--electric-purple);
        }

        .view-details-btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--electric-purple), var(--purple-glow));
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 14px;
        }

        .view-details-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-state p {
            font-size: 24px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .order-details {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .orders-container {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">◆ BuyTi</div>
        <div>
            <a href="home-kinetic.php" class="btn btn-secondary">← Back to Shopping</a>
        </div>
    </header>

    <main class="orders-container">
        <h2 class="orders-title">Your Orders</h2>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <p>📦 No orders yet</p>
                <p style="font-size: 16px;">Start shopping to see your orders here</p>
                <a href="home-kinetic.php" class="view-details-btn">Continue Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo $order['order_id']; ?></div>
                            <div style="color: var(--text-secondary); font-size: 14px; margin-top: 5px;">
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <span class="order-status status-<?php echo strtolower($order['payment_status']); ?>">
                            <?php echo ucfirst($order['payment_status']); ?>
                        </span>
                    </div>

                    <div class="order-details">
                        <div class="detail-item">
                            <div class="detail-label">Delivery Address</div>
                            <div class="detail-value"><?php echo htmlspecialchars($order['customer_city']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Total Amount</div>
                            <div class="order-amount">₹<?php echo number_format($order['amount']); ?></div>
                        </div>
                        <div class="detail-item" style="text-align: right;">
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="view-details-btn">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script src="modern-interactive.js"></script>
    <script>
        function viewOrderDetails(id) {
            window.location.href = 'order_details.php?id=' + id;
        }
    </script>
</body>
</html>
