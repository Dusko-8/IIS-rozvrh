<?php
session_start();
if(isset($_POST['selected_activity'])){
    $selectedActID = $_POST['selected_activity'];
}
// Check if the project_id is set in the URL
if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: ../Pages/login_page.php');
        exit;
    }

    if ($_SESSION['user_role'] !== 'Admin' and $_SESSION['user_role'] !== 'Guarantor') {
        header('Location: ../Pages/main_page.php');
        exit;
    }

    require '../Database/db_connect.php';
    $stmt = $pdo->prepare("SELECT * FROM activity WHERE subject_ID = ?");
    $stmt->execute([$subject_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>Activities for Subject: $subject_id</h2>";
?>
    <form action="activity_slots_page.php" method="post">
        <?php
        // Display checkboxes for each activity
        foreach ($activities as $row) {
            if(isset($selectedActID) && $selectedActID === $row['activity_ID']){
                echo "<label><input type='radio' name='selected_activity' value='{$row['activity_ID']}' 'checked'> {$row['activity_type']}</label><br>";
            }else{
                echo "<label><input type='radio' name='selected_activity' value='{$row['activity_ID']}'> {$row['activity_type']}</label><br>";
            }
            
        }
        echo "<label><input type='radio' name='selected_activity' value='-1'> New Activity</label><br>";
        ?>
        <input type="hidden" id="subjectID" name="subjectID" value='<?php echo $subject_id; ?>'>
        <input type="submit" value="Submit">
    </form>
<?php
} else {
    header("Location: guaranted_sub_page.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferred Slots</title>
    <link rel="stylesheet" href="../Styles/guarant_style.css">
</head>

<body>
    
    <?php
    /*
    
    $stmt = $pdo->prepare("SELECT DISTINCT user_ID,username FROM users JOIN subjects WHERE user_role = 'Teacher' OR (user_role = 'Guarantor' AND subject_ID = ?)");
    $stmt->execute([$subject_id]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $selectedTeacherID = isset($_POST['teacher_id']) ? $_POST['teacher_id'] : null;

    $stmt = $pdo->prepare("SELECT DAY_TIME.day_time_ID, week_day, time_range, preference
                            FROM PREFERED_SLOTS_TEACHER
                            JOIN DAY_TIME ON PREFERED_SLOTS_TEACHER.day_time_ID = DAY_TIME.day_time_ID
                            WHERE guarantor_ID = ?");
    if (isset($selectedTeacherID)) {
        $stmt->execute([$selectedTeacherID]);
        $teacherPrefs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    ?>

    <form method="post">
        <label for="teacher">Select Teacher:</label>
        <select name="teacher_id" id="teacher" onchange="this.form.submit()">
            <option value="" selected disabled>Select Teacher</option>

            <?php foreach ($teachers as $teacher) {
                $selected = ($selectedTeacherID == $teacher['user_ID']) ? 'selected' : '';
                echo '<option value="' . $teacher['user_ID'] . '" ' . $selected . '>' . $teacher['username'] . '</option>';
            }
            ?>
        </select>
    </form>
    
    <?php
    */
    if(isset($selectedActID)){
        $stmt = $pdo->prepare("SELECT DAY_TIME.day_time_ID, week_day, time_range, preference
                            FROM PREFERED_SLOTS_TEACHER
                            JOIN DAY_TIME ON PREFERED_SLOTS_ACTIVITY.day_time_ID = DAY_TIME.day_time_ID
                            WHERE activity_ID = ?");
        $stmt->execute([$selectedTeacherID]);
        $teacherPrefs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    $preferences = [];
    if (isset($prefs)) {
        foreach ($prefs as $row) :
            // Extract start and end times
            list($startTime, $endTime) = explode('-', $row['time_range']);

            // Convert start time to 24-hour format
            $startHour = date('G', strtotime($startTime));

            // Convert end time to 24-hour format
            $endHour = date('G', strtotime($endTime));

            // Fill the preferences array with 1-hour intervals
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                $preferences[$row['week_day']][$hour] = $row['preference'];
            }
        endforeach;
    }
    // Days and times
    $days = ['Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota', 'Ňedeľa'];
    $times = range(8, 20);

    // Display the table
    echo '<table>';
    echo '<tr><th>Day</th>';
    foreach ($times as $time) {
        echo '<th>' . $time . ':00 - ' . ($time + 1) . ':00</th>';
    }
    echo '</tr>';

    foreach ($days as $day) {
        echo '<tr>';
        echo '<td>' . $day . '</td>';

        foreach ($times as $time) {
            $class = '';

            if (isset($preferences[$day][$time])) {
                $preference = $preferences[$day][$time];
                $class = ($preference == 'Preferuje') ? 'prefer-green' : 'prefer-red';
            }

            echo '<td class="' . $class . '"></td>';
        }

        echo '</tr>';
    }

    echo '</table>';
    ?>

</body>

</html>