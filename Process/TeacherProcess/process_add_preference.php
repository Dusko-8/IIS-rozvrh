<?php
session_start();
require '../../Database/db_connect.php';

$userID = $_SESSION['user_ID'];
$day = $_POST['workdays'];
$startTime = $_POST['hours'];
$duration = $_POST['slider'];
$preference = $_POST['pref'];
$startTime = $startTime .':00';

$endTime = date('H:i', strtotime($startTime . '+' . $duration . ' hours'));
$timeRange = $startTime . '-' . $endTime;

$stmt = $pdo->prepare("SELECT pft.preference, dt.week_day, dt.time_range, dt.day_time_ID FROM PREFERED_SLOTS_TEACHER AS pft NATURAL JOIN DAY_TIME AS dt WHERE pft.user_ID = :id AND dt.week_day = :day");
$stmt->execute([
    ':id' => $userID,
    ':day' => $day,
]);
$timesToCheck = $stmt->fetchAll(PDO::FETCH_ASSOC);
$update = false;
foreach($timesToCheck as $time){
    if($preference == $time['preference']){
        if(rangesOverlap($time['time_range'], $timeRange, true)){
            $update = true;
            $dtID = $time['day_time_ID'];
            $updateTime = $time['time_range'];
        }
    }else{
        if(rangesOverlap($time['time_range'], $timeRange, false)){
            
            $_SESSION['error'] = 'You can not prefer and disprefer the same time. Please pick one.';
            header('Location: ../../Pages/Teacher/teacher_main.php');
            exit;
        }
    }
}

if($update == true){
    $stmt = $pdo->prepare("UPDATE DAY_TIME SET time_range = :newRange WHERE day_time_ID = :id");
    $stmt->execute([
        ':newRange' => updatedRange($updateTime, $timeRange),
        ':id' => $dtID,
    ]);

    $_SESSION['success'] = 'Preference was updated successfully.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}else{
    $stmt = $pdo->prepare("INSERT INTO DAY_TIME (week_day, time_range) VALUES (:day, :timeRange)");
    $stmt->execute([
        ':day' => $day,
        ':timeRange' => $timeRange,
    ]);
    $dayTimeID = $pdo->lastInsertId();
}

$stmt = $pdo->prepare("INSERT INTO PREFERED_SLOTS_TEACHER (user_ID, day_time_ID, preference) VALUES (:user, :dt, :pref)");
$stmt->execute([
    ':user' => $userID,
    ':dt' => $dayTimeID,
    ':pref' => $preference,
]);

$_SESSION['success'] = 'Preference was created successfully.';
header('Location: ../../Pages/Teacher/teacher_main.php');
exit;

function updatedRange($dbRange, $userRange) {
    list($dbStart, $dbEnd) = explode('-', $dbRange);
    list($userStart, $userEnd) = explode('-', $userRange);

    $dbStartTime = strtotime($dbStart);
    $dbEndTime = strtotime($dbEnd);
    $userStartTime = strtotime($userStart);
    $userEndTime = strtotime($userEnd);

    $updatedStart = ($dbStartTime < $userStartTime) ? $dbStart : $userStart;
    $updatedEnd = ($dbEndTime > $userEndTime) ? $dbEnd : $userEnd;

    $updatedRange = $updatedStart . '-' . $updatedEnd;

    return $updatedRange;
}

function rangesOverlap($dbRange, $userRange, $include) {
    list($start1, $end1) = explode('-', $dbRange);
    list($start2, $end2) = explode('-', $userRange);

    $startTime1 = strtotime($start1);
    $endTime1 = strtotime($end1);
    $startTime2 = strtotime($start2);
    $endTime2 = strtotime($end2);
    if($include == true){
        return ($startTime1 <= $endTime2 && $endTime1 >= $startTime2);
    }else{
        return ($startTime1 < $endTime2 && $endTime1 > $startTime2);
    }
}
?>