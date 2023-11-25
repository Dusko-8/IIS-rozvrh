<?php
session_start();
require '../../Database/db_connect.php';

$days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"];
$times = ["8:00", "9:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00"];
$preferences = [];

//GET USER ID
$stmt = $pdo->prepare("SELECT user_ID FROM USERS WHERE username = :username");
$stmt->execute([':username' => $_SESSION['username']]);
$userID = $stmt->fetch(PDO::FETCH_ASSOC);
$userID = $userID['user_ID'];
$_SESSION['user_ID'] = $userID;


//GET TEACHER PREFERENCES
$stmt = $pdo->prepare("SELECT pst.teacher_slot_ID, pst.preference, dt.week_day, dt.time_range " . 
                        "FROM PREFERED_SLOTS_TEACHER AS pst " .
                        "NATURAL JOIN DAY_TIME AS dt " .
                        "WHERE user_ID = :id");
$stmt->execute([':id' => $userID]);
$preferences = $stmt->fetchall(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Main Page</title>
        <link rel="stylesheet" href="../../Styles/teacher_style.css">
        <link rel="stylesheet" href="../../Styles/sidebar_style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    </head>
    <body>   
    <!-- SIDE BAR -->
    <?php include '../../Components/sidebar_component.php'; ?>
    <!-- Sidebar Toggle Icon -->
    <div class="sidebar-header">
        <!-- Sidebar Toggle -->
        <div class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </div>
    </div>
    <!-- Overlay -->
    <div class="overlay hidden" onclick="toggleSidebar()"></div>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('hidden');
            // Toggle the 'show' class for the overlay
            overlay.classList.toggle('show');
        }
    </script>

    <!-- SUCCESS ALERT -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; ?>
        </div>
        <script>
            setTimeout(function () {
                document.querySelector('.alert-success').style.display = 'none';
            }, 5000);
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>


    <!-- ERROR ALERT -->
    <?php if (isset($_SESSION['errorAlert'])): ?>
        <div class="alert alert-error">
            <?= $_SESSION['errorAlert']; ?>
        </div>
        <script>
            setTimeout(function () {
                document.querySelector('.alert-error').style.display = 'none';
            }, 5000);
        </script>
        <?php unset($_SESSION['errorAlert']); ?>
    <?php endif; ?>


    <div class="horizontal-container" style="gap: 100px;  align-items: flex-start;">
        <div class="vertical-container">
            <h2>Add Preference</h2>
            <!-- ADD PREFERENCE FORM -->
            <form id="addPreferenceForm" action="../../Process/TeacherProcess/process_add_preference.php" method="post">
                <div class="horizontal-container">
                    <div class="vertical-container">
                        <label for="workdays">Select workday:</label>
                        <select id="workdays" name="workdays">
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                        </select>
                    </div>
                    <div class="vertical-container">
                        <label for="hours">Select time:</label>
                            <select id="hours" name="hours">
                                <option value="8">8:00</option>
                                <option value="9">9:00</option>
                                <option value="10">10:00</option>
                                <option value="11">11:00</option>
                                <option value="12">12:00</option>
                                <option value="13">13:00</option>
                                <option value="14">14:00</option>
                                <option value="15">15:00</option>
                                <option value="16">16:00</option>
                            </select>
                    </div>
                </div>
                <label for="slider">Select preference duration:</label>
                <div class="horizontal-container">
                    <input type="range" id="slider" name="slider" min="1" max="100" value="2">
                    <p><span id="sliderValue"></span></p>
                </div>
                <label for="pref">Select preference:</label>
                <select id="pref" name="pref" style="width: 50%;">
                    <option value="Prefers">Prefer</option>
                    <option value="Disprefers">Disprefer</option>
                </select>
                <br>
                <button type="submit" class="styled-button">Add Preference</button>
            </form>
        </div>
        <div class="vertical-container">
            <h2>Remove Preference</h2>
            <!-- REMOVE PREFERENCE FORM -->
            <form id="removePreferenceForm" action="../../Process/TeacherProcess/process_remove_preference.php" method="post">
                <label for="removePref">Select workday:</label>
                <select id="removePref" name="removePref">
                <?php
                    foreach ($preferences as $pref) {
                        echo '<option value="' . $pref['teacher_slot_ID'] . '">' . $pref['preference'] . '-' . $pref['week_day'] . ' : ' . $pref['time_range'] . '</option>';
                    }
                ?>
                </select>
                <button type="submit" class="styled-button">Remove Preference</button>
            <form>
            </div>
    </div>
    
    <!-- TABLE TO SHOW PREFERENCES -->
    <table>
        <thead>
            <tr>
                <th></th>
                <?php foreach ($times as $time) : ?>
                    <th><?= $time ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($days as $day){
                echo '<tr>';
                    echo '<th>' . $day . '</th>';
                    foreach ($times as $time){
                        echo '<td>';
                        foreach ($preferences as $pref){
                            if (isTimeRangeFitting($pref['time_range'], $time, getNextTimeSlot($time)) && $pref['week_day'] == $day) {
                                if($pref['preference'] == 'Prefers'){
                                    echo '<div class="prefers">';
                                    echo '<label>Pref</label>';
                                    echo '</div>';
                                }else{
                                    echo '<div class="disprefers">';
                                    echo '<label>DisPref</label>';
                                    echo '</div>';
                                }
                                
                            }
                        }
                        echo '</td>';
                    }
                echo '</tr>';
            } 

            //check if preference fits into table
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

            //get next time slot
            function getNextTimeSlot($timeSlot) {
                $currentTime = new DateTime($timeSlot);
                $currentTime->add(new DateInterval('PT1H')); 
                return $currentTime->format('H:i');
            }
            ?>
        </tbody>
    </table>

    <!-- SCRIPT FOR SLIDER UPDATING -->
    <script>
        const slider = document.getElementById('slider');
        const sliderValue = document.getElementById('sliderValue');
        const hoursSelect = document.getElementById('hours');

        

        hoursSelect.addEventListener('change', updateSlider);

        function updateSlider() {
            slider.max = 17 - hoursSelect.value;
            sliderValue.textContent = slider.value + 'h';
        }

        slider.addEventListener('input', () => {
            sliderValue.textContent = slider.value + 'h';
        });
        updateSlider();
    </script>
    </body>
</html>