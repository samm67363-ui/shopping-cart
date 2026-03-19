<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login-kinetic.html");
    exit;
}
require 'db.php';

$products = mysqli_query($conn, "SELECT * FROM products");
$cartCount = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyTi - Kinetic Commerce</title>
    <link rel="stylesheet" href="modern-style.css">
    <style>
        .nav-search {
            flex: 1;
            min-width: 250px;
        }

        .nav-search input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .nav-search input:focus {
            outline: none;
            border-color: var(--electric-purple);
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 20px rgba(124, 58, 237, 0.3);
        }

        .products-section {
            padding: 60px 40px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .product-card {
            position: relative;
            background: var(--glass-white);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            padding: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-15px);
            background: var(--glass-white-hover);
            border-color: var(--electric-purple);
            box-shadow: 0 30px 60px rgba(124, 58, 237, 0.3);
        }

        .product-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.2), rgba(236, 72, 153, 0.1));
            border-radius: 12px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            transition: all 0.3s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-name {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 700;
            color: white;
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 20px;
            font-weight: 700;
            color: var(--electric-purple);
            margin-bottom: 12px;
        }

        .product-actions {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(180deg, transparent, var(--dark-bg));
            padding: 20px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            display: flex;
            gap: 10px;
        }

        .product-card:hover .product-actions {
            transform: translateY(0);
        }

        .product-actions button {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            background: var(--electric-purple);
            color: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .product-actions button:hover {
            background: var(--purple-glow);
            transform: scale(1.05);
        }

        .section-title {
            font-size: 48px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--electric-purple), var(--purple-glow), var(--accent-cyan));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }

            .products-section {
                padding: 40px 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">◆ BuyTi</div>
        
        <div class="nav-search">
            <input type="text" placeholder="Search products..." id="searchInput">
        </div>

        <div class="nav-links">
            <a href="cart-kinetic.php" class="interactive">
                <span class="cart-icon">
                    🛒
                    <span class="cart-count" id="cart-count"><?php echo $cartCount; ?></span>
                </span>
            </a>
            <a href="my_orders-kinetic.php">Orders</a>
            <a href="logout.php" class="btn btn-secondary">Logout</a>
        </div>
    </header>

    <main>
        <!-- Products Section -->
        <section class="products-section">
            <h2 class="section-title">Featured Products</h2>
            
            <div class="products-grid" id="productsGrid">
                <?php
                $index = 0;
                while ($row = mysqli_fetch_assoc($products)) {
                    $index++;
                    ?>
                    <div class="product-card stagger-in" style="animation-delay: <?php echo ($index % 6) * 0.1; ?>s">
                        <div class="product-image">📦</div>
                        <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="product-price">₹<?php echo number_format($row['price']); ?></div>
                        <div class="product-actions">
                            <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', <?php echo $row['price']; ?>)">Add</button>
                            <button onclick="viewProduct(<?php echo $row['id']; ?>)">View</button>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </section>
    </main>

    <script src="modern-interactive.js"></script>
    <script>
        function addToCart(id, name, price) {
            // Store in session via AJAX
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + id + '&name=' + encodeURIComponent(name) + '&price=' + price
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.cartCount;
                    alert('✅ Product added to cart!');
                }
            });
        }

        function viewProduct(id) {
            // Can implement product detail page later
            alert('Product detail page coming soon!');
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.querySelector('.product-name').textContent.toLowerCase();
                card.style.display = name.includes(query) ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
