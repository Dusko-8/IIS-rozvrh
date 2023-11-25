<?php
session_start();
require '../../Database/db_connect.php';


$selectedActID = null;
$subjectID = null;

if (isset($_POST['selected_activity'])) {
    $selectedActID = $_POST['selected_activity'];
} else if (isset($_SESSION['post_data'])) {
    $post_data = $_SESSION['post_data'];
    $selectedActID = $post_data['selectedActID'];
    $subjectID = $post_data['subjectID'];
}

if (isset($_POST['subjectID'])) {
    $subjectID = $_POST['subjectID'];
}

$stmt = $pdo->prepare("SELECT DISTINCT * FROM PREFERED_SLOTS_ACTIVITY
    JOIN DAY_TIME ON PREFERED_SLOTS_ACTIVITY.day_time_ID = DAY_TIME.day_time_ID
    JOIN ROOM ON PREFERED_SLOTS_ACTIVITY.room_ID = ROOM.room_ID
    JOIN USERS ON PREFERED_SLOTS_ACTIVITY.teacher_ID = USERS.user_ID
    WHERE activity_ID = ?");
$stmt->execute([$selectedActID]);
$preferedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);



$stmtSubj = $pdo->prepare("SELECT abbervation FROM subjects WHERE subject_ID = ?");
$stmtSubj->execute([$subjectID]);
$result = $stmtSubj->fetch(PDO::FETCH_ASSOC);

$stmtAct = $pdo->prepare("SELECT activity_type FROM activity WHERE activity_ID = ?");
$stmtAct->execute([$selectedActID]);
$type = $stmtAct->fetch(PDO::FETCH_ASSOC);

if ($result !== false) {
    $subjName = $result['abbervation'];
} else {
    // No data found for the specified subject ID
    $subjName = "Unknown Subject";
}

$stmtRooms = $pdo->query("SELECT room_ID, room_location FROM ROOM");
$roomLocations = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

