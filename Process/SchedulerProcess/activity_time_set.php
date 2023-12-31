<?php
session_start();
require '../../Database/db_connect.php';

//CHECK PRE PROCESS
if ($_SERVER["REQUEST_METHOD"] != "GET") {
    $_SESSION['alert_error'] = 'GET request required.';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

if($_SESSION['user_role'] != 'Scheduler'  && $_SESSION['user_role'] != 'Admin'){
    $_SESSION['alert_error'] = 'You don\'t have rights to modify data. Please log in with Scheduler account.';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

if(!isset($_GET['day'], $_GET['time'], $_GET['room'], $_GET['duration'], $_GET['activity'])){
    $_SESSION['alert_error'] = 'Data not set correctly.';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

//SET DATA
$day = $_GET['day'];
$time = $_GET['time'];
$roomID = $_GET['room'];
$duration = $_GET['duration'];
$activityID = $_GET['activity'];

//CHECK DATA
$roomID = filter_var($roomID, FILTER_VALIDATE_INT);
$activityID = filter_var($activityID, FILTER_VALIDATE_INT);
$duration = filter_var($duration, FILTER_VALIDATE_INT);
if(!$roomID || !$activityID || !$duration){
    $_SESSION['alert_error'] = 'Invalid data.1';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

$validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
if(!in_array($day, $validDays)){
    $_SESSION['alert_error'] = 'Invalid data.2';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

$pattern = '/^([0-9]|1[0-7]):[0-5][0-9]$/';
if(!preg_match($pattern, $time)){
    $_SESSION['alert_error'] = 'Invalid data.';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

$endTime = date('H:i', strtotime($time . '+' . $duration . ' hours'));
$timeRange = $time . '-' . $endTime;

$stmt = $pdo->prepare("SELECT day_time_ID FROM ACTIVITY WHERE activity_ID = :id");
$stmt->execute([':id' => $activityID]);
if($stmt->rowCount() > 0){
    //if activity has time and room => it gets updated
    $timeID = $stmt->fetch(PDO::FETCH_ASSOC);
    $timeID = $timeID['day_time_ID'];
    $stmt = $pdo->prepare("UPDATE DAY_TIME SET week_day = :day, time_range = :time WHERE day_time_ID = :id");
    $stmt->execute([
        ':day' => $day,
        ':time' => $timeRange,
        ':id' => $timeID,
    ]);

    if($stmt->rowCount() == 0){
        $_SESSION['alert_error'] = 'There is nothing to update. Please select new time.';
        header('Location: ../../Pages/Scheduler/scheduler_main.php');
        exit;
    }

    $dayTimeID = $timeID;
}else{
    //if activity doesnt have time and room => new time gets created for this activity
    $stmt = $pdo->prepare("INSERT INTO DAY_TIME (week_day, time_range) VALUES (:day, :timeRange)");
    $stmt->execute([
        ':day' => $day,
        ':timeRange' => $timeRange,
    ]);

    if($stmt->rowCount() == 0){
        $_SESSION['alert_error'] = 'An error occurred while processing your request. Please try again.' . $timeRange;
        header('Location: ../../Pages/Scheduler/scheduler_main.php');
        exit;
    }

    $dayTimeID = $pdo->lastInsertId();
}

//SET <<FK>> FOR ACTIVITY
$stmt = $pdo->prepare("UPDATE ACTIVITY SET day_time_id = :timeID, room_ID = :roomID WHERE activity_ID = :activityID");
$result = $stmt->execute([
    ':timeID' => $dayTimeID,
    ':roomID' => $roomID,
    ':activityID' => $activityID,
]);

//check if data was changed
if($stmt->rowCount() == 0 && $result == false){
    $_SESSION['alert_error'] = 'An error occurred while processing your request. Please try again.';
    header('Location: ../../Pages/Scheduler/scheduler_main.php');
    exit;
}

//return success
$_SESSION['alert_success'] = 'Successfully changed activity time and room';
header('Location: ../../Pages/Scheduler/scheduler_main.php');
exit;
?>