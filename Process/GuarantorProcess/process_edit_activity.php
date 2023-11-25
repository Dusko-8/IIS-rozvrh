<?php
session_start();
require '../../Database/db_connect.php';

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin'&& $_SESSION['user_role'] !== 'Guarantor') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assuming you have sanitized the input data for security
    $activityID = $_POST['activity_ID'];
    $activityType = $_POST['activity_type'];
    $teacherID = $_POST['username'];
    $repetition = $_POST['repetition'];
    $duration = $_POST['duration'];

    try {
        // Update the ACTIVITY table
        $stmt = $pdo->prepare("UPDATE ACTIVITY
                                SET activity_type = :activityType,
                                    teacher_ID = :teacherID,
                                    repetition = :repetition,
                                    duration = :duration
                                WHERE activity_ID = :activityID");

        $stmt->bindParam(':activityType', $activityType, PDO::PARAM_STR);
        $stmt->bindParam(':teacherID', $teacherID, PDO::PARAM_INT);
        $stmt->bindParam(':repetition', $repetition, PDO::PARAM_STR);
        $stmt->bindParam(':activityID', $activityID, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);

        $stmt->execute();

        echo json_encode(["success" => "Changes saved successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error saving changes: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}
?>