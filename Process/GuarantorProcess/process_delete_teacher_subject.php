<?php
session_start();
require '../../Database/db_connect.php';

// Check if the user is logged in and is an Admin or Guarantor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Guarantor')) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the required parameter is present in the POST request
if (!isset($_POST['sub_teach_id'])) {
    echo json_encode(["error" => "Missing parameter"]);
    exit;
}

header('Content-Type: application/json');

$subTeachId = $_POST['sub_teach_id'];

// Delete the subject teacher relationship from the database
$stmt = $pdo->prepare("DELETE FROM SUBJECT_TEACHERS WHERE sub_teach_ID = ?");
$stmt->execute([$subTeachId]);

// Check if the deletion was successful
if ($stmt->rowCount() > 0) {
    echo json_encode(["success" => "Subject teacher deleted successfully"]);
} else {
    echo json_encode(["error" => "Failed to delete subject teacher"]);
}
