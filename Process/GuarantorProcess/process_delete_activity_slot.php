<?php
session_start();

require '../../Database/db_connect.php';

// Check if the user is logged in and is an Admin or Guarantor
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Guarantor')) {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the required parameters are present in the POST request
if (!isset($_POST['subjectID']) || !isset($_POST['selectedActID']) || !isset($_POST['activitySlotID'])) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['activitySlotID'], $_POST['selectedActID'], $_POST['subjectID'])) {
        $activitySlotID = $_POST['activitySlotID'];
        $selectedActID = $_POST['selectedActID'];
        $subjectID = $_POST['subjectID'];

        $stmt = $pdo->prepare("DELETE FROM PREFERED_SLOTS_ACTIVITY WHERE activity_slot_ID = :activitySlotID");
        $stmt->bindParam(":activitySlotID", $activitySlotID, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['post_data'] = array(
            'selectedActID' => $selectedActID,
            'subjectID' => $subjectID
        );

        header('Location: ../../Pages/Guarantor/activity_slots_page.php');
        exit;
    }
}
?>