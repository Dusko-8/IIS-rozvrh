<?php
session_start();

require '../Database/db_connect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../Pages/login_page.php');
    exit;
}

if ($_SESSION['user_role'] !== 'Admin' && $_SESSION['user_role'] !== 'Guarantor') {
    header('Location: ../Pages/main_page.php');
    exit;
}

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

        header('Location: ../Pages/activity_slots_page.php');
        exit;
    }
}
?>