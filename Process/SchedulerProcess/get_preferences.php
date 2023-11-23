<?php
session_start();
require '../../Database/db_connect.php';

$selectedActivity = $_GET['activity_id'];
$preferences = [];
$allRooms = [];

$stmt = $pdo->prepare("SELECT r.room_name, r.room_location, dt.week_day, dt.time_range, psa.preference " .
                        "FROM  PREFERED_SLOTS_ACTIVITY AS psa " .
                        "JOIN ACTIVITY AS a ON a.activity_ID = psa.activity_ID " .
                        "JOIN DAY_TIME AS dt ON psa.day_time_ID = dt.day_time_ID " .
                        "JOIN ROOM AS r ON r.room_ID = psa.room_ID " .
                        "WHERE a.activity_ID = :id");
$stmt->execute([':id' => $selectedActivity]);
$preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM ROOM");
$stmt->execute();
$allRooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT repetition, activity_date FROM ACTIVITY WHERE activity_id = :id");
$stmt->execute([':id' => $selectedActivity]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$repetition = $result['repetition'];

if($repetition == 'oneTime'){
    $isOneTime = true;
}else{
    $isOneTime = false;
}
if(isset($result['activity_date'])){
    $date = '-' . $result['activity_date'];
}else{
    $date = "";
}

echo '<h2>Preferences:</h2>';
echo '<div style="text-align: center;">';
foreach ($preferences as $p) {
    $boxClass = ($p['preference'] == 'Prefers') ? 'preference-box-green' : 'preference-box-red';

    echo '<div style="display: inline-block; margin: 10px;" class="' . $boxClass . '">';
    echo $p['week_day'] . "( " . $p['time_range'] . " ) : " . $p['room_name'] . " - " . $p['room_location'];
    echo '</div>';
}
echo '</div>';
$stmt = $pdo->prepare("SELECT r.room_name FROM ACTIVITY AS a JOIN ROOM as r ON r.room_ID = a.room_ID WHERE a.activity_ID = :id");
$stmt->execute([':id' => $selectedActivity]);
if($stmt->rowCount() > 0){
    $currentRoom = $stmt->fetch(PDO::FETCH_ASSOC);
    echo '<h2>Form (currently: ' . $currentRoom['room_name'] . $date . ') :</h2>';
}
else{
    echo '<h2>Form:</h2>';
}

echo '<form id="roomForm">';
echo '<label>Rooms:</label>';
if($isOneTime){
    echo '<select name="rooms" id="rooms" required onchange="loadRoomSchedule(\'../../Process/SchedulerProcess/get_time_select.php\')">';
}else{
    echo '<select name="rooms" id="rooms" required onchange="loadRoomSchedule(\'../../Process/SchedulerProcess/get_room_schedule.php\')">';
}

echo '<option value="" disabled selected>Select room</option>';
foreach($allRooms as $aR){
    echo '<option value="' . $aR['room_ID'] . '">' . $aR['room_name'] . ' (' . $aR['room_location'] . ' ) </option>';
}
echo '</select>';
echo '</form>';

echo '<div id="room_schedule"></div>';

?>