// Fetch usernames for dropdown
$stmtUsers = $pdo->query("SELECT user_ID, username FROM USERS WHERE user_role IN ('Guarantor', 'Teacher')");
$usernames = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Define week days
$weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../Styles/style.css">
    <link rel="stylesheet" href="../../Styles/guarant_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Edit Activity Slots</title>
    <style>
        .dialog {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .up_button {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-right: 9%;
        }
    </style>
</head>

<body>
    <div class="main-container">
        <!-- Content Area -->
        <div class="content">
            <button class="up_button" onclick="goBack(<?php echo $subjectID; ?>)">Back</button>
            <button class="up_button" style="position: absolute; top: 20px; right: 20px;" onclick="showAddPreferenceDialog()">Add New Preference</button>
            <div class="title">Manage Preferences of <?php echo isset($subjName) ? htmlspecialchars($subjName) : "Unknown Subject" ?> - <?php echo htmlspecialchars($type['activity_type']) ?></div>

            <table border="1">
                <thead>
                    <tr>
                        <th>Room ID</th>
                        <th>Teacher ID</th>
                        <th>Day</th>
                        <th>Time Range</th>
                        <th>Preference</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preferedSlots as $row) : ?>
                        <tr>
                            <td><?php echo $row['room_location']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td><?php echo $row['week_day']; ?></td>
                            <td><?php echo $row['time_range']; ?></td>
                            <td><?php echo $row['preference']; ?></td>
                            <td>
                                <!-- Trigger the edit dialog -->
                                <button class="edit-btn" onclick="showEditDialog(<?php echo $row['activity_slot_ID']; ?>, '<?php echo $row['room_ID']; ?>', '<?php echo $row['teacher_ID']; ?>','<?php echo $row['week_day']; ?>', '<?php echo $row['time_range']; ?>', '<?php echo $row['preference']; ?>')">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="delete-btn" onclick="deleteSlot(<?php echo $row['activity_slot_ID']; ?>, <?php echo $selectedActID; ?>, <?php echo $subjectID; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Edit Dialog -->
            <div id="overlay"></div>
            <div id="editDialog" class="dialog">
                <form method="post" id="editActivityForm" action="">
                    <input type="hidden" id="editActivitySlotID" name="activity_slot_ID" value="">
                    <label>*Room Location:
                        <select name="room_location" id="EditRoom_location" required>
                            <?php foreach ($roomLocations as $room) : ?>
                                <option value="<?php echo $room['room_ID']; ?>"><?php echo $room['room_location']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Teacher Username:
                        <select name="username" id="EditUsername" required>
                            <?php foreach ($usernames as $username) : ?>
                                <option value="<?php echo $username['user_ID']; ?>"><?php echo $username['username']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Day:
                        <select name="week_day" id="EditWeek_Day" required>
                            <?php foreach ($weekDays as $day) : ?>
                                <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Time Range:
                        <select name="start_hour" id="EditStart_hour" required>
                            <?php
                            $selectedStart = date('H', strtotime($row['time_range']));
                            for ($i = 8; $i <= 16; $i++) {
                                $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                echo "<option value=\"$hour\">$hour:00</option>";
                            }
                            ?>
                        </select>
                        -
                        <select name="end_hour" id="EditEnd_hour" required>
                            <?php
                            $selectedEnd = date('H', strtotime($row['time_range_end']));
                            for ($i = 9; $i <= 17; $i++) {
                                if ($i == 10) {
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option value=\"$hour\" selected >$hour:00</option>";
                                } else {
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option value=\"$hour\">$hour:00</option>";
                                }
                            }
                            ?>
                        </select>
                    </label>
                    <br>
                    <label>*Preference:
                        <select name="preference" id="EditPreference" required>
                            <option value="Prefers">Prefers</option>
                            <option value="Disprefers">Disprefers</option>
                        </select>
                    </label>
                    <br>
                    <p id="dialog_notification" style="color: green; display: none;"></p>
                    <input type="submit" value="Save Changes">
                    <button type="button" onclick="hideDialog()">Cancel</button>
                </form>
            </div>
            <!-- New Preferecne Dialog -->
            <div id="addPreferenceDialog" class="dialog">
                <form method="post" id="addPreferenceForm" action="">
                    <input type="hidden" id="editActivitySlotID" name="activity_ID" value="<?php echo $selectedActID ?>">
                    <label>*Room Location:
                        <select name="room_location" id="EditRoom_location" required>
                            <option value="" disabled selected>Select Room</option>
                            <?php foreach ($roomLocations as $room) : ?>
                                <option value="<?php echo $room['room_ID']; ?>"><?php echo $room['room_location']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Teacher Username:
                        <select name="username" id="EditUsername" required>
                            <option value="" disabled selected>Select Teacher</option>
                            <?php foreach ($usernames as $username) : ?>
                                <option value="<?php echo $username['user_ID']; ?>"><?php echo $username['username']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Day:
                        <select name="week_day" id="EditWeek_Day" required>
                            <option value="" disabled selected>Select Day</option>
                            <?php foreach ($weekDays as $day) : ?>
                                <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Time Range:
                        <select name="start_hour" id="SetStart_hour" required>
                            <?php
                            $selectedStart = date('H', strtotime($row['time_range']));
                            for ($i = 8; $i <= 16; $i++) {
                                if ($i == 8) {
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option value=\"$hour\" selected >$hour:00</option>";
                                } else {
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option value=\"$hour\">$hour:00</option>";
                                }
                            }
                            ?>
                        </select>
                        -
                        <select name="end_hour" id="SetEnd_hour" required>
                            <?php
                            $selectedEnd = date('H', strtotime($row['time_range_end']));
                            for ($i = 9; $i <= 17; $i++) {
                                if ($i == 10) {
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option value=\"$hour\" selected >$hour:00</option>";
                                } else {
                                    $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    echo "<option value=\"$hour\">$hour:00</option>";
                                }
                            }
                            ?>
                        </select>
                    </label>
                    <br>
                    <label>*Preference:
                        <select name="preference" id="EditPreference" required>
                            <option value="" disabled selected>Select Preference</option>
                            <option value="Prefers">Prefers</option>
                            <option value="Disprefers">Disprefers</option>
                        </select>
                    </label>
                    <br>

                    <p id="add_dialog_notification" style="color: green; display: none;"></p>
                    <input type="submit" value="Add Preference">
                    <button type="button" onclick="hideDialog()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let originalActivityData = {
            activityID: '',
            roomID: '',
            teacherID: '',
            week_day: '',
            startTime: '',
            endTime: '',
            preference: ''
        };

        function showEditDialog(activitySlotID, roomID, teacherID, week_day, timeRange, preference) {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('editDialog').style.display = 'block';
            document.getElementById('editActivitySlotID').value = activitySlotID;

            // Set default values based on the row
            setSelectedOption('EditRoom_location', roomID);
            setSelectedOption('EditUsername', teacherID);
            setSelectedOption('EditWeek_Day', week_day);
            var [startTime, endTime] = timeRange.split('-').map(time => time.trim().replace(':00', ''));
            setSelectedOption('EditStart_hour', startTime);
            setSelectedOption('EditEnd_hour', endTime);
            setSelectedOption('EditPreference', preference);

            originalActivityData.activityID = activitySlotID;
            originalActivityData.roomID = roomID;
            originalActivityData.teacherID = teacherID;
            originalActivityData.week_day = week_day;
            originalActivityData.startTime = startTime;
            originalActivityData.endTime = endTime;
            originalActivityData.preference;

        }

        function setSelectedOption(selectId, selectedValue) {
            var selectElement = document.getElementById(selectId);
            var options = selectElement.options;

            for (var i = 0; i < options.length; i++) {
                if (options[i].value == selectedValue) {
                    selectElement.selectedIndex = i;
                    break;
                }
            }
        }

        function deleteSlot(activitySlotID, selectedActID, subjectID) {
            if (confirm('Are you sure you want to delete this activity slot?')) {
                var form = document.createElement('form');
                form.method = 'post';
                form.action = '../../Process/GuarantorProcess/process_delete_activity_slot.php';

                // Append the necessary POST data as hidden input fields
                var inputActivitySlotID = document.createElement('input');
                inputActivitySlotID.type = 'hidden';
                inputActivitySlotID.name = 'activitySlotID';
                inputActivitySlotID.value = activitySlotID;
                form.appendChild(inputActivitySlotID);

                var inputSelectedActID = document.createElement('input');
                inputSelectedActID.type = 'hidden';
                inputSelectedActID.name = 'selectedActID';
                inputSelectedActID.value = selectedActID;
                form.appendChild(inputSelectedActID);

                var inputSubjectID = document.createElement('input');
                inputSubjectID.type = 'hidden';
                inputSubjectID.name = 'subjectID';
                inputSubjectID.value = subjectID;
                form.appendChild(inputSubjectID);

                // Append the form to the document body
                document.body.appendChild(form);

                // Submit the form
                form.submit();
            }
        }

        document.getElementById('editActivityForm').addEventListener('submit', function(event) {
            event.preventDefault();
            saveActivityChanges();
        });

        function saveActivityChanges() {
            const formData = new FormData(document.getElementById('editActivityForm'));

            // Check if any changes were made
            const currentRoomID = formData.get('room_location');
            const currentTeacherID = formData.get('username');
            const currentWeekDay = formData.get('week_day');
            const currentStartTime = formData.get('start_hour');
            const currentEndTime = formData.get('end_hour');
            const currentPreference = formData.get('preference');

            if (
                currentRoomID !== originalActivityData.roomID ||
                currentTeacherID !== originalActivityData.teacherID ||
                currentWeekDay !== originalActivityData.week_day ||
                currentStartTime !== originalActivityData.startTime ||
                currentEndTime !== originalActivityData.endTime ||
                currentPreference !== originalActivityData.preference
            ) {
                console.log('Changes detected, saving...');
                console.log(formData.get('activity_slot_ID'));

                fetch('../../Process/GuarantorProcess/process_edit_activity_slot.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        if (data.error) {
                            document.getElementById('dialog_notification').innerText = data.error;
                            document.getElementById('dialog_notification').style.display = 'block';
                        } else {
                            document.getElementById('dialog_notification').innerText = 'Changes saved successfully!';
                            document.getElementById('dialog_notification').style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('dialog_notification').innerText = 'Error saving changes';
                        document.getElementById('dialog_notification').style.display = 'block';
                    });
            } else {
                console.log('No changes detected');
                document.getElementById('dialog_notification').innerText = 'No changes to save.';
                document.getElementById('dialog_notification').style.display = 'block';
            }
        }

        function hideDialog() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('editDialog').style.display = 'none';
            location.reload();
        }

        function goBack(subjectID) {
            window.location.href = '../../Pages/Guarantor/activity_page.php?subject_id=' + subjectID;
        }

        function showAddPreferenceDialog() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('addPreferenceDialog').style.display = 'block';
        }

        document.getElementById('addPreferenceForm').addEventListener('submit', function(event) {
            event.preventDefault();
            addNewPreference();
        });


        function addNewPreference() {
            fetch('../../Process/GuarantorProcess/process_new_preference.php', {
                    method: 'POST',
                    body: new FormData(document.getElementById('addPreferenceForm'))
                })
                .then(response => response.json())
                .then(data => {
                    // Handle the response data, update UI or show a notification
                    console.log(data);
                    document.getElementById('add_dialog_notification').innerText = 'Preference added successfully!';
                    document.getElementById('add_dialog_notification').style.display = 'block';
                })
                .catch(error => {
                    // Handle errors
                    console.error('Error:', error);
                    document.getElementById('add_dialog_notification').innerText = 'Error adding preference';
                    document.getElementById('add_dialog_notification').style.display = 'block';
                });
        }

        document.addEventListener("DOMContentLoaded", function() {
            // Get references to the start and end hour dropdowns
            var startHourDropdown = document.getElementById("EditStart_hour");
            var endHourDropdown = document.getElementById("EditEnd_hour");

            // Add onchange event listener to the start hour dropdown
            startHourDropdown.addEventListener("change", function() {
                // Get the selected start hour value
                var selectedStartHour = parseInt(startHourDropdown.value);

                // Update the options in the end hour dropdown
                updateEndHourOptions(selectedStartHour);
            });

            // Function to update the options in the end hour dropdown
            function updateEndHourOptions(selectedStartHour) {
                // Clear existing options in the end hour dropdown
                endHourDropdown.innerHTML = "";

                // Add default option
                var defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.text = "End Hour";
                endHourDropdown.add(defaultOption);

                // Populate end hour dropdown with valid options
                for (var i = selectedStartHour + 1; i <= 17; i++) {
                    var hour = ("0" + i).slice(-2); // Pad with leading zero if needed
                    var option = document.createElement("option");
                    option.value = hour;
                    option.text = hour + ":00";
                    endHourDropdown.add(option);
                }
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Get references to the start and end hour dropdowns
            var startHourDropdown = document.getElementById("SetStart_hour");
            var endHourDropdown = document.getElementById("SetEnd_hour");

            // Add onchange event listener to the start hour dropdown
            startHourDropdown.addEventListener("change", function() {
                // Get the selected start hour value
                var selectedStartHour = parseInt(startHourDropdown.value);

                // Update the options in the end hour dropdown
                updateEndHourOptions(selectedStartHour);
            });

            // Function to update the options in the end hour dropdown
            function updateEndHourOptions(selectedStartHour) {
                // Clear existing options in the end hour dropdown
                endHourDropdown.innerHTML = "";

                // Add default option
                var defaultOption = document.createElement("option");
                defaultOption.value = "";
                defaultOption.text = "End Hour";
                endHourDropdown.add(defaultOption);

                // Populate end hour dropdown with valid options
                for (var i = selectedStartHour + 1; i <= 17; i++) {
                    var hour = ("0" + i).slice(-2); // Pad with leading zero if needed
                    var option = document.createElement("option");
                    option.value = hour;
                    option.text = hour + ":00";
                    endHourDropdown.add(option);
                }
            }


        });
    </script>
</body>

</html>