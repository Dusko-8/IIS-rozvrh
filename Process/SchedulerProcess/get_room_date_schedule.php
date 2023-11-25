<?php
session_start();
require '../../Database/db_connect.php';

//GET DATA
$selectedRoom = $_GET['room_id'];
$selectedActivity = $_GET['activity_id'];
$selectedDate = $_GET['date'];
$timeSlots = ['8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];

$timestamp = strtotime($selectedDate);
$day = date('l', $timestamp);

//CHECK IF DAY IS A FUTERE WEEK DAY
if($day == 'Sunday' || $day == 'Saturday'){
    echo '<div class="error">Please only choose work days (Mon-Fri).</div>';
    exit;
}
if($timestamp < time()){
    echo '<div class="error">Please select a time in the future.</div>';
    exit;
}

//GET ROOM TIMES
$stmt = $pdo->prepare(   "SELECT s.abbervation, dt.week_day, dt.time_range, a.activity_type, a.activity_ID, a.repetition, a.activity_date " .
                        "FROM ROOM AS r " .
                        "JOIN ACTIVITY AS a ON r.room_ID = a.room_ID " .
                        "JOIN DAY_TIME AS dt ON dt.day_time_ID = a.day_time_ID " .
                        "JOIN SUBJECTS AS s ON s.subject_ID = a.subject_ID " . 
                        "WHERE r.room_ID = :room_id");
$stmt->execute([':room_id' => $selectedRoom]);
$roomTimes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//GET DURATION OF ACTIVITY
$stmt = $pdo->prepare(  "SELECT a.duration, a.repetition FROM ACTIVITY AS a WHERE a.activity_ID = :id");
$stmt->execute([':id' => $selectedActivity]);
$result = $stmt->fetch(PDO::FETCH_ASSOC); 
$duration = $result['duration'];

//DISPLAY TABLE OF ROOM OCUPATION
echo '<h2>Room schedule:</h2>';
echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<th></th>';

foreach ($timeSlots as $timeSlot) {
    echo '<th class="time-header">' . $timeSlot . '</th>';
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';
$cellContent = [];
echo '<tr>';
echo '<th>' . $day . '</th>';
//set content into cellContent[]...
//it gets checked later for we need to place buttons for insertion into the table where there is nothing blocking the activity
foreach ($timeSlots as $index => $timeSlot) {
    $cellContent[$index] = "";
    foreach($roomTimes as $rt){
        $subject = $rt['abbervation'];
        $weekDay = $rt['week_day'];
        $time = $rt['time_range'];
        $type = $rt['activity_type'];
        if(isset($rt['activity_date'])){
            if (date('oW', strtotime($selectedDate)) === date('oW', strtotime($rt['activity_date']))) {
                if (isTimeRangeFitting($time, $timeSlot, getNextTimeSlot($timeSlot)) && $weekDay == $day) {
                    if($rt['activity_ID'] == $selectedActivity){
                        $cellContent[$index] = $cellContent[$index] . "(this): ";
                    }
                    $cellContent[$index] = $cellContent[$index] . $subject . '-' . $rt['repetition'] . "<br>";
                }
            }
        }else{
            if (isTimeRangeFitting($time, $timeSlot, getNextTimeSlot($timeSlot)) && $weekDay == $day) {
                if($rt['activity_ID'] == $selectedActivity){
                    $cellContent[$index] = $cellContent[$index] . "(this): ";
                }
                $cellContent[$index] = $cellContent[$index] . $subject . '-' . $rt['repetition'] . "<br>";
            }
        }
    }
}
//insert cellContent or a button for insertion if possible
foreach($timeSlots as $index => $timeSlot){
    echo '<td>';
    if($index + $duration < 10){
        if(isCellEmpty($cellContent[$index]) && isCellEmpty($cellContent[$index + $duration - 1])){
            echo '<button class="cell-button" onclick="addOneTimeActivity(\'' .$day. '\', \'' .$timeSlot. '\', \'' .$selectedRoom. '\', \'' .$duration. '\', \'' .$selectedActivity. '\', \'' .$selectedDate. '\')">Add Here</button>';
        }
    }
    if($cellContent[$index] != ""){
        echo $cellContent[$index];
    }
}
echo '</td>';
echo '</tr>';
echo '</tbody>';

//checks if cell is empty or is filled with current activity
function isCellEmpty($cell){
    if($cell == "" || stripos($cell, 'this') !== false) return true;
    return false;
}

//true if activity fits into table
function isTimeRangeFitting($timeRange, $tableStartTime, $tableEndTime) {
    list($startTime, $endTime) = explode('-', $timeRange);
    $startTime = new DateTime($startTime);
    $endTime = new DateTime($endTime);
    $currentTime = clone $startTime;
    $currentDate = date("Y-m-d");

    $tableEndTime = new DateTime($tableEndTime);
    $tableEndTime->sub(new DateInterval('PT1M'));
    $tableStartTime = new DateTime($tableStartTime);

    while ($currentTime <= $endTime) {
        if ($currentTime->format('H:i') >= $tableStartTime->format('H:i') && $currentTime->format('H:i') <= $tableEndTime->format('H:i')) {
            return true; 
        }
        $currentTime->add(new DateInterval('PT1H'));
    }
    return false;
}

//returns next time slot of table
function getNextTimeSlot($timeSlot) {
    $currentTime = new DateTime($timeSlot);
    $currentTime->add(new DateInterval('PT1H'));
    return $currentTime->format('H:i');
}
?>