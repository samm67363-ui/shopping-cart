<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = mysqli_prepare($conn, "SELECT id, NAME, PASSWORD FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['PASSWORD'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['NAME'];
    $_SESSION['user_email'] = $email;

    // FIXED: Removed the <a href> tag - just use plain redirect
    header("Location: home.php");
    exit;
} else {
    die("Invalid email or password");
}
?>