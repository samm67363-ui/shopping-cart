<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

require 'db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: home.php");
    exit;
}

$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$shipping = $subtotal > 5000 ? 0 : 100;
$total = $subtotal + $shipping;

$user_id = $_SESSION['user_id'];
$query = "SELECT NAME, email FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - BuyIt</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #09090b;
            color: #fff;
            cursor: none;
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
        }

        .navbar {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 60px;
            text-align: center;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            font-weight: 900;
            background: linear-gradient(135deg, #fff, #f0c14b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 60px;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 40px;
        }

        .checkout-section {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 40px;
            animation: slideUp 0.8s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
            cursor: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #7c3aed;
            background: rgba(255, 255, 255, 0.05);
        }

        .payment-methods {
            display: grid;
            gap: 15px;
            margin: 30px 0;
        }

        .payment-option {
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            cursor: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-option:hover {
            border-color: #7c3aed;
            background: rgba(124, 58, 237, 0.1);
        }

        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #7c3aed;
        }

        .order-summary {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 20px;
            padding: 40px;
            height: fit-content;
            position: sticky;
            top: 20px;
            animation: slideUp 0.8s 0.2s ease both;
        }

        .summary-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .summary-item {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
        }

        .summary-total {
            font-size: 32px;
            font-weight: 800;
            color: #7c3aed;
            margin: 30px 0;
            text-align: center;
        }

        .place-order-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            cursor: none;
            transition: all 0.3s;
        }

        .place-order-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.5);
        }

        @media (max-width: 968px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="cursor"></div>

    <nav class="navbar">
        <h1 class="page-title">BuyIt - Secure Checkout</h1>
    </nav>

    <div class="container">
        <div class="checkout-section">
            <h2 class="section-title">📍 Delivery Information</h2>
            <form action="process_payment.php" method="POST" onsubmit="return handlePaymentCompletion();">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['NAME']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" pattern="[0-9]{10}" required>
                </div>

                <div class="form-group">
                    <label>Complete Address *</label>
                    <textarea name="address" rows="3" placeholder="House No., Street, Area" required></textarea>
                </div>

                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" required>
                </div>

                <div class="form-group">
                    <label>State *</label>
                    <input type="text" name="state" required>
                </div>

                <div class="form-group">
                    <label>PIN Code *</label>
                    <input type="text" name="pincode" pattern="[0-9]{6}" placeholder="400001" required>
                </div>

                <h2 class="section-title">💳 Payment Method</h2>
                <div class="payment-methods">
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" required>
                        <div>
                            <strong>💵 Cash on Delivery</strong>
                            <p style="font-size: 14px; color: rgba(255,255,255,0.6); margin-top: 5px;">Pay when you receive</p>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="upi">
                        <div>
                            <strong>📱 UPI Payment</strong>
                            <p style="font-size: 14px; color: rgba(255,255,255,0.6); margin-top: 5px;">Google Pay, PhonePe, Paytm</p>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="card">
                        <div>
                            <strong>💳 Credit / Debit Card</strong>
                            <p style="font-size: 14px; color: rgba(255,255,255,0.6); margin-top: 5px;">Visa, Mastercard, RuPay</p>
                        </div>
                    </label>
                </div>

                <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                <button type="submit" class="place-order-btn">Place Order - ₹<?php echo number_format($total, 2); ?></button>
            </form>
        </div>

        <div class="order-summary">
            <h3 class="section-title">Order Summary</h3>
            <div class="summary-items">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="summary-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="summary-item">
                <span>Subtotal</span>
                <span>₹<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <div class="summary-item">
                <span>Shipping</span>
                <span><?php echo $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2); ?></span>
            </div>
            <div class="summary-total">
                Total: ₹<?php echo number_format($total, 2); ?>
            </div>
            <div style="text-align: center; padding: 15px; background: rgba(124, 58, 237, 0.1); border-radius: 12px; font-size: 14px;">
                🔒 Your payment is secure
            </div>
        </div>
    </div>

    <script>
        const cursor = document.querySelector('.cursor');
        const hoverElements = document.querySelectorAll('a, button, input, textarea, label');

        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX - 20 + 'px';
            cursor.style.top = e.clientY - 20 + 'px';
        });

        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });

        function handlePaymentCompletion() {
            // Simulate payment success and redirect to orders page
            alert('Payment successful! Redirecting to your orders...');
            window.location.href = 'orders.php';
            return false; // Prevent default form submission
        }
    </script>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - BuyIt</title>
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
            text-align: center;
        }

        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #f0c14b;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        /* Payment Options */
        .payment-methods {
            margin-top: 20px;
        }

        .payment-option {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }

        .payment-option input[type="radio"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .payment-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

        .payment-icon {
            font-size: 32px;
        }

        .payment-details {
            flex: 1;
        }

        .payment-name {
            font-weight: 600;
            font-size: 16px;
            color: #333;
        }

        .payment-desc {
            font-size: 13px;
            color: #666;
            margin-top: 3px;
        }

        /* UPI Details Form */
        .upi-details,
        .card-details {
            margin-top: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            display: none;
        }

        .upi-details.active,
        .card-details.active {
            display: block;
        }

        /* Order Summary */
        .order-summary {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 15px;
        }

        .summary-total {
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #667eea;
        }

        .cart-items {
            max-height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .place-order-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }

        .place-order-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }

        .place-order-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .back-link {
            display: inline-block;
            color: #667eea;
            text-decoration: none;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 968px) {
            .container {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">BuyIt - Secure Checkout</div>
    </div>

    <div class="container">
        <div class="checkout-section">
            <a href="cart.php" class="back-link">← Back to Cart</a>
            
            <h2 class="section-title">📍 Delivery Address</h2>
            <form id="checkoutForm" method="post" action="process_payment.php">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['NAME']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" name="phone" placeholder="+91 XXXXX XXXXX" required pattern="[0-9]{10}">
                </div>

                <div class="form-group">
                    <label>Complete Address *</label>
                    <textarea name="address" rows="3" placeholder="House No., Street, Area" required></textarea>
                </div>

                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" required>
                </div>

                <div class="form-group">
                    <label>State *</label>
                    <input type="text" name="state" required>
                </div>

                <div class="form-group">
                    <label>PIN Code *</label>
                    <input type="text" name="pincode" pattern="[0-9]{6}" placeholder="400001" required>
                </div>

                <h2 class="section-title">💳 Payment Method</h2>
                <div class="payment-methods">
                    <!-- UPI Payment -->
                    <label class="payment-option" onclick="selectPayment('upi')">
                        <input type="radio" name="payment_method" value="upi" id="payment-upi" required>
                        <div class="payment-icon">📱</div>
                        <div class="payment-details">
                            <div class="payment-name">UPI Payment</div>
                            <div class="payment-desc">Google Pay, PhonePe, Paytm & more</div>
                        </div>
                    </label>
                    <div class="upi-details" id="upi-details">
                        <div class="form-group">
                            <label>UPI ID</label>
                            <input type="text" name="upi_id" placeholder="yourname@paytm" pattern="[a-zA-Z0-9.\-_]{2,}@[a-zA-Z]{2,}">
                        </div>
                    </div>

                    <!-- Net Banking -->
                    <label class="payment-option" onclick="selectPayment('netbanking')">
                        <input type="radio" name="payment_method" value="netbanking" id="payment-netbanking" required>
                        <div class="payment-icon">🏦</div>
                        <div class="payment-details">
                            <div class="payment-name">Net Banking</div>
                            <div class="payment-desc">All major banks supported</div>
                        </div>
                    </label>

                    <!-- Debit/Credit Card -->
                    <label class="payment-option" onclick="selectPayment('card')">
                        <input type="radio" name="payment_method" value="card" id="payment-card" required>
                        <div class="payment-icon">💳</div>
                        <div class="payment-details">
                            <div class="payment-name">Credit / Debit Card</div>
                            <div class="payment-desc">Visa, Mastercard, RuPay</div>
                        </div>
                    </label>
                    <div class="card-details" id="card-details">
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX" maxlength="19">
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Expiry</label>
                                <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label>CVV</label>
                                <input type="text" name="card_cvv" placeholder="XXX" maxlength="3">
                            </div>
                        </div>
                    </div>

                    <!-- Cash on Delivery -->
                    <label class="payment-option" onclick="selectPayment('cod')">
                        <input type="radio" name="payment_method" value="cod" id="payment-cod" required>
                        <div class="payment-icon">💵</div>
                        <div class="payment-details">
                            <div class="payment-name">Cash on Delivery</div>
                            <div class="payment-desc">Pay when you receive</div>
                        </div>
                    </label>
                </div>

                <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                <button type="submit" class="place-order-btn">Place Order - ₹<?php echo number_format($total, 2); ?></button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h3 class="section-title">Order Summary</h3>
            
            <div class="cart-items">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div class="cart-item">
                        <span><?php echo htmlspecialchars($item['name']); ?> × <?php echo $item['quantity']; ?></span>
                        <span>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-item">
                <span>Subtotal:</span>
                <span>₹<?php echo number_format($subtotal, 2); ?></span>
            </div>

            <div class="summary-item">
                <span>Shipping:</span>
                <span><?php echo $shipping == 0 ? 'FREE' : '₹' . number_format($shipping, 2); ?></span>
            </div>

            <div class="summary-item summary-total">
                <span>Total:</span>
                <span>₹<?php echo number_format($total, 2); ?></span>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #f0f4ff; border-radius: 8px; font-size: 13px; color: #666;">
                🔒 Your payment information is secure and encrypted
            </div>
        </div>
    </div>

    <script>
        function selectPayment(method) {
            // Remove all selected classes
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selected class to clicked option
            document.getElementById('payment-' + method).closest('.payment-option').classList.add('selected');

            // Hide all detail forms
            document.querySelectorAll('.upi-details, .card-details').forEach(detail => {
                detail.classList.remove('active');
            });

            // Show relevant detail form
            if (method === 'upi') {
                document.getElementById('upi-details').classList.add('active');
            } else if (method === 'card') {
                document.getElementById('card-details').classList.add('active');
            }
        }

        // Auto-format card number
        document.querySelector('input[name="card_number"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            e.target.value = formattedValue;
        });

        // Auto-format expiry
        document.querySelector('input[name="card_expiry"]')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0,2) + '/' + value.slice(2,4);
            }
            e.target.value = value;
        });

        function handlePaymentCompletion() {
            // Simulate payment success and redirect to orders page
            alert('Payment successful! Redirecting to your orders...');
            window.location.href = 'orders.php';
            return false; // Prevent default form submission
        }
    </script>
</body>
</html>