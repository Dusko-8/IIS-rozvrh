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
            function loadAvailableSlots() {
                var selectedActivity = document.getElementById('activities').value;
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('preference').innerHTML = '';
                        document.getElementById('preference').innerHTML = xhr.responseText;
                    }
                };

                xhr.open('GET', 'get_preferences.php?activity_id=' + selectedActivity, true);
                xhr.send();
            }
        </script>
    </body>
</html>