<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['userId'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $userRole = $_POST['user_role'];
    $password = $_POST['password']; // Be cautious with password handling

    // Update the user data in the database
    try {
        $pdo->beginTransaction();

        $updateQuery = "UPDATE USERS SET username = :username, email = :email, user_role = :user_role WHERE user_ID = :userId";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute(['username' => $username, 'email' => $email, 'user_role' => $userRole, 'userId' => $userId]);

        // If password is provided, update it as well
        if (!empty($password)) {
            // Remember to hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE USERS SET password = :password WHERE user_ID = :userId");
            $stmt->execute(['password' => $hashedPassword, 'userId' => $userId]);
        }

        $pdo->commit();
        echo json_encode(["success" => "User updated successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => "Failed to update user: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>