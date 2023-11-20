<?php
session_start();
require '../Database/db_connect.php';

header('Content-Type: application/json');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subjectId = $_POST['subjectId'];
    $title = $_POST['title'];
    $abbervation = $_POST['abbervation'];
    $credits = $_POST['credits'];
    $subj_description = $_POST['subj_description'];
    $guarantor_ID = $_POST['guarantor_ID'];

    // Ensure guarantor_ID is an integer and exists in the USERS table
    $guarantor_ID = isset($_POST['guarantor_ID']) && $_POST['guarantor_ID'] !== '' ? $_POST['guarantor_ID'] : null;
    // Ensure guarantor_ID is either an integer or null
    if ($guarantor_ID !== null) {
        $guarantor_ID = filter_var($guarantor_ID, FILTER_VALIDATE_INT);
        if (!$guarantor_ID || !isValidGuarantor($guarantor_ID, $pdo)) {
            echo json_encode(["error" => "Invalid guarantor selected"]);
            exit;
        }
    }
    // Ensure credits is a number
    $credits = filter_var($credits, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    if (!$credits) {
        echo json_encode(["error" => "Credits must be a positive integer greater than zero"]);
        exit;
    }
    // Cast credits to an integer to ensure it matches the database type
    $credits = (int) $credits;

    // Validate that no input is empty
    if (empty($subjectId) || empty($title) || empty($abbervation) || empty($credits) || empty($subj_description)) {
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }
    
    // Validate subject title and abbervation
    if (!isValidSubjectTitle($subjectId, $title, $pdo) ) {
        echo json_encode(["error" => "Subject title already exists. Please choose another."]);
        exit;
    }

    // Update the subject data in the database
    try {
        $pdo->beginTransaction();
        $updateQuery = "UPDATE SUBJECTS SET title = :title, abbervation = :abbervation, credits = :credits, subj_description = :subj_description, guarantor_ID = :guarantor_ID WHERE subject_ID = :subjectId";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute(['title' => $title, 'abbervation' => $abbervation, 'credits' => $credits, 'subj_description' => $subj_description, 'guarantor_ID' => $guarantor_ID, 'subjectId' => $subjectId]);

        if ($stmt->errorInfo()[0] != '00000') {
            echo json_encode(["error" => "SQL error: " . implode(", ", $stmt->errorInfo())]);
            $pdo->rollBack();
            exit;
        }

        $pdo->commit();
        echo json_encode(["success" => "Subject updated successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => "Failed to update subject: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

// Function to validate subject title
function isValidSubjectTitle($subjectId, $title, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM SUBJECTS WHERE title = ? AND subject_ID != ?");
    $stmt->execute([$title, $subjectId]);
    return $stmt->fetchColumn() == 0;
}

function isValidGuarantor($guarantor_ID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE user_ID = ? AND user_role = 'Guarantor'");
    $stmt->execute([$guarantor_ID]);
    return $stmt->fetchColumn() > 0;
}

?>
