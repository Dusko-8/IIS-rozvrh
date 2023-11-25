<?php
session_start();
require '../../Database/db_connect.php';

//CHECK PRE PROCESS
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    $_SESSION['errorAlert'] = 'POST request required.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

if($_SESSION['user_role'] != 'Teacher' && $_SESSION['user_role'] != 'Guarantor' && $_SESSION['user_role'] != 'Admin'){
    $_SESSION['errorAlert'] = 'You don\'t have rights to modify data. Please log in with Teacher or Guarantor account.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

if(!isset($_POST['removePref'])){
    $_SESSION['errorAlert'] = 'Data not set correctly.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

$prefID = $_POST['removePref'];


if($prefID == null || $prefID == ""){
    $_SESSION['errorAlert'] = 'Please select a preference to remove.';
    header('Location: ../../Pages/Teacher/teacher_main.php');
    exit;
}

$prefID = filter_var($prefID, FILTER_VALIDATE_INT);
if(!$prefID){
    $_SESSION['errorAlert'] = 'Invalid data.';
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