<?php
require '../Database/db_connect.php';

if (isset($_POST['selected_activity'])) {
    $selectedActID = $_POST['selected_activity'];
    $stmt = $pdo->prepare("SELECT DISTINCT * FROM PREFERED_SLOTS_ACTIVITY
    JOIN DAY_TIME ON PREFERED_SLOTS_ACTIVITY.day_time_ID = DAY_TIME.day_time_ID
    JOIN ROOM ON PREFERED_SLOTS_ACTIVITY.room_ID = ROOM.room_ID
    JOIN USERS ON PREFERED_SLOTS_ACTIVITY.teacher_ID = USERS.user_ID
    WHERE activity_ID = ?");
    $stmt->execute([$selectedActID]);
    $preferedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$stmtRooms = $pdo->query("SELECT room_ID, room_location FROM ROOM");
$roomLocations = $stmtRooms->fetchAll(PDO::FETCH_ASSOC);

// Fetch usernames for dropdown
$stmtUsers = $pdo->query("SELECT user_ID, username FROM USERS");
$usernames = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Define week days
$weekDays = ['Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota', 'Ňedeľa'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit PREFERED_SLOTS_ACTIVITY</title>
    <style>
        /* Add your styling for the dialog here */
        #editDialog {
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
    </style>
</head>

<body>
    <h2>Edit PREFERED_SLOTS_ACTIVITY</h2>

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
                        <button onclick="showEditDialog(<?php echo $row['activity_slot_ID']; ?>, '<?php echo $row['room_ID']; ?>', '<?php echo $row['teacher_ID']; ?>', '<?php echo $row['day_time_ID']; ?>', '<?php echo $row['preference']; ?>')">Edit</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Edit Dialog -->
    <div id="overlay"></div>
    <div id="editDialog">
        <form method="post" action="">
            <input type="hidden" id="editActivitySlotID" name="activity_slot_ID" value="">
            <label>Room Location:
                <select name="Editroom_location">
                    <?php foreach ($roomLocations as $room) : ?>
                        <option value="<?php echo $room['room_ID']; ?>"><?php echo $location['room_location']; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br>
            <label>Teacher Username:
                <select name="Editusername">
                    <?php foreach ($usernames as $username) : ?>
                        <option value="<?php echo $username['user_ID']; ?>"><?php echo $username['username']; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br>
            <label>Day:
                <select name="Editweek_day">
                    <?php foreach ($weekDays as $day) : ?>
                        <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <br>
            <label>Time Range:
                <select name="Editstart_hour">
                    <?php
                    $selectedStart = date('H', strtotime($row['time_range']));
                    for ($i = 8; $i <= 20; $i++) {
                        $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo "<option value=\"$hour\" " . (($hour == $selectedStart) ? 'selected' : '') . ">$hour:00</option>";
                    }
                    ?>
                </select>
                -
                <select name="Editend_hour">
                    <?php
                    $selectedEnd = date('H', strtotime($row['time_range_end']));
                    for ($i = 9; $i <= 20; $i++) {
                        $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
                        echo "<option value=\"$hour\" " . (($hour == $selectedEnd) ? 'selected' : '') . ">$hour:00</option>";
                    }
                    ?>
                </select>
            </label>
            <br>
            <label>Preference:
                <select name="Editpreference">
                    <option value="Preferuje" <?php echo ($row['preference'] == 'Preferuje') ? 'selected' : ''; ?>>Preferuje</option>
                    <option value="Nepreferuje" <?php echo ($row['preference'] == 'Nepreferuje') ? 'selected' : ''; ?>>Nepreferuje</option>
                </select>
            </label>
            <br>
            <input type="submit" value="Save Changes">
            <button type="button" onclick="hideEditDialog()">Cancel</button>
        </form>
    </div>
    <script>
    function showEditDialog(activitySlotID, roomID, teacherID, dayTimeID, preference) {
        document.getElementById('overlay').style.display = 'block';
        document.getElementById('editDialog').style.display = 'block';
        document.getElementById('editActivitySlotID').value = activitySlotID;

        // Set default values based on the row
        setSelectedOption('Editroom_location', roomID);
        setSelectedOption('editUsername', '<?php echo $preferedSlots[0]['teacher_ID']; ?>');
        setSelectedOption('editWeekDay', '<?php echo $preferedSlots[0]['week_day']; ?>');
        setSelectedOption('editStartHour', '<?php echo date('H', strtotime($preferedSlots[0]['time_range'])); ?>');
        setSelectedOption('editEndHour', '<?php echo date('H', strtotime($preferedSlots[0]['time_range_end'])); ?>');
        setSelectedOption('editPreference', '<?php echo $preferedSlots[0]['preference']; ?>');
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

    function hideEditDialog() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('editDialog').style.display = 'none';
    }
</script>

</body>

</html>