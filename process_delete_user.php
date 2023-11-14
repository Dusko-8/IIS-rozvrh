<?php
session_start();

require 'db_connect.php';

// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login_page.php');
    exit;
}

// Check if the user role is not Admin
if ($_SESSION['user_role'] !== 'Admin') {
    header('Location: main_page.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Prepare a delete statement
        $stmt = $pdo->prepare("DELETE FROM USERS WHERE user_ID = :id");
        
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            header("Location: your_page.php?message=User+Deleted");
        } else {
            header("Location: your_page.php?message=Delete+Failed");
        }
    }
}
?>
