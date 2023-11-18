<?php
session_start();

require '../Database/db_connect.php';

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
            if($user['user_role'] == 'Student')
            {
                header('Location: ../Pages/Student/student_yearly.php');
                exit;
            }
            header('Location: ../Pages/main_page.php');
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header('Location: ../Pages/login_page.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Both fields are required!";
        header('Location: ../Pages/login_page.php');
        exit;
    }
}
?>