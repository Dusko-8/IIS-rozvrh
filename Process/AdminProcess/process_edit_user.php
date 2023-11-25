<?php
session_start();
require '../../Database/db_connect.php';

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

    // Fetch current username
    $stmt = $pdo->prepare("SELECT username FROM USERS WHERE user_ID = ?");
    $stmt->execute([$userId]);
    $currentUsername = $stmt->fetchColumn();

    // Check if the new username already exists, excluding the current user
    if (strcasecmp($username, $currentUsername) !== 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE username = ? AND user_ID != ?");
        $stmt->execute([$username, $userId]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(["error" => "Username already exists. Please choose another."]);
            exit;
        }
    }
    
    if (strlen($username) > 50) {
        echo json_encode(["error" => "Username must be 50 characters or fewer"]);
        exit;
    }

    // Validate email
    if (strlen($email) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "Invalid email format or too long"]);
        exit;
    }
    // Validate password if provided
    if (!empty($password) && (strlen($password) > 255 || !isValidPassword($password))) {
        echo json_encode(["error" => "Invalid password. Ensure it is between 5 and 255 characters, includes a number, and a capital letter."]);
        exit;
    }
    
    $validRoles = ['Teacher', 'Scheduler', 'Guarantor', 'Student', 'Admin'];
    if (!in_array($userRole, $validRoles)) {
        echo json_encode(["error" => "Invalid user role"]);
        exit;
    }

    // Update the user data in the database
    try {
        $pdo->beginTransaction();

        $updateQuery = "UPDATE USERS SET username = :username, email = :email, user_role = :user_role WHERE user_ID = :userId";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute(['username' => $username, 'email' => $email, 'user_role' => $userRole, 'userId' => $userId]);

        if ($stmt->errorInfo()[0] != '00000') {
            echo json_encode(["error" => "SQL error: " . implode(", ", $stmt->errorInfo())]);
            $pdo->rollBack();
            exit;
        }

        // If password is provided, update it as well
        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE USERS SET hashed_password = :password WHERE user_ID = :userId");
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

function isValidPassword($password) {
    return strlen($password) >= 5 && preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password);
}
?>