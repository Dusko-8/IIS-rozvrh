<?php
session_start();
require '../../Database/db_connect.php';

// Check if the user is logged in and is an Admin or Guarantor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Guarantor')) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $activityType = $_POST['activity_type'];
    $teacherID = $_POST['username'];
    $repetition = $_POST['repetition'];
    $duration = $_POST['duration'];
    $subjectID = $_POST['subject_ID'];

    if (empty($activityType) || !isValidActivityType($activityType)) {
        echo json_encode(["error" => "Invalid activity type"]);
        exit;
    }

    if ($teacherID !== null) {
        $teacherID = filter_var($teacherID, FILTER_VALIDATE_INT);
        if (!$teacherID || !isValidTeacher($teacherID, $pdo)) {
            echo json_encode(["error" => "Invalid teacher selected"]);
            exit;
        }
    }

    if (empty($repetition) || !isValidRepetition($repetition)) {
        echo json_encode(["error" => "Invalid repetition value"]);
        exit;
    }

    if (empty($duration) || !isValidDuration($duration)) {
        echo json_encode(["error" => "Invalid duration value"]);
        exit;
    }

    if (empty($subjectID) || !isValidSubject($subjectID, $pdo)) {
        echo json_encode(["error" => "Invalid subject selected"]);
        exit;
    }


    $roomID = null;
    $dayTimeID = null;
    $activityDate = null;

    try {
        // Insert into the ACTIVITY table
        $stmt = $pdo->prepare("INSERT INTO ACTIVITY (subject_ID, room_ID, teacher_ID, day_time_ID, duration, repetition, activity_date, activity_type)
                                VALUES (:subjectID, :roomID, :teacherID, :dayTimeID, :duration, :repetition, :activityDate, :activityType)");

        $stmt->bindParam(':subjectID', $subjectID, PDO::PARAM_INT);
        $stmt->bindParam(':roomID', $roomID, PDO::PARAM_INT);
        $stmt->bindParam(':teacherID', $teacherID, PDO::PARAM_INT);
        $stmt->bindParam(':dayTimeID', $dayTimeID, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':repetition', $repetition, PDO::PARAM_STR);
        $stmt->bindParam(':activityDate', $activityDate, PDO::PARAM_STR);
        $stmt->bindParam(':activityType', $activityType, PDO::PARAM_STR);

        $stmt->execute();

        echo json_encode(["success" => "Activity added successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error adding activity: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}


function isValidTeacher($teacherID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS NATURAL JOIN SUBJECT_TEACHERS WHERE user_ID = ? AND user_role IN ('Guarantor', 'Teacher')");
    $stmt->execute([$teacherID]);
    return $stmt->fetchColumn() > 0;
}

function isValidSubject($subjectID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM SUBJECTS WHERE subject_ID = ?");
    $stmt->execute([$subjectID]);
    return $stmt->fetchColumn() > 0;
}

function isValidActivityType($activityType) {
    $validActivityTypes = ['Lecture', 'Tutorial', 'Seminar', 'Exam', 'Consultation', 'Exercise', 'Demo'];
    return in_array($activityType, $validActivityTypes);
}

function isValidRepetition($repetition) {
    $validRepetitions = ['everyWeek', 'evenWeek', 'oddWeek', 'oneTime'];
    return in_array($repetition, $validRepetitions);
}

function isValidDuration($duration) {
    return filter_var($duration, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
}

?>