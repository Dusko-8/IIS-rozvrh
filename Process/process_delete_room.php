<?php
session_start();

require '../Database/db_connect.php';

// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../Pages/login_page.php');
    exit;
}

// Check if the user role is not Admin
if ($_SESSION['user_role'] !== 'Admin') {
    header('Location: ../Pages/main_page.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Prepare a delete statement for the room
        $stmt = $pdo->prepare("DELETE FROM ROOM WHERE room_ID = :id");
        
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            $_SESSION['alert_success'] = "Room deleted successfully";
        } else {
            $_SESSION['alert_failure'] = "Faild to delete room, try again.";
        }
        header("Location: ../Pages/manage_rooms_page.php");
    }
}
?>
