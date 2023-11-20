<?php
session_start();
require '../../Database/db_connect.php';

$selectedActivity = $_GET['activity_id'];
$preferences = [];

$stmt = $pdo->prepare("SELECT r.room_name, r.room_location, dt.week_day, dt.time_range, psa.preference " .
                        "FROM  PREFERED_SLOTS_ACTIVITY AS psa " .
                        "JOIN ACTIVITY AS a ON a.activity_ID = psa.activity_ID " .
                        "JOIN DAY_TIME AS dt ON psa.day_time_ID = dt.day_time_ID " .
                        "JOIN ROOM AS r ON r.room_ID = psa.room_ID " .
                        "WHERE a.activity_ID = :id");
$stmt->execute([':id' => $selectedActivity]);
$preferences = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($preferences as $p) {
    if($p['preference'] == 'Preferuje'){
        echo '<div class="preference-box-green">';
    }else{
        echo '<div class="preference-box-red">';
    }
    
    echo $p['week_day'] . "( " . $p['time_range'] . " ) : " . $p['room_name'] . " - " . $p['room_location'];
    echo '</div>';
}
?>
<style>
    .preference-box-green {
        border: 1px solid #80ff80;
        background-color: #ccffcc;
        padding: 10px;
        margin: 10px;
        width: 200px;
        text-align: center;
        border-radius: 8px; 
    }

    .preference-box-red {
        border: 1px solid #ff8080;
        background-color: #ffcccc;
        padding: 10px;
        margin: 10px;
        width: 200px;
        text-align: center;
        border-radius: 8px;
    }
</style>