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
    $title = $_POST['title'];
    $abbervation = $_POST['abbervation'];
    $credits = $_POST['credits'];
    $subj_description = $_POST['description'];
    $guarantor_ID = $_POST['guarantor_ID'];

    // Ensure credits is a number
    $credits = filter_var($credits, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    if (!$credits) {
        echo json_encode(["error" => "Credits must be a positive integer greater than zero"]);
        exit;
    }

    $guarantor_ID = isset($_POST['guarantor_ID']) && $_POST['guarantor_ID'] !== '' ? $_POST['guarantor_ID'] : null;
    // Ensure guarantor_ID is either an integer or null
    if ($guarantor_ID !== null) {
        $guarantor_ID = filter_var($guarantor_ID, FILTER_VALIDATE_INT);
        if (!$guarantor_ID || !isValidGuarantor($guarantor_ID, $pdo)) {
            echo json_encode(["error" => "Invalid guarantor selected"]);
            exit;
        }
    }
    if (strlen($title) > 50) { // Adjust the length as per your requirements
        echo json_encode(["error" => "Invalid title format max 50 characters"]);
        exit;
    }
    if (!preg_match('/^[A-Z]{3,4}$/', $abbervation)) {
        echo json_encode(["error" => "Abbreviation must be 3 or 4 uppercase letters"]);
        exit;
    }
    if (strlen($subj_description) > 500) {
        echo json_encode(["error" => "Description must be 500 characters or fewer"]);
        exit;
    }
    // Cast credits to an integer to ensure it matches the database type
    $credits = (int) $credits;

    // Validate that no input is empty
    if (empty($title) || empty($abbervation) || empty($credits)) {
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    // Validate subject title
    if (!isValidSubjectTitle($title, $pdo)) {
        echo json_encode(["error" => "Subject title already exists. Please choose another."]);
        exit;
    }

    // Insert the new subject data into the database
    try {
        $pdo->beginTransaction();

        $insertQuery = "INSERT INTO SUBJECTS (title, abbervation, credits, subj_description, guarantor_ID) VALUES (:title, :abbervation, :credits, :subj_description, :guarantor_ID)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute(['title' => $title, 'abbervation' => $abbervation, 'credits' => $credits, 'subj_description' => $subj_description, 'guarantor_ID' => $guarantor_ID]);
    

        if ($stmt->errorInfo()[0] != '00000') {
            echo json_encode(["error" => "SQL error: " . implode(", ", $stmt->errorInfo())]);
            $pdo->rollBack();
            exit;
        }

        $pdo->commit();
        $_SESSION['alert_success'] = "Subject added successfully";
        echo json_encode(["success" => "Subject added successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => "Failed to add subject: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

// Function to validate subject title
function isValidSubjectTitle($title, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM SUBJECTS WHERE title = ?");
    $stmt->execute([$title]);
    return $stmt->fetchColumn() == 0;
}

function isValidGuarantor($guarantor_ID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE user_ID = ? AND user_role = 'Guarantor'");
    $stmt->execute([$guarantor_ID]);
    return $stmt->fetchColumn() > 0;
}
?>
