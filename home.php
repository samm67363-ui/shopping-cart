<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login_kinetic.html");
    exit;
}

require 'db.php';

// Get products from database
$products = mysqli_query($conn, "SELECT * FROM products");
$cartCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

// Get user name
$user_id = $_SESSION['user_id'];
$query = "SELECT NAME FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
$userName = $user['NAME'] ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BuyIt - Future Commerce</title>
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
            overflow-x: hidden;
            cursor: none;
        }

        /* Custom Magnetic Cursor */
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

        /* Glassmorphic Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 20px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideDown 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideDown {
            from { transform: translateY(-100%); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 24px;
            cursor: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .logo:hover .logo-icon {
            transform: rotate(360deg);
        }

        .nav-links {
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-weight: 500;
            position: relative;
            transition: color 0.3s;
            cursor: none;
        }

        .nav-link:hover {
            color: #fff;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: #7c3aed;
            transition: width 0.3s;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .cart-icon {
            position: relative;
            cursor: none;
            transition: transform 0.3s;
        }

        .cart-icon:hover {
            transform: scale(1.2) rotate(10deg);
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #7c3aed;
            color: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }

        .profile-menu {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: none;
            padding: 8px 16px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s;
        }

        .profile-trigger:hover {
            background: rgba(124, 58, 237, 0.2);
            transform: scale(1.05);
        }

        .profile-pic {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            background: rgba(20, 20, 30, 0.95);
            backdrop-filter: blur(20px);
            min-width: 220px;
            border-radius: 12px;
            border: 1px solid rgba(124, 58, 237, 0.3);
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }

        .profile-menu:hover .dropdown-content {
            display: block;
            animation: dropdownFade 0.3s ease;
        }

        @keyframes dropdownFade {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dropdown-item {
            padding: 15px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .dropdown-item:hover {
            background: rgba(124, 58, 237, 0.2);
            color: #fff;
        }

        .dropdown-item.logout {
            border-top: 1px solid rgba(124, 58, 237, 0.3);
            color: #ff4757;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }

        .orb {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.3) 0%, transparent 70%);
            filter: blur(80px);
            animation: orbPulse 4s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes orbPulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.2); opacity: 0.8; }
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: clamp(60px, 12vw, 140px);
            font-weight: 900;
            text-align: center;
            background: linear-gradient(135deg, #fff 0%, #7c3aed 50%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 30px;
            animation: titleEntry 1s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
        }

        @keyframes titleEntry {
            from { opacity: 0; transform: translateY(50px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hero-subtitle {
            font-size: 20px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 50px;
            animation: titleEntry 1s cubic-bezier(0.16, 1, 0.3, 1) 0.5s both;
        }

        .search-container {
            position: relative;
            width: 600px;
            max-width: 90%;
            animation: titleEntry 1s cubic-bezier(0.16, 1, 0.3, 1) 0.7s both;
        }

        .search-input {
            width: 100%;
            padding: 20px 60px 20px 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            color: #fff;
            font-size: 16px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
            cursor: none;
        }

        .search-input:focus {
            outline: none;
            border-color: #7c3aed;
            background: rgba(255, 255, 255, 0.1);
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #7c3aed;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            cursor: none;
            transition: all 0.3s;
        }

        .search-btn:hover {
            transform: translateY(-50%) scale(1.1) rotate(90deg);
            background: #8b5cf6;
        }

        /* Products Section */
        .products-section {
            padding: 100px 60px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 60px;
            background: linear-gradient(135deg, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 40px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: none;
            animation: cardEntry 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .product-card:hover {
            transform: translateY(-10px);
            border-color: rgba(124, 58, 237, 0.5);
            box-shadow: 0 20px 60px rgba(124, 58, 237, 0.3);
        }

        .product-image-wrapper {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.02);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .product-card:hover .product-image {
            transform: scale(1.1);
        }

        .quick-add-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            padding: 15px;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-align: center;
            font-weight: 600;
            cursor: none;
        }

        .product-card:hover .quick-add-overlay {
            transform: translateY(0);
        }

        .product-info {
            padding: 25px;
        }

        .product-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #7c3aed;
        }

        @media (max-width: 768px) {
            .navbar { padding: 15px 20px; }
            .hero-title { font-size: 48px; }
            .products-section { padding: 60px 20px; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="cursor"></div>

    <nav class="navbar">
        <div class="logo">
            <div class="logo-icon"></div>
            <span>BuyIt</span>
        </div>
        
        <div class="nav-links">
            <a href="home.php" class="nav-link">Home</a>
            <a href="cart.php" class="cart-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <div class="profile-menu">
                <div class="profile-trigger">
                    <div class="profile-pic"><?php echo $userInitial; ?></div>
                    <span style="font-size: 14px;"><?php echo htmlspecialchars($userName); ?></span>
                </div>
                <div class="dropdown-content">
                    <a href="account.php" class="dropdown-item">👤 Account</a>
                    <a href="orders.php" class="dropdown-item">📦 Orders</a>
                    <a href="logout.php" class="dropdown-item logout">🚪 Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero">
        <div class="orb"></div>
        <h1 class="hero-title">FUTURE COMMERCE</h1>
        <p class="hero-subtitle">Experience Shopping Reimagined</p>
        
        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" placeholder="Search for products...">
            <button class="search-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
        </div>
    </section>

    <section class="products-section">
        <h2 class="section-title">Featured Products</h2>
        
        <div class="products-grid">
            <?php 
            $delay = 0.1;
            while($p = mysqli_fetch_assoc($products)): 
                $imagePath = "assets/" . $p['image'];
            ?>
                <div class="product-card" style="animation-delay: <?php echo $delay; ?>s;">
                    <div class="product-image-wrapper">
                        <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="<?php echo htmlspecialchars($p['name']); ?>" 
                             class="product-image"
                             onerror="this.src='https://via.placeholder.com/300x300/1a1a2e/7c3aed?text=<?php echo urlencode($p['name']); ?>'">
                        <form method="POST" action="add_to_cart.php" style="margin: 0;">
                            <input type="hidden" name="name" value="<?php echo htmlspecialchars($p['name']); ?>">
                            <input type="hidden" name="price" value="<?php echo $p['price']; ?>">
                            <button type="submit" class="quick-add-overlay" style="border: none; width: 100%;">
                                QUICK ADD TO CART
                            </button>
                        </form>
                    </div>
                    <div class="product-info">
                        <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div class="product-price">₹<?php echo number_format($p['price'], 2); ?></div>
                    </div>
                </div>
            <?php 
                $delay += 0.1;
            endwhile; 
            ?>
        </div>
    </section>

    <script>
        const cursor = document.querySelector('.cursor');
        const hoverElements = document.querySelectorAll('a, button, .product-card, input, .profile-trigger');

        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX - 20 + 'px';
            cursor.style.top = e.clientY - 20 + 'px';
        });

        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const productCards = document.querySelectorAll('.product-card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            productCards.forEach(card => {
                const productName = card.querySelector('.product-name').textContent.toLowerCase();
                
                if (productName.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.5s ease';
                } else {
                    card.style.display = 'none';
                }
            });

            // If search is empty, show all products
            if (searchTerm === '') {
                productCards.forEach(card => {
                    card.style.display = 'block';
                });
            }
        });

        // Search button click
        document.querySelector('.search-btn').addEventListener('click', function() {
            const searchTerm = searchInput.value.toLowerCase();
            if (searchTerm) {
                // Scroll to products section
                document.querySelector('.products-section').scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });

        // Enter key to search
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('.search-btn').click();
            }
        });
    </script>
</body>
</html>