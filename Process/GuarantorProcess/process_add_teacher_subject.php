<?php
session_start();
require '../../Database/db_connect.php';

// Check if the user is logged in and is an Admin or Guarantor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Guarantor')) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the required parameters are present in the POST request
if (!isset($_POST['subject_id']) || !isset($_POST['teacher_id'])) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

header('Content-Type: application/json');

$subjectID = $_POST['subject_id'];
$teacherID = $_POST['teacher_id'];

if ($teacherID !== null) {
    $teacherID = filter_var($teacherID, FILTER_VALIDATE_INT);
    if (!$teacherID || !isValidTeacher($teacherID, $pdo)) {
        echo json_encode(["error" => "Invalid teacher selected"]);
        exit;
    }
}

if (empty($subjectID) || !isValidSubject($subjectID, $pdo)) {
    echo json_encode(["error" => "Invalid subject selected"]);
    exit;
}

// Insert teacher-subject into the database
$stmt = $pdo->prepare("INSERT INTO SUBJECT_TEACHERS (user_ID, subject_ID) VALUES (?, ?)");
$stmt->execute([$teacherID, $subjectID]);

// Check if the insertion was successful
if ($stmt->rowCount() > 0) {
    echo json_encode(["success" => "Teacher added to the subject successfully"]);
} else {
    echo json_encode(["error" => "Failed to add teacher to the subject"]);
}

function isValidTeacher($teacherID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE user_ID = ? AND user_role IN ('Guarantor', 'Teacher')");
    $stmt->execute([$teacherID]);
    return $stmt->fetchColumn() > 0;
}

function isValidSubject($subjectID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM SUBJECTS WHERE subject_ID = ?");
    $stmt->execute([$subjectID]);
    return $stmt->fetchColumn() > 0;
}
?>