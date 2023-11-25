<?php
session_start();
// Check if the project_id is set in the URL
if (isset($_GET['subject_id'])) {
    $subject_id = $_GET['subject_id'];

    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: ../../Pages/login_page.php');
        exit;
    }

    if ($_SESSION['user_role'] !== 'Admin' and $_SESSION['user_role'] !== 'Guarantor') {
        header('Location: ../../Pages/User/anotations_page.php');
        exit;
    }

    require '../../Database/db_connect.php';
    $stmt = $pdo->prepare("SELECT * FROM activity WHERE subject_ID = ?");
    $stmt->execute([$subject_id]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmtSubj = $pdo->prepare("SELECT title FROM subjects WHERE subject_ID = ?");
    $stmtSubj->execute([$subject_id]);
    $subjName = $stmtSubj->fetch(PDO::FETCH_ASSOC);

    $stmtAct = $pdo->prepare("SELECT a.activity_ID, a.activity_type, r.room_location, r.room_ID, u.username, a.teacher_ID, 
                            d.week_day, d.time_range, a.repetition, a.activity_date, a.duration 
                            FROM activity AS a
                            LEFT JOIN room AS r ON a.room_ID = r.room_ID
                            LEFT JOIN users AS u ON a.teacher_ID = u.user_ID
                            LEFT JOIN day_time AS d ON a.day_time_ID = d.day_time_ID
                            WHERE subject_ID = ?");
    $stmtAct->execute([$subject_id]);
    $activities = $stmtAct->fetchAll(PDO::FETCH_ASSOC);

    $stmtTeach = $pdo->prepare("SELECT sub_teach_ID, user_ID, username FROM subject_teachers
                            NATURAL JOIN users WHERE subject_ID = ?");
    $stmtTeach->execute([$subject_id]);
    $subjectTeachers =  $stmtTeach->fetchAll(PDO::FETCH_ASSOC);

    $stmtTeachAll = $pdo->prepare("SELECT user_ID, username FROM users WHERE user_role IN ('Teacher', 'Guarantor')");
    $stmtTeachAll->execute();
    $allTeachers = $stmtTeachAll->fetchAll(PDO::FETCH_ASSOC);

    $existingTeachers = array_column($subjectTeachers, 'user_ID');
    $availableTeachers = array_filter($allTeachers, function ($teacher) use ($existingTeachers) {
        return !in_array($teacher['user_ID'], $existingTeachers);
    });

    $types = ['Lecture', 'Tutorial', 'Seminar', 'Exam', 'Consultation', 'Exercise', 'Demo'];
    $repetitions = ['everyWeek', 'evenWeek', 'oddWeek', 'oneTime'];
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
    <title>Activities</title>
    <link rel="stylesheet" href="../../Styles/style.css">
    <link rel="stylesheet" href="../../Styles/guarant_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../Styles/activity_style.css">
</head>

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
    }
</style>

<body>
    <div class="main-container">
        <!-- Content Area -->
        <div class="content">
            <button class="up_button" onclick="goBack()">Back</button>
            <button class="up_button" style="position: absolute; top: 20px; right: 20px; margin-right: 5%;" onclick="showAddActivityDialog()">Add New Activity</button>
            <div class="title">Activities of <?php echo  htmlspecialchars($subjName['title']) ?></div>

            <table>
                <thead>
                    <tr>
                        <th>Activity Type</th>
                        <th>*Room Location</th>
                        <th>Teacher</th>
                        <th>*Week Day</th>
                        <th>Time Range</th>
                        <th>Repetition</th>
                        <th>Duration</th>
                        <th>*Activity Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity) : ?>
                        <tr>
                            <td><?= $activity['activity_type'] ?></td>
                            <td><?= $activity['room_location'] ?? '-' ?></td>
                            <td><?= $activity['username'] ?></td>
                            <td><?= $activity['week_day'] ?? '-' ?></td>
                            <td><?= $activity['time_range'] ?? '-' ?></td>
                            <td><?= $activity['repetition'] ?></td>
                            <td><?= $activity['duration'] ?></td>
                            <td><?= $activity['activity_date'] ?? '/' ?></td>
                            <td>
                                <!-- Trigger the edit dialog -->
                                <button class="edit-btn" onclick="showEditDialog('<?php echo $activity['activity_ID']; ?>','<?php echo $activity['teacher_ID']; ?>',
                                '<?php echo $activity['repetition']; ?>', '<?php echo $activity['activity_type']; ?>', '<?php echo $activity['duration']; ?>')">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <button class="delete-btn" onclick="deleteActivity('<?php echo $activity['activity_ID']; ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="prefBtn" onclick="showPreferences(<?php echo $activity['activity_ID']; ?>, <?php echo $subject_id; ?>)">
                                    <i class="fas fa-cogs"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="info-icon">
                <div class="tooltip">* Values are assigned by Scheduler, preferences can be set in Action collum.</div>
            </div>

            <div class="title">Subject Teachers</div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subjectTeachers as $subjectTeacher) : ?>
                            <tr>
                                <td><?= $subjectTeacher['username'] ?></td>
                                <td>
                                    <!-- Trigger the edit dialog -->
                                    <button class="delete-btn" onclick="deleteSubjectTeacher('<?php echo $subjectTeacher['sub_teach_ID'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <td>
                            <select id="newTeacherSelect">
                                <option value="" disabled selected>Select Teacher</option>
                                <?php foreach ($availableTeachers as $teacher) : ?>
                                    <option value="<?= $teacher['user_ID'] ?>"><?= $teacher['username'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <button class="prefBtn" onclick="addSubjectTeacher('<?php echo $subject_id ?>')">Add</button>
                        </td>
                    </tbody>
                </table>
            </div>

            <div id="overlay"></div>
            <div id="editDialog" class="dialog">
                <form method="post" id="editActivityForm" action="">
                    <input type="hidden" id="editActivityID" name="activity_ID" value="">
                    <label>*Type:
                        <select name="activity_type" id="EditType" required>
                            <?php foreach ($types as $type) : ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Teacher Username:
                        <select name="username" id="EditUsername" required>
                            <?php foreach ($allTeachers as $username) : ?>
                                <option value="<?php echo $username['user_ID']; ?>"><?php echo $username['username']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Repetition:
                        <select name="repetition" id="EditRepe" required>
                            <?php foreach ($repetitions as $repe) : ?>
                                <option value="<?php echo $repe; ?>"><?php echo $repe; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Duration(hours):
                        <select name="duration" id="EditDura" required>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value=\"$i\">$i</option>";
                            }
                            ?>
                        </select>
                    </label>
                    <br>
                    <p id="dialog_notification" style="color: green; display: none;"></p>
                    <input type="submit" value="Save Changes">
                    <button type="button" onclick="hideDialog()">Cancel</button>
                </form>
            </div>
            <!--Add activity dialog -->
            <div id="addActivityDialog" class="dialog">
                <form method="post" id="addActivityForm" action="">
                    <input type="hidden" id="subject_ID" name="subject_ID" value="<?php echo $subject_id ?>">
                    <label>*Type:
                        <select name="activity_type" id="AddType" required>
                            <option disabled selected>Select Type</option>
                            <?php foreach ($types as $type) : ?>
                                <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Teacher Username:
                        <select name="username" id="AddUsername" required>
                            <option disabled selected>Select Teacher</option>
                            <?php foreach ($allTeachers as $username) : ?>
                                <option value="<?php echo $username['user_ID']; ?>"><?php echo $username['username']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Repetition:
                        <select name="repetition" id="AddRepe" required>
                            <option disabled selected>Select Repetition</option>
                            <?php foreach ($repetitions as $repe) : ?>
                                <option value="<?php echo $repe; ?>"><?php echo $repe; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <br>
                    <label>*Duration(hours):
                        <select name="duration" id="EditDura" required>
                            <option disabled selected>Select Duration</option>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value=\"$i\">$i</option>";
                            }
                            ?>
                        </select>
                    </label>
                    <br>
                    <p id="add_dialog_notification" style="color: green; display: none;"></p>
                    <input type="submit" value="Add Activity">
                    <button type="button" onclick="hideDialog()">Cancel</button>
                </form>
            </div>
        </div>
    </div>
    </div>

    <script>
        let originalActivityData = {
            activityID: '',
            teacherID: '',
            repetition: '',
            duration: '',
            type: ''
        };

        function goBack(subjectID) {
            window.location.href = 'guaranted_sub_page.php';
        }

        function showEditDialog(activityID, teacherID, repetition, type, duration) {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('editDialog').style.display = 'block';
            document.getElementById('editActivityID').value = activityID;

            // Set default values based on the row
            setSelectedOption('EditType', type);
            setSelectedOption('EditUsername', teacherID);
            setSelectedOption('EditRepe', repetition);
            setSelectedOption('EditDura', duration)

            originalActivityData.activityID = activityID;
            originalActivityData.teacherID = teacherID;
            originalActivityData.repetition = repetition;
            originalActivityData.duration = duration;
            originalActivityData.type = type;
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

        document.getElementById('editActivityForm').addEventListener('submit', function(event) {
            event.preventDefault();
            saveActivityChanges();
        });

        function hideDialog() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('editDialog').style.display = 'none';
            location.reload();
        }

        function saveActivityChanges() {
            const formData = new FormData(document.getElementById('editActivityForm'));

            // Check if any changes were made
            const currentType = formData.get('activity_type');
            const currentTeacherID = formData.get('username');
            const currentRepetition = formData.get('repetition');
            const currentDuration = formData.get('duration');

            if (
                currentType !== originalActivityData.type ||
                currentTeacherID !== originalActivityData.teacherID ||
                currentRepetition !== originalActivityData.repetition ||
                currentDuration !== originalActivityData.duration
            ) {
                console.log('Changes detected, saving...');

                fetch('../../Process/GuarantorProcess/process_edit_activity.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        if (data.error) {
                            document.getElementById('dialog_notification').innerText = data.error;
                            document.getElementById('dialog_notification').style.color = 'red';
                        } else {
                            document.getElementById('dialog_notification').innerText = 'Changes saved successfully!';
                            document.getElementById('dialog_notification').style.color = 'green';
                        }
                        document.getElementById('dialog_notification').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('dialog_notification').innerText = 'Error saving changes';
                        document.getElementById('dialog_notification').style.color = 'red';
                        document.getElementById('dialog_notification').style.display = 'block';
                    });
            } else {
                console.log('No changes detected');
                document.getElementById('dialog_notification').innerText = 'No changes to save.';
                document.getElementById('dialog_notification').style.color = 'orange';
                document.getElementById('dialog_notification').style.display = 'block';
            }
        }

        function showPreferences(activityId, subjectId) {
            // Create a form element
            var form = document.createElement("form");
            form.method = "post";
            form.action = "activity_slots_page.php";

            // Create input elements for subjectId and activityId
            var subjectIdInput = document.createElement("input");
            subjectIdInput.type = "hidden";
            subjectIdInput.name = "subjectID";
            subjectIdInput.value = subjectId;

            var activityIdInput = document.createElement("input");
            activityIdInput.type = "hidden";
            activityIdInput.name = "selected_activity";
            activityIdInput.value = activityId;

            // Append input elements to the form
            form.appendChild(subjectIdInput);
            form.appendChild(activityIdInput);

            // Append the form to the document and submit it
            document.body.appendChild(form);
            form.submit();
        }

        function showAddActivityDialog() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('addActivityDialog').style.display = 'block';
        }

        document.getElementById('addActivityForm').addEventListener('submit', function(event) {
            event.preventDefault();
            addNewActivity();
        });

        function addNewActivity() {
            fetch('../../Process/GuarantorProcess/process_add_activity.php', {
                    method: 'POST',
                    body: new FormData(document.getElementById('addActivityForm'))
                })
                .then(response => response.json())
                .then(data => {
                    // Handle the response data, update UI or show a notification
                    console.log(data);
                    document.getElementById('add_dialog_notification').innerText = 'Activity added successfully!';
                    document.getElementById('add_dialog_notification').style.display = 'block';
                })
                .catch(error => {
                    // Handle errors
                    console.error('Error:', error);
                    document.getElementById('add_dialog_notification').innerText = 'Error adding activity';
                    document.getElementById('add_dialog_notification').style.display = 'block';
                });
        }


        function addSubjectTeacher(subjectId) {
            var selectedTeacherId = document.getElementById("newTeacherSelect").value;

            // Check if a teacher is selected
            if (!selectedTeacherId) {
                alert("Please select a teacher.");
                return;
            }

            // Prepare data to be sent in the POST request
            var formData = new FormData();
            formData.append('subject_id', subjectId);
            formData.append('teacher_id', selectedTeacherId);

            // Make a POST request using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../Process/GuarantorProcess/process_add_teacher_subject.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Reload the page after the request is successful
                    location.reload();
                } else {
                    console.error("Error adding subject teacher. Status:", xhr.status);
                }
            };
            xhr.send(formData);
        }

        function deleteSubjectTeacher(subTeachId) {
            // Confirm the deletion
            var confirmation = confirm("Are you sure you want to delete this subject teacher?");

            if (!confirmation) {
                return;
            }

            // Prepare data to be sent in the POST request
            var formData = new FormData();
            formData.append('sub_teach_id', subTeachId);

            // Make a POST request using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../Process/GuarantorProcess/process_delete_teacher_subject.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Reload the page after the request is successful
                    location.reload();
                } else {
                    console.error("Error deleting subject teacher. Status:", xhr.status);
                }
            };
            xhr.send(formData);
        }

        function deleteActivity(activityId) {
            // Confirm the deletion
            var confirmation = confirm("Are you sure you want to delete this activity?");

            if (!confirmation) {
                return;
            }

            // Prepare data to be sent in the POST request
            var formData = new FormData();
            formData.append('activity_id', activityId);

            // Make a POST request using AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../Process/GuarantorProcess/process_delete_activity.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Reload the page after the request is successful
                    location.reload();
                } else {
                    console.error("Error deleting activity. Status:", xhr.status);
                }
            };
            xhr.send(formData);
        }
    </script>
</body>

</html>