<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'db.php';

$token = $_GET['token'] ?? '';

$stmt = $conn->prepare(
    "SELECT id FROM users 
     WHERE reset_token=? AND reset_expires > NOW()"
);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired reset link");
}

$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users 
         SET password=?, reset_token=NULL, reset_expires=NULL 
         WHERE id=?"
    );
    $stmt->bind_param("si", $hash, $user['id']);
    $stmt->execute();

    echo "Password updated successfully. <a href='login.html'>Login</a>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<style>
body {
    font-family: Arial;
    background: linear-gradient(135deg,#667eea,#764ba2);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.box {
    background:#fff;
    padding:30px;
    border-radius:10px;
    width:350px;
}
input, button {
    width:100%;
    padding:12px;
    margin-top:10px;
}
button {
    background:#667eea;
    color:#fff;
    border:none;
}
</style>
</head>
<body>
<div class="box">
    <h2>Reset Password</h2>
    <form method="POST">
        <input type="password" name="password" placeholder="New password" required>
        <button type="submit">Update Password</button>
    </form>
</div>
</body>
</html>
