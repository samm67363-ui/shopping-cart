<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $user_logged_in = true;
} else {
    $user_logged_in = false;
}

require 'db.php';

// Fetch products
$query = "SELECT * FROM products LIMIT 12";
$result = mysqli_query($conn, $query);
$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyTi - Kinetic Commerce Platform</title>
    <link rel="stylesheet" href="modern-style.css">
    <style>
        /* Additional Home Page Styles */
        .nav-links {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-links a, .nav-links button {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-links a:hover::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--electric-purple), var(--accent-cyan));
            animation: slideRight 0.3s ease;
        }

        @keyframes slideRight {
            from { width: 0; }
            to { width: 100%; }
        }

        .search-bar {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .search-bar:focus-within {
            border-color: var(--electric-purple);
            box-shadow: 0 0 20px rgba(124, 58, 237, 0.3);
        }

        .search-bar input {
            background: transparent;
            border: none;
            color: var(--text-primary);
            width: 200px;
            padding: 0;
        }

        .cart-icon {
            position: relative;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .cart-icon:hover {
            transform: scale(1.2) rotate(10deg);
            color: var(--electric-purple);
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--neon-pink);
            color: white;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .section {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 50px;
            text-align: center;
        }

        @media (max-width: 768px) {
            .nav-links {
                gap: 15px;
            }

            .search-bar input {
                width: 100px;
            }

            .section {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">◆ BuyTi</div>
        
        <div class="nav-links">
            <div class="search-bar">
                <input type="text" placeholder="Search products...">
                <span>🔍</span>
            </div>

            <?php if ($user_logged_in): ?>
                <a href="my_orders.php">My Orders</a>
                <a href="cart.php">
                    <span class="cart-icon">
                        🛒
                        <span class="cart-count" id="cart-count">0</span>
                    </span>
                </a>
                <a href="logout.php" class="btn btn-secondary">Logout</a>
            <?php else: ?>
                <a href="login.html" class="btn btn-secondary">Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>FUTURE COMMERCE</h1>
                <p>Experience the next generation of shopping with kinetic design and seamless payments</p>
                <button class="btn btn-primary">Explore Products</button>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="section">
            <h2 class="section-title">Featured Products</h2>
            
            <div class="product-grid">
                <?php foreach ($products as $index => $product): ?>
                    <div class="product-card stagger-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                        <div class="product-image" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(236, 72, 153, 0.1));"></div>
                        <div class="product-info">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">₹<?php echo number_format($product['price']); ?></div>
                        </div>
                        <div class="quick-add">
                            <button class="btn btn-primary" style="width: 100%;">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="section" style="text-align: center;">
            <h2 class="section-title">Ready to Shop?</h2>
            <p style="font-size: 18px; color: var(--text-secondary); margin-bottom: 30px;">
                Experience fast, secure checkout powered by Razorpay
            </p>
            <button class="btn btn-primary">Browse All Products</button>
        </section>
    </main>

    <script src="modern-interactive.js"></script>
    <script>
        // Update cart count
        function updateCartCount() {
            const cart = <?php echo json_encode($_SESSION['cart'] ?? []); ?>;
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cart-count').textContent = count;
        }

        document.addEventListener('DOMContentLoaded', updateCartCount);

        // Add to cart functionality
        document.querySelectorAll('.quick-add .btn').forEach((btn, index) => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                alert('Product added to cart!');
                updateCartCount();
            });
        });
    </script>
</body>
</html>
