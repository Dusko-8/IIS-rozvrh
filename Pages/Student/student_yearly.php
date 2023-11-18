<?php
session_start();
require '../../Database/db_connect.php';
$_SESSION['pageNum'] = 2;
$user_id;
$activities = [];
$allSubjects = [];
$daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$timeSlots = ['8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
$tableQuerry = "SELECT SUBJECTS.abbervation, DAY_TIME.week_day, DAY_TIME.time_range, ACTIVITY.activity_date, ACTIVITY.repetition " .
                "FROM SUBJECTS " . 
                "JOIN ACTIVITY ON SUBJECTS.subject_ID = ACTIVITY.subject_ID " .
                "JOIN DAY_TIME ON DAY_TIME.day_time_ID = ACTIVITY.day_time_ID " .
                "WHERE ACTIVITY.activity_ID = :id";
try {
    $stmt = $pdo->prepare("SELECT user_ID FROM USERS WHERE username = :username");
    $stmt->execute([':username' => $_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $user_id = $result['user_ID'];

        $stmt = $pdo->prepare("SELECT SUBJECTS.abbervation, ACTIVITY.repetition, ACTIVITY.activity_ID, ACTIVITY.activity_type, DAY_TIME.week_day, DAY_TIME.time_range
                                FROM ACTIVITY 
                                JOIN STUDENT_ACTIVITIES ON STUDENT_ACTIVITIES.activity_ID = ACTIVITY.activity_ID
                                JOIN SUBJECTS ON SUBJECTS.subject_ID = ACTIVITY.subject_ID
                                JOIN DAY_TIME ON DAY_TIME.day_time_ID = ACTIVITY.day_time_ID
                                WHERE STUDENT_ACTIVITIES.student_ID = :user_ID");
        $stmt->execute([':user_ID' => $user_id]);
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM SUBJECTS");
        $stmt->execute();
        $allSubjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "User not found!";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
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
    <h2>Yearly Calendar</h2>

    <div class="buttons-container">
        <button id="button1" class="styled-button" onclick="location.href='student_weekly.php'">Weekly Schedule</button>
        <button id="button2" class="styled-button">Yearly Schedule</button>
    </div>

    <script>
        var selectedButtonId = null;

        function showTable(tableNumber) {
            if (selectedButtonId !== null) {
                document.getElementById(selectedButtonId).classList.remove('selected');
            }

            var clickedButtonId = 'button' + tableNumber;
            document.getElementById(clickedButtonId).classList.add('selected');
            selectedButtonId = clickedButtonId;

            
        }

        window.onload = function() {
            showTable(2);
        };
    </script>

        <table>
        <thead>
        <tr>
            <th></th>
            <?php foreach ($timeSlots as $timeSlot): ?>
                <th class="time-header"><?= $timeSlot ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
        <tbody>
        <?php
            foreach ($daysOfWeek as $day) {
                echo '<tr>';
                echo '<th>' . $day . '</th>';
                foreach ($timeSlots as $timeSlot) {
                    echo '<td>';
                    foreach ($activities as $subject) {
                        $stmt = $pdo->prepare($tableQuerry);
                        $stmt->execute([':id' => $subject['activity_ID']]);
                        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $abbreviation = $result['abbervation'];
                            $weekDay = $result['week_day'];
                            $timeRange = $result['time_range'];
                            if (isTimeRangeFitting($timeRange, $timeSlot, getNextTimeSlot($timeSlot)) && $weekDay == $day) {
                                echo "$abbreviation<br>";
                            }
                        }
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }

            function isTimeRangeFitting($timeRange, $tableStartTime, $tableEndTime) {
                list($startTime, $endTime) = explode('-', $timeRange);
                $startTime = new DateTime($startTime);
                $endTime = new DateTime($endTime);
                $currentTime = clone $startTime;

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

            function getNextTimeSlot($timeSlot) {
                $currentTime = new DateTime($timeSlot);
                $currentTime->add(new DateInterval('PT1H')); // Assuming each table header represents a 1-hour time slot
                return $currentTime->format('H:i');
            }
            ?>
        </tbody>
    </table>

    <div style="display: flex; justify-content: space-between; padding: 20px;">

        <!-- Left side -->
        <div style="width: 48%;">
            <h2>Add subject</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']);?>
            <?php endif; ?>

            <form id="scheduleFormLeft" action="../../Process/StudentProcess/process_add_activity.php" method="post">
                <label for='subject'>Subject:</label>
                <select name="subject" id="subject" required onchange="loadAvailableSlots()">
                    <option value="" disabled selected>Select a subject</option>
                    <?php foreach ($allSubjects as $subject): ?>
                        <option value="<?php echo $subject['abbervation']; ?>"><?php echo $subject['abbervation']; ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="time"></div>
            </form>
        </div>

        <!-- Right side -->
        <div style="width: 48%;">
            <!-- Duplicate the content here with appropriate styling -->
            <h2>Remove subject</h2>
            <?php if (isset($_SESSION['error2'])): ?>
                <div class="error"><?php echo $_SESSION['error2']; ?></div>
                <?php unset($_SESSION['error2']);?>
            <?php endif; ?>

            <form id="scheduleFormRight" action="../../Process/StudentProcess/process_remove_activity.php" method="post" style="text-align: right;">
                <label for='subject'  style="text-align: left;">Subject:</label>
                <select name="activity" id="activity">
                    <option value="" disabled selected>Select a subject</option>
                    <?php foreach ($activities as $activity): ?>
                        <option value="<?php echo $activity['activity_ID']; ?>"><?php echo $activity['abbervation'] . "  : " . $activity['activity_type'] . ": " . $activity['week_day'] . $activity['time_range'] . " (" . $activity['repetition'] . ")"; ?></option>
                    <?php endforeach; ?>
                </select>
                <div style="text-align: left;">
                    <button type="submit">Remove</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function loadAvailableSlots() {
            var selectedSubject = document.getElementById('subject').value;
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('time').outerHTML = xhr.responseText;
                }
            };
            
            xhr.open('GET', 'fetch_slots.php?subject=' + selectedSubject, true);
            xhr.send();
        }
    </script>
    </body>
</html>