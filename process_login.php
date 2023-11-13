<?php
session_start();

require 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Include user_role in the SELECT statement
        $stmt = $pdo->prepare('SELECT hashed_password, user_role FROM USERS WHERE username = ?');
        $stmt->execute([$_POST['username']]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['password'], $user['hashed_password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $_POST['username'];
            $_SESSION['user_role'] = $user['user_role']; // Store the user role in the session
            header('Location: main_page.php');
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header('Location: login_page.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Both fields are required!";
        header('Location: login_page.php');
        exit;
    }
}
?>