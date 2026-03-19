<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass  = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($pass)) {
        die("All fields are required");
    }

    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

    $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email=?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        die("Email already exists");
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO users (name,email,password) VALUES (?,?,?)");
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name']  = $name;
        header("Location: home.php");
        exit;
    } else {
        echo "Registration failed";
    }
}
?>
