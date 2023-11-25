<?php
session_start();
require '../../Database/db_connect.php';

// Check if the user is logged in and is an Admin or Guarantor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Guarantor')) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the required parameter is present in the POST request
if (!isset($_POST['activity_id'])) {
    echo json_encode(["error" => "Missing parameter"]);
    exit;
}

header('Content-Type: application/json');

$activityId = $_POST['activity_id'];

// Perform any additional validation if needed

// Delete the activity from the database
$stmt = $pdo->prepare("DELETE FROM activity WHERE activity_ID = ?");
$stmt->execute([$activityId]);

// Check if the deletion was successful
if ($stmt->rowCount() > 0) {
    echo json_encode(["success" => "Activity deleted successfully"]);
} else {
    echo json_encode(["error" => "Failed to delete activity"]);
}
?>