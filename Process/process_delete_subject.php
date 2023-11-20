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
        
        // Prepare a delete statement for the subject
        $stmt = $pdo->prepare("DELETE FROM SUBJECTS WHERE subject_ID = :id");
        
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        
        // Execute the prepared statement
        if ($stmt->execute()) {
            // Redirect back to the manage subjects page with a success message
            header("Location: ../Pages/manage_subjects_page.php?deletion=success");
        } else {
            // Redirect back with a failure message
            header("Location: ../Pages/manage_subjects_page.php?deletion=failed");
        }
    }
}
?>
