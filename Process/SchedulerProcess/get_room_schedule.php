<?php
session_start();
require '../../Database/db_connect.php';

//GET DATA
$selectedRoom = $_GET['room_id'];
$selectedActivity = $_GET['activity_id'];
$roomTimes = [];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timeSlots = ['8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];


echo '<h2>Room schedule:</h2>';

//GET ROOM TIMES
$stmt = $pdo->prepare(   "SELECT s.abbervation, dt.week_day, dt.time_range, a.activity_type, a.activity_ID, a.repetition " .
                        "FROM ROOM AS r " .
                        "JOIN ACTIVITY AS a ON r.room_ID = a.room_ID " .
                        "JOIN DAY_TIME AS dt ON dt.day_time_ID = a.day_time_ID " .
                        "JOIN SUBJECTS AS s ON s.subject_ID = a.subject_ID " . 
                        "WHERE r.room_ID = :room_id");
$stmt->execute([':room_id' => $selectedRoom]);
$roomTimes = $stmt->fetchAll(PDO::FETCH_ASSOC);

//GET ACTIVITY DURATION AND REPETITION
$stmt = $pdo->prepare(  "SELECT a.duration, a.repetition FROM ACTIVITY AS a WHERE a.activity_ID = :id");
$stmt->execute([':id' => $selectedActivity]);
$result = $stmt->fetch(PDO::FETCH_ASSOC); 
$duration = $result['duration'];
$repetition = $result['repetition'];


//TABLE WHERE BUTTONS FOR ACTIVITY TIME AND ROOM SET WILL BE PLACED
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
foreach ($daysOfWeek as $day) {
    echo '<tr>';
    echo '<th>' . $day . '</th>';
    foreach ($timeSlots as $index => $timeSlot) {
        $cellContent[$index] = "";
        foreach($roomTimes as $rt){
            $subject = $rt['abbervation'];
            $weekDay = $rt['week_day'];
            $time = $rt['time_range'];
            $type = $rt['activity_type'];
            
            
            if (isTimeRangeFitting($time, $timeSlot, getNextTimeSlot($timeSlot)) && $weekDay == $day) {
                if($rt['activity_ID'] == $selectedActivity){
                    $cellContent[$index] = $cellContent[$index] . "(this): ";
                }
                $cellContent[$index] = $cellContent[$index] . $subject . '-' . $rt['repetition'] . "<br>";
            }
        }
    }
    //PLACE IN CONTENT OR A BUTTON FOR ACTIVITY TIME AND ROOM SET
    foreach($timeSlots as $index => $timeSlot){
        echo '<td>';
        if($index + $duration < 10){
            if( (isCellEmpty($cellContent[$index]) && isCellEmpty($cellContent[$index + $duration - 1])) ||
                (cellAreOpositeWeeks($repetition, $cellContent[$index + $duration - 1]))){
                echo '<button class="cell-button" onclick="addActivity(\'' .$day. '\', \'' .$timeSlot. '\', \'' .$selectedRoom. '\', \'' .$duration. '\', \'' .$selectedActivity. '\')">Add Here</button>';
            }
        }
        if($cellContent[$index] != ""){
            echo $cellContent[$index];
        }
    }
    echo '</td>';
    }
    echo '</tr>';
echo '</tbody>';


//CHECKS IF TABLE CELL IS EMPTY OR IS FILLED WITH CURRENT ACTIVITY
function isCellEmpty($cell){
    if($cell == "" || stripos($cell, 'this') !== false) return true;
    return false;
}

//CHECKS IF CURRENTLY SELECTED ACTIVITY IS OPOSITE WEEK FROM THE ACTIVITY INSIDE TABLE
function cellAreOpositeWeeks($repetition, $checkCell){
    if($repetition == 'oddWeek'){
        if(stripos($checkCell, 'evenWeek') !== false){
            return true;
        }
        return false;
    }else if($repetition == 'evenWeek'){
        if(stripos($checkCell, 'oddWeek') !== false){
            return true;
        }
        return false;
    }
    return false;
}

//CHECKS IF ACTIVITY FITS INTO TABLE
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

//RETURNS NEXT TIME SLOT
function getNextTimeSlot($timeSlot) {
    $currentTime = new DateTime($timeSlot);
    $currentTime->add(new DateInterval('PT1H'));
    return $currentTime->format('H:i');
}
?>
