<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: home.php");
    exit();
}

// Check if product data exists
if (!isset($_POST['name']) || !isset($_POST['price'])) {
    $error = "Product information is missing!";
} else {
    // Get product details
    $product_name = htmlspecialchars($_POST['name']);
    $product_price = floatval($_POST['price']);

    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['name'] === $product_name) {
            $item['quantity']++;
            $found = true;
            break;
        }
    }

    // Add new product if not found
    if (!$found) {
        $_SESSION['cart'][] = [
            'name' => $product_name,
            'price' => $product_price,
            'quantity' => 1
        ];
    }

    $success = true;
    $cart_count = count($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Added to Cart</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
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

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }

        .success-icon {
            font-size: 60px;
            margin-bottom: 15px;
            animation: bounce 0.6s ease;
        }

        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        .error-icon {
            font-size: 60px;
            margin-bottom: 15px;
            color: #ff4757;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
            text-align: center;
        }

        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .product-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 24px;
            color: #667eea;
            font-weight: 700;
        }

        .cart-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }

        .cart-info p {
            color: #555;
            font-size: 16px;
        }

        .cart-count {
            font-weight: 700;
            color: #667eea;
            font-size: 18px;
        }

        .buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .btn {
            flex: 1;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            text-align: center;
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
            background: #667eea;
            color: white;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #ff4757;
            margin-bottom: 25px;
        }

        /* Auto redirect countdown */
        .redirect-info {
            margin-top: 20px;
            color: #888;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="header">
                <div class="success-icon">✓</div>
                <h1>Added to Cart!</h1>
                <p>Product successfully added</p>
            </div>

            <div class="content">
                <div class="product-info">
                    <div class="product-name"><?php echo $product_name; ?></div>
                    <div class="product-price">₹<?php echo number_format($product_price, 2); ?></div>
                </div>

                <div class="cart-info">
                    <p>Your cart now has <span class="cart-count"><?php echo $cart_count; ?></span> item(s)</p>
                </div>

                <div class="buttons">
                    <a href="home.php" class="btn btn-secondary">← Continue Shopping</a>
                    <a href="cart.php" class="btn btn-primary">View Cart →</a>
                </div>

                <div class="redirect-info">
                    <p>Redirecting to home in <span id="countdown">3</span> seconds...</p>
                </div>
            </div>

            <script>
                // Auto redirect after 3 seconds
                let seconds = 3;
                const countdownElement = document.getElementById('countdown');
                
                const interval = setInterval(() => {
                    seconds--;
                    countdownElement.textContent = seconds;
                    
                    if (seconds <= 0) {
                        clearInterval(interval);
                        window.location.href = 'home.php';
                    }
                }, 1000);

                // Cancel auto-redirect if user clicks a button
                document.querySelectorAll('.btn').forEach(btn => {
                    btn.addEventListener('click', () => clearInterval(interval));
                });
            </script>

        <?php else: ?>
            <div class="header">
                <div class="error-icon">⚠</div>
                <h1>Error</h1>
                <p>Something went wrong</p>
            </div>

            <div class="content">
                <div class="error-message">
                    <?php echo isset($error) ? $error : "Unknown error occurred"; ?>
                </div>

                <div class="buttons">
                    <a href="home-kinetic.php" class="btn btn-primary">← Back to Home</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>