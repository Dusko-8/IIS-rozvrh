<?php
session_start();
require '../../Database/db_connect.php';
$activities = [];
$activityPreference = [];

$stmt = $pdo->prepare("SELECT a.activity_ID, s.abbervation, a.activity_type FROM ACTIVITY AS a JOIN SUBJECTS AS s ON s.subject_ID = a.subject_ID");
$stmt->execute();
$activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Main Page</title>
        <link rel="stylesheet" href="../../Styles/table_style.css">
    </head>
    <body>
            <?php if (isset($_SESSION['alert_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['alert_success']; ?>
                </div>
                <script>
                    setTimeout(function () {
                        document.querySelector('.alert-success').style.display = 'none';
                    }, 5000);
                </script>
                <?php unset($_SESSION['alert_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['alert_error'])): ?>
                <div class="alert alert-error">
                    <?= $_SESSION['alert_error']; ?>
                </div>
                <script>
                    setTimeout(function () {
                        document.querySelector('.alert-error').style.display = 'none';
                    }, 5000);
                </script>
                <?php unset($_SESSION['alert_error']); ?>
            <?php endif; ?>

        <form id="schedulerForm">
            <label for='activities'>Activities:</label>
            <select name="activities" id="activities" required onchange="loadAvailableSlots()">
                <option value="" disabled selected>Select an activity</option>
                <?php foreach ($activities as $activity): ?>
                    <option value="<?php echo $activity['activity_ID']; ?>"><?php echo $activity['abbervation'] . ": " . $activity['activity_type']; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <div id="preference"></div>

        <script>
            function addOneTimeActivity(day, time, roomID, duration, activityID, date){
                var baseUrl = '../../Process/SchedulerProcess/onetime_activity_time_set.php';

                var encodedDay = encodeURIComponent(day);
                var encodedTime = encodeURIComponent(time);
                var encodedRoom = encodeURIComponent(roomID);
                var encodedDuration = encodeURIComponent(duration);
                var encodedActivity = encodeURIComponent(activityID);
                var encodedDate = encodeURIComponent(date); 
                console.log(date);
                var url = baseUrl+'?day='+encodedDay+'&time='+encodedTime+'&room='+encodedRoom+'&duration='+encodedDuration+'&activity='+encodedActivity+'&date='+encodedDate;
                window.location.href = url;
            } 
            function addActivity(day, timeSlot, roomID, duration, activityID){
                var baseUrl = '../../Process/SchedulerProcess/activity_time_set.php';

                var encodedDay = encodeURIComponent(day);
                var encodedTime = encodeURIComponent(timeSlot);
                var encodedRoom = encodeURIComponent(roomID);
                var encodedDuration = encodeURIComponent(duration);
                var encodedActivity = encodeURIComponent(activityID);

                var url = baseUrl+'?day='+encodedDay+'&time='+encodedTime+'&room='+encodedRoom+'&duration='+encodedDuration+'&activity='+encodedActivity;
                window.location.href = url;
            }
            function loadAvailableSlots() {
                var selectedActivity = document.getElementById('activities').value;
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('preference').innerHTML = '';
                        document.getElementById('preference').innerHTML = xhr.responseText;
                    }
                };

                xhr.open('GET', '../../Process/SchedulerProcess/get_preferences.php?activity_id=' + selectedActivity, true);
                xhr.send();
            }
            function loadRoomSchedule(phpFile){
                var selectedRoom = document.getElementById('rooms').value;
                var selectedActivity = document.getElementById('activities').value;
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('room_schedule').innerHTML = '';
                        document.getElementById('room_schedule').innerHTML = xhr.responseText;
                    }
                };

                xhr.open('GET', phpFile + '?room_id=' + selectedRoom + '&activity_id=' + selectedActivity, true);
                xhr.send();
            }

            function loadDateSchedule(){
                var selectedRoom = document.getElementById('rooms').value;
                var selectedActivity = document.getElementById('activities').value;
                var selectedDate = document.getElementById('activityDate').value;
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('room_date_schedule').innerHTML = '';
                        document.getElementById('room_date_schedule').innerHTML = xhr.responseText;
                    }
                };

                xhr.open('GET', '../../Process/SchedulerProcess/get_room_date_schedule.php?room_id=' + selectedRoom + '&activity_id=' + selectedActivity + '&date=' + selectedDate, true);
                xhr.send();
            }
        </script>
    </body>
</html>