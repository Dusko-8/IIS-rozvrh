<?php
session_start();
require '../../Database/db_connect.php';

$day = $_GET['day'];
$time = $_GET['time'];
$roomID = $_GET['room'];
$duration = $_GET['duration'];
$activityID = $_GET['activity'];

$endTime = date('H:i', strtotime($time . '+' . $duration . ' hours'));
$timeRange = $time . '-' . $endTime;
$stmt = $pdo->prepare("SELECT day_time_ID FROM ACTIVITY WHERE activity_ID = :id");
$stmt->execute([':id' => $activityID]);
if($stmt->rowCount() > 0){
    $timeID = $stmt->fetch(PDO::FETCH_ASSOC);
    $timeID = $timeID['day_time_ID'];
    $stmt = $pdo->prepare("UPDATE DAY_TIME SET week_day = :day, time_range = :time WHERE day_time_ID = :id");
    $stmt->execute([
        ':day' => $day,
        ':time' => $timeRange,
        ':id' => $timeID,
    ]);
    $dayTimeID = $timeID;
}else{
    $stmt = $pdo->prepare("INSERT INTO DAY_TIME (week_day, time_range) VALUES (:day, :timeRange)");
    $stmt->execute([
        ':day' => $day,
        ':timeRange' => $timeRange,
    ]);
    $dayTimeID = $pdo->lastInsertId();
}


$stmt = $pdo->prepare("UPDATE ACTIVITY SET day_time_id = :timeID, room_ID = :roomID WHERE activity_ID = :activityID");
$stmt->execute([
    ':timeID' => $dayTimeID,
    ':roomID' => $roomID,
    ':activityID' => $activityID,
]);

header('Location: ../../Pages/Scheduler/scheduler_main.php');
exit;
?>