<?php
session_start();
require 'db.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Check if email exists
    $query = "SELECT id, NAME FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        // Email exists - In production, send actual reset email
        // For now, just show success message
        $message = "Password reset link has been sent to your email!";
        $messageType = "success";
        
        // TODO: Implement actual email sending with PHPMailer
        // Generate reset token, save to DB, send email
        
    } else {
        $message = "Email not registered. Please check and try again.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - BuyIt</title>
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
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: none;
            overflow: hidden;
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

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .orb {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.3) 0%, transparent 70%);
            filter: blur(80px);
            animation: orbFloat 8s ease-in-out infinite;
        }

        .orb:nth-child(1) {
            top: -200px;
            left: -200px;
        }

        .orb:nth-child(2) {
            bottom: -200px;
            right: -200px;
            animation-delay: 2s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 50px) scale(1.1); }
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 500px;
            padding: 20px;
        }

        .card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 50px 40px;
            backdrop-filter: blur(20px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 900;
            background: linear-gradient(135deg, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .title {
            font-family: 'Outfit', sans-serif;
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 10px;
            text-align: center;
            background: linear-gradient(135deg, #fff, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 40px;
            font-size: 15px;
            line-height: 1.6;
        }

        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 500;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #22c55e;
        }

        .message.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .input-group {
            position: relative;
            margin-bottom: 30px;
        }

        .input-field {
            width: 100%;
            padding: 18px 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            transition: all 0.3s;
            cursor: none;
        }

        .input-field:focus {
            outline: none;
            border-color: #7c3aed;
            background: rgba(255, 255, 255, 0.05);
            transform: scale(1.02);
        }

        .input-field:focus + .floating-label,
        .input-field:not(:placeholder-shown) + .floating-label {
            top: -10px;
            font-size: 12px;
            color: #7c3aed;
        }

        .floating-label {
            position: absolute;
            left: 20px;
            top: 18px;
            color: rgba(255, 255, 255, 0.5);
            pointer-events: none;
            transition: all 0.3s;
            background: #09090b;
            padding: 0 5px;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #7c3aed, #8b5cf6);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            cursor: none;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .submit-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.5);
        }

        .btn-text {
            position: relative;
            z-index: 1;
        }

        .links {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            font-size: 14px;
        }

        .links a {
            color: #7c3aed;
            text-decoration: none;
            cursor: none;
            transition: all 0.3s;
        }

        .links a:hover {
            color: #8b5cf6;
        }

        .icon {
            font-size: 60px;
            text-align: center;
            margin-bottom: 20px;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @media (max-width: 600px) {
            .card {
                padding: 40px 30px;
            }
            .title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="cursor"></div>

    <div class="background">
        <div class="orb"></div>
        <div class="orb"></div>
    </div>

    <div class="container">
        <div class="card">
            <div class="logo-container">
                <div class="logo-icon"></div>
                <span class="logo-text">BuyIt</span>
            </div>

            <div class="icon">🔐</div>
            
            <h1 class="title">Forgot Password?</h1>
            <p class="subtitle">No worries! Enter your email and we'll send you reset instructions.</p>

            <?php if ($message): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="forgot.php">
                <div class="input-group">
                    <input type="email" class="input-field" name="email" placeholder=" " required>
                    <label class="floating-label">Email Address</label>
                </div>

                <button type="submit" class="submit-btn">
                    <span class="btn-text">Send Reset Link</span>
                </button>
            </form>

            <div class="links">
                <a href="login.html">← Back to Login</a>
                <a href="register.html">Create Account →</a>
            </div>
        </div>
    </div>

    <script>
        const cursor = document.querySelector('.cursor');
        const hoverElements = document.querySelectorAll('a, button, input');

        document.addEventListener('mousemove', (e) => {
            cursor.style.left = e.clientX - 20 + 'px';
            cursor.style.top = e.clientY - 20 + 'px';
        });

        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('hover'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('hover'));
        });

        // Input animation
        document.querySelector('.input-field').addEventListener('focus', function() {
            this.style.transform = 'scale(1.02)';
        });
        
        document.querySelector('.input-field').addEventListener('blur', function() {
            this.style.transform = 'scale(1)';
        });

        // Auto redirect to login after success
        <?php if ($messageType === 'success'): ?>
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>