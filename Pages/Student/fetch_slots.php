<?php
session_start();
require '../../Database/db_connect.php';
// Fetch slots based on the selected subject (replace with your actual database logic)
$selectedSubject = $_GET['subject'];
$activities = [];
echo '<form id="addActivity" action="../../Process/StudentProcess/process_add_activity.php" method="post">';

echo '<label id="time" for="activity">' . $selectedSubject . ":" . '</label>';

$stmt = $pdo->prepare("SELECT ACTIVITY.repetition, ACTIVITY.activity_ID, ACTIVITY.activity_type, DAY_TIME.week_day, DAY_TIME.time_range " .
                        "FROM ACTIVITY " .
                        "JOIN SUBJECTS ON SUBJECTS.subject_ID = ACTIVITY.subject_ID " .
                        "JOIN DAY_TIME ON DAY_TIME.day_time_ID = ACTIVITY.day_time_ID " .
                        "WHERE SUBJECTS.abbervation = :shortcut");
$stmt->execute([':shortcut' => $selectedSubject]);
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<select id="activity" name="activity">';
echo '<option value="" disabled selected>Select time</option>';
if($activities != []){
    foreach ($activities as $activity) {
        echo '<option value="' . $activity['activity_ID'] . '">' . $activity['activity_type'] . ": " . $activity['week_day'] . $activity['time_range'] . " (" . $activity['repetition'] . ")" . '</option>';
    }
    echo '</select>';
    echo '<button type="submit">' . 'Add ' . $activity['activity_type'] . '</button>';
    echo '</form>';
}


?>