<?php
session_start();
require '../../Database/db_connect.php';

//CHECK PRE PROCESS
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['errorAlert'] = 'POST request required.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

if($_SESSION['user_role'] != 'Teacher' && $_SESSION['user_role'] != 'Guarantor' && $_SESSION['user_role'] != 'Admin'){
    $_SESSION['errorAlert'] = 'You don\'t have rights to modify data. Please log in with Teacher or Guarantor account.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

if(!isset($_SESSION['user_ID'], $_POST['workdays'], $_POST['hours'], $_POST['slider'], $_POST['pref'])){
    $_SESSION['errorAlert'] = 'Data not set correctly.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

//SET DATA
$userID = $_SESSION['user_ID'];
$day = $_POST['workdays'];
$startTime = $_POST['hours'];
$duration = $_POST['slider'];
$preference = $_POST['pref'];

//CHECK DATA
$userID = filter_var($userID, FILTER_VALIDATE_INT);
$duration = filter_var($duration, FILTER_VALIDATE_INT);
$startTime = filter_var($startTime, FILTER_VALIDATE_INT);
if(!$userID || !$duration || !$startTime){
    $_SESSION['errorAlert'] = 'Invalid data.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

$validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
if(!in_array($day, $validDays)){
    $_SESSION['errorAlert'] = 'Invalid data.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

if($preference != 'Prefers' && $preference != 'Disprefers'){
    $_SESSION['errorAlert'] = 'Invalid data.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}


//PROCESS
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
        //ERROR IF RANGES OVERLAP BUT PREFERENCES ARE DIFFERENT
        if(rangesOverlap($time['time_range'], $timeRange, false)){
            
            $_SESSION['errorAlert'] = 'You can not prefer and disprefer the same time. Please pick one.';
            header('Location: ../../Pages/Teacher/teacher_main.php');
            exit;
        }
    }
}

//UPDATE IF RANGES OVERLAP BUT PREFERENCE IS THE SAME
if($update == true){
    $stmt = $pdo->prepare("UPDATE DAY_TIME SET time_range = :newRange WHERE day_time_ID = :id");
    $stmt->execute([
        ':newRange' => updatedRange($updateTime, $timeRange),
        ':id' => $dtID,
    ]);

    if($stmt->rowCount() == 0){
        $_SESSION['errorAlert'] = 'There is nothing to be changed with this request. Try selecting different time.';
        header('Location: ../../Pages/Teacher/teacher_main.php');
        exit;
    }
    $_SESSION['success'] = 'Preference was updated successfully.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
//CREATE NEW DAY TIME IF NO RANGES OVERLAP
}else{
    $stmt = $pdo->prepare("INSERT INTO DAY_TIME (week_day, time_range) VALUES (:day, :timeRange)");
    $stmt->execute([
        ':day' => $day,
        ':timeRange' => $timeRange,
    ]);

    if($stmt->rowCount() == 0){
        $_SESSION['errorAlert'] = 'An error occurred while processing your request. Please try again.';
        header('Location: ../../Pages/Teacher/teacher_main.php');
        exit;
    }
    $dayTimeID = $pdo->lastInsertId();
}

//CREATE NEW PREFERENCE
$stmt = $pdo->prepare("INSERT INTO PREFERED_SLOTS_TEACHER (user_ID, day_time_ID, preference) VALUES (:user, :dt, :pref)");
$stmt->execute([
    ':user' => $userID,
    ':dt' => $dayTimeID,
    ':pref' => $preference,
]);

if($stmt->rowCount() == 0){
    $_SESSION['errorAlert'] = 'An error occurred while processing your request. Please try again.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

$_SESSION['success'] = 'Preference was created successfully.';
header('Location: ../../Pages/Teacher/teacher_main.php');
exit;

//RETURNS NEW RANGE => IF RANGES OVERLAP, THIS RANGE IS USED AS NEW RANGE 
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


//CHECK IF RANGES OVERLAP
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