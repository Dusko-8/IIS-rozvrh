<?php
session_start();
require '../../Database/db_connect.php';

$prefID = $_POST['removePref'];

if($prefID == null || $prefID == ""){
    $_SESSION['errorAlert'] = 'Please select a preference to remove.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM PREFERED_SLOTS_TEACHER WHERE teacher_slot_ID = :prefID");
$stmt->execute([':prefID' => $prefID]);



if ($stmt->rowCount() > 0) {
    $_SESSION['success'] = 'Preference was removed successfully.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
} else {
    $_SESSION['errorAlert'] = "An error occurred while processing your request. Please try again.";
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}
?>