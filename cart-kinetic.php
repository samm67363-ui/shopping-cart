<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login-kinetic.html");
    exit;
}

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal > 5000 ? 0 : 100;
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - BuyTi</title>
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

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 40px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 40px;
        }

        .cart-items {
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 30px;
        }

        .cart-items h2 {
            font-size: 32px;
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            animation: slideUp 0.5s ease-out;
        }

        .cart-item:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }

        .cart-item-image {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(236, 72, 153, 0.1));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .cart-item-price {
            color: var(--electric-purple);
            font-weight: 600;
            margin-bottom: 12px;
        }

        .cart-item-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .quantity-control {
            display: flex;
            gap: 10px;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 5px 10px;
        }

        .quantity-control button {
            background: none;
            border: none;
            color: var(--electric-purple);
            cursor: pointer;
            font-size: 16px;
            padding: 5px 10px;
            transition: all 0.2s ease;
        }

        .quantity-control button:hover {
            transform: scale(1.2);
            color: var(--purple-glow);
        }

        .remove-btn {
            background: rgba(236, 72, 153, 0.2);
            border: none;
            color: var(--neon-pink);
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .remove-btn:hover {
            background: rgba(236, 72, 153, 0.4);
            transform: scale(1.05);
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .empty-cart p {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .continue-shopping {
            display: inline-block;
            padding: 12px 32px;
            background: linear-gradient(135deg, var(--electric-purple), var(--purple-glow));
            color: white;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .continue-shopping:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4);
        }

        .cart-summary {
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 30px;
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .summary-row.total {
            font-size: 18px;
            font-weight: 700;
            color: var(--electric-purple);
            border-top: 2px solid rgba(124, 58, 237, 0.3);
            padding-top: 15px;
            margin-top: 15px;
            border-bottom: none;
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--electric-purple), var(--purple-glow));
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }

        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(124, 58, 237, 0.5);
        }

        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .cart-item {
                flex-direction: column;
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

    <main class="cart-container">
        <!-- Cart Items -->
        <div class="cart-items">
            <h2>Shopping Cart</h2>
            
            <?php if (empty($cart)): ?>
                <div class="empty-cart">
                    <p>🛒 Your cart is empty</p>
                    <a href="home-kinetic.php" class="continue-shopping">Continue Shopping</a>
                </div>
            <?php else: ?>
                <?php foreach ($cart as $index => $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-image">📦</div>
                        <div class="cart-item-details">
                            <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="cart-item-price">₹<?php echo number_format($item['price']); ?></div>
                            <div class="cart-item-actions">
                                <div class="quantity-control">
                                    <button onclick="updateQuantity(<?php echo $index; ?>, -1)">−</button>
                                    <span><?php echo $item['quantity']; ?></span>
                                    <button onclick="updateQuantity(<?php echo $index; ?>, 1)">+</button>
                                </div>
                                <button class="remove-btn" onclick="removeItem(<?php echo $index; ?>)">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Cart Summary -->
        <div class="cart-summary">
            <h3 style="margin-bottom: 20px; font-size: 24px;">Order Summary</h3>
            
            <div class="summary-section">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($subtotal); ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span><?php echo $shipping == 0 ? 'FREE' : '₹' . number_format($shipping); ?></span>
                </div>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <span>₹<?php echo number_format($total); ?></span>
            </div>

            <?php if (!empty($cart)): ?>
                <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
            <?php endif; ?>
        </div>
    </main>

    <script src="modern-interactive.js"></script>
    <script>
        function updateQuantity(index, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'index=' + index + '&change=' + change
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function removeItem(index) {
            if (confirm('Remove item from cart?')) {
                fetch('remove_cart_item.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'index=' + index
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
    </script>
</body>
</html>
