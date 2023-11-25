<?php
session_start();

require '../../Database/db_connect.php';

// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../Pages/login_page.php');
    exit;
}

// Check if the user role is not Admin
if ($_SESSION['user_role'] !== 'Admin') {
    header('Location: ../../Pages/User/anotations_page.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Prepare a delete statement
        $stmt = $pdo->prepare("DELETE FROM USERS WHERE user_ID = :id");
        
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $_SESSION['alert_success'] = "User deleted successfully";
        } else {
            $_SESSION['alert_failure'] = "Faild to delete user, try again.";
        }

        header("Location: ../../Pages/Admin/manage_users_page.php");
    }
}
?>
