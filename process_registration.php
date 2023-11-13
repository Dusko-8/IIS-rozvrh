<?php
session_start();

require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $user_role = $_POST['user_role'];

    try {
        $stmt = $pdo->prepare('INSERT INTO USERS (username, hashed_password, email, user_role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $password, $email, $user_role]);
        $_SESSION['message'] = "Registration successful!";
        header('Location: login_page.php');
    } catch (PDOException $e) {
        $_SESSION['error'] = "Registration failed: " . $e->getMessage();
        header('Location: register_page.php');
    }
}
?>
