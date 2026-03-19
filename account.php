<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'db.php';

// Fetch user details from database
$user_id = $_SESSION['user_id'];
$query = "SELECT id, NAME, email, PASSWORD, created_at FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Handle the NAME column (uppercase in database)
$userName = isset($user['NAME']) ? $user['NAME'] : (isset($user['email']) ? explode('@', $user['email'])[0] : 'User');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Details - BuyIt</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
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
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .account-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            color: #667eea;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            margin: 0 auto 15px;
            border: 4px solid rgba(255,255,255,0.3);
        }

        .card-header h2 {
            margin: 0;
            font-size: 28px;
        }

        .card-body {
            padding: 40px;
        }

        .info-group {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }

        .info-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .info-value {
            font-size: 18px;
            color: #333;
            font-weight: 500;
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
            border-radius: 8px;
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
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px auto;
            }

            .card-body {
                padding: 25px;
            }

            .action-buttons {
                flex-direction: column;
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
        <div class="account-card">
            <div class="card-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($userName, 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($userName); ?></h2>
            </div>

            <div class="card-body">
                <div class="info-group">
                    <div class="info-label">👤 Full Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($userName); ?></div>
                </div>

                <div class="info-group">
                    <div class="info-label">📧 Email Address</div>
                    <div class="info-value">
                        <?php echo isset($user['email']) ? htmlspecialchars($user['email']) : 'Not provided'; ?>
                    </div>
                </div>

                <div class="info-group">
                    <div class="info-label">📱 Phone Number</div>
                    <div class="info-value">Not provided</div>
                </div>

                <div class="info-group">
                    <div class="info-label">📅 Member Since</div>
                    <div class="info-value">
                        <?php 
                        echo isset($user['created_at']) 
                            ? date('F d, Y', strtotime($user['created_at'])) 
                            : 'January 11, 2026';
                        ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="my_orders-kinetic.php" class="btn btn-primary">View My Orders</a>
                    <a href="home-kinetic.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>