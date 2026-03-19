<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

// Handle remove item
if (isset($_POST['remove_item'])) {
    $index = intval($_POST['remove_item']);
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: cart.php");
    exit();
}

// Handle clear cart
if (isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = [];
    header("Location: cart.php");
    exit();
}

// Calculate total
$total = 0;
$shipping = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    $shipping = $total > 5000 ? 0 : 100;
}

$grandTotal = $total + $shipping;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - BuyIt</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
            cursor: none;
            min-height: 100vh;
        }

        .cursor {
            width: 40px;
            height: 40px;
            border: 2px solid #7c3aed;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: all 0.15s ease;
            mix-blend-mode: difference;
        }

        .cursor.hover {
            transform: scale(1.5);
            background: rgba(124, 58, 237, 0.3);
            border-color: #fff;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 24px;
            text-decoration: none;
            color: #fff;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
        }

        .back-btn {
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 50px;
            color: #7c3aed;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            cursor: none;
        }

        .back-btn:hover {
            background: rgba(124, 58, 237, 0.2);
            transform: scale(1.05);
        }

        .container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 60px;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 64px;
            font-weight: 900;
            background: linear-gradient(135deg, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 50px;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cart-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }

        .cart-items {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 30px;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr auto auto;
            gap: 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 15px;
            margin-bottom: 20px;
            align-items: center;
            transition: all 0.3s;
        }

        .cart-item:hover {
            background: rgba(124, 58, 237, 0.1);
            transform: translateX(5px);
        }

        .item-image {
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .item-details h3 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .item-price {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }

        .item-quantity {
            background: rgba(124, 58, 237, 0.2);
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
        }

        .item-total {
            font-size: 24px;
            font-weight: 700;
            color: #7c3aed;
        }

        .remove-btn {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: #ff4757;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: none;
            transition: all 0.3s;
            font-weight: 600;
        }

        .remove-btn:hover {
            background: #ff4757;
            color: #fff;
            transform: scale(1.05);
        }

        .cart-summary {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 20px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 20px;
            animation: slideUp 0.8s 0.2s ease both;
        }

        .summary-title {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 30px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.7);
        }

        .summary-total {
            border-top: 2px solid rgba(124, 58, 237, 0.3);
            padding-top: 20px;
            margin-top: 20px;
            font-size: 32px;
            font-weight: 800;
            color: #7c3aed;
        }

        .checkout-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            cursor: none;
            margin-top: 20px;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.5);
        }

        .clear-btn {
            width: 100%;
            padding: 15px;
            background: rgba(255, 71, 87, 0.1);
            border: 1px solid rgba(255, 71, 87, 0.3);
            border-radius: 12px;
            color: #ff4757;
            font-weight: 600;
            cursor: none;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .clear-btn:hover {
            background: rgba(255, 71, 87, 0.2);
        }

        .empty-cart {
            text-align: center;
            padding: 100px 20px;
        }

        .empty-icon {
            font-size: 100px;
            margin-bottom: 30px;
            opacity: 0.5;
        }

        .empty-cart h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            margin-bottom: 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        @media (max-width: 968px) {
            .cart-layout {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="cursor"></div>

    <nav class="navbar">
        <a href="home.php" class="logo">
            <div class="logo-icon"></div>
            <span>BuyIt</span>
        </a>
        <a href="home.php" class="back-btn">← Continue Shopping</a>
    </nav>

    <div class="container">
        <h1 class="page-title">Shopping Cart</h1>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="empty-cart">
                <div class="empty-icon">🛒</div>
                <h2>Your cart is empty</h2>
                <a href="home.php" class="checkout-btn" style="max-width: 300px; margin: 30px auto 0;">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <div class="cart-item">
                            <div class="item-image">📦</div>
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p class="item-price">₹<?php echo number_format($item['price'], 2); ?> each</p>
                            </div>
                            <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                            <div class="item-total">₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
                            <form method="post" style="margin: 0;">
                                <input type="hidden" name="remove_item" value="<?php echo $index; ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-summary">
                    <h3 class="summary-title">Order Summary</h3>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span>₹<?php echo number_format($total, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span><?php echo $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2); ?></span>
                    </div>
                    <div class="summary-row summary-total">
                        <span>Total</span>
                        <span>₹<?php echo number_format($grandTotal, 2); ?></span>
                    </div>
                    <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
                    <form method="post" style="margin: 0;">
                        <button type="submit" name="clear_cart" class="clear-btn" onclick="return confirm('Clear entire cart?')">Clear Cart</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const cursor = document.querySelector('.cursor');
        const hoverElements = document.querySelectorAll('a, button');

        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX - 20 + 'px';
            cursor.style.top = e.clientY - 20 + 'px';
        });

        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });
    </script>
</body>
</html>