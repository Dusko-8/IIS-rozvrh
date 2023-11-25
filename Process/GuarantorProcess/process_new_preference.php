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
    // Assuming you have sanitized the input data for security
    $selectedActID = $_POST['activity_ID'];
    $roomID = $_POST['room_location'];
    $teacherID = $_POST['username'];
    $weekDay = $_POST['week_day'];
    $startTime = $_POST['start_hour'] . ':00';
    $endTime = $_POST['end_hour'] . ':00';
    $preference = $_POST['preference'];
    $timeRange = $startTime . '-' . $endTime;


    if (empty($selectedActID) || !filter_var($selectedActID, FILTER_VALIDATE_INT) || isValidActivityID($selectedActID, $pdo)) {
        echo json_encode(["error" => "Invalid activity slot ID"]);
        exit;
    }

    if (empty($roomID) || !filter_var($roomID, FILTER_VALIDATE_INT) || !isValidRoomID($roomID, $pdo)) {
        echo json_encode(["error" => "Invalid room ID"]);
        exit;
    }

    if (empty($teacherID) || !filter_var($teacherID, FILTER_VALIDATE_INT) || !isValidTeacherID($teacherID, $pdo)) {
        echo json_encode(["error" => "Invalid teacher ID"]);
        exit;
    }

    if (empty($weekDay) || !isValidWeekDay($weekDay)) {
        echo json_encode(["error" => "Invalid week day"]);
        exit;
    }

    if (empty($startTime) || empty($endTime) || !isValidTimeRange($startTime, $endTime)) {
        echo json_encode(["error" => "Invalid time range"]);
        exit;
    }

    if (empty($preference) || !isValidPreference($preference)) {
        echo json_encode(["error" => "Invalid preference value"]);
        exit;
    }

    try {
        // Check if day_time record exists, create if not
        $stmtCheckDayTime = $pdo->prepare("SELECT day_time_ID FROM DAY_TIME WHERE week_day = :weekDay AND time_range = :timeRange");
        $stmtCheckDayTime->bindParam(':weekDay', $weekDay, PDO::PARAM_STR);
        $stmtCheckDayTime->bindParam(':timeRange', $timeRange, PDO::PARAM_STR);
        $stmtCheckDayTime->execute();
        $dayTimeID = $stmtCheckDayTime->fetchColumn();

        if (!$dayTimeID) {
            // Day_time record doesn't exist, create it
            $stmtCreateDayTime = $pdo->prepare("INSERT INTO DAY_TIME (week_day, time_range) VALUES (:weekDay, :timeRange)");
            $stmtCreateDayTime->bindParam(':weekDay', $weekDay, PDO::PARAM_STR);
            $stmtCreateDayTime->bindParam(':timeRange', $timeRange, PDO::PARAM_STR);
            $stmtCreateDayTime->execute();

            // Get the newly created day_time_ID
            $dayTimeID = $pdo->lastInsertId();
        }

        // Insert a new record into the PREFERED_SLOTS_ACTIVITY table
        $stmt = $pdo->prepare("INSERT INTO PREFERED_SLOTS_ACTIVITY (activity_ID, room_ID, teacher_ID, day_time_ID, preference)
                                VALUES (:selectedActID, :roomID, :teacherID, :dayTimeID, :preference)");

        $stmt->bindParam(':selectedActID', $selectedActID, PDO::PARAM_INT);
        $stmt->bindParam(':roomID', $roomID, PDO::PARAM_INT);
        $stmt->bindParam(':teacherID', $teacherID, PDO::PARAM_INT);
        $stmt->bindParam(':dayTimeID', $dayTimeID, PDO::PARAM_INT);
        $stmt->bindParam(':preference', $preference, PDO::PARAM_STR);

        $stmt->execute();

        echo json_encode(["success" => "New preference added successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(["error" => "Error adding new preference: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

function isValidRoomID($roomID, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ROOM WHERE room_ID = ?");
    $stmt->execute([$roomID]);
    return $stmt->fetchColumn() > 0;
}

function isValidTeacherID($teacherID, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE user_ID = ? AND user_role IN ('Guarantor', 'Teacher')");
    $stmt->execute([$teacherID]);
    return $stmt->fetchColumn() > 0;
}

function isValidWeekDay($weekDay)
{
    $validWeekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    return in_array($weekDay, $validWeekDays);
}

function isValidTimeRange($startTime, $endTime)
{
    if (!isValidTimeFormat($startTime) || !isValidTimeFormat($endTime)) {
        return false;
    }

    if ($startTime < '08:00' || $startTime > '17:00') {
        return false;
    }

    if ($endTime < '08:00' || $endTime > '17:00' || strtotime($endTime) <= strtotime($startTime)) {
        return false;
    }

    return true;
}

function isValidTimeFormat($time)
{
    $pattern = '/^(0[0-9]|1[0-9]|2[0-3]):00$/';
    return preg_match($pattern, $time);
}

function isValidPreference($preference)
{
    $validPreferences = ['Prefers', 'Disprefers'];
    return in_array($preference, $validPreferences);
}

function isValidActivityID($selectedActID, $pdo)
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM activity WHERE activity_ID = ?");
    $stmt->execute([$selectedActID]);
    return $stmt->fetchColumn() == 0;
}