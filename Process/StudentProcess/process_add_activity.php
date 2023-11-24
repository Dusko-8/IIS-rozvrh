<?php
session_start();
require '../../Database/db_connect.php';

if($_SESSION['pageNum'] == 1){
    $return = 'Location: ../../Pages/Student/student_weekly.php';
}else{
    $return = 'Location: ../../Pages/Student/student_yearly.php';
}
unset($_SESSION['pageNum']);

if($_SESSION['user_role'] != 'Student'){
    $_SESSION['error'] = "You don't have rights to add activities. Please log in with Student account";
    header($return);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if(isset($_POST['activity']) && isset($_SESSION['user_ID'])){
        
        $userId = $_SESSION['user_ID'];
        $userId = filter_var($userId, FILTER_VALIDATE_INT);
        $activityID = $_POST['activity'];
        $activityID = filter_var($activityID, FILTER_VALIDATE_INT);

        if(!$userId || !$activityID){
            $_SESSION['error'] = "Invalid data posted.";
            header($return);
            exit;
        }

        if(!isUserValid($userId, $pdo) || !isActivityValid($activityID, $pdo)){
            $_SESSION['error'] = "Invalid data posted. ID not correct.";
            header($return);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM STUDENT_ACTIVITIES WHERE student_ID = :student AND activity_ID = :activity");
        $stmt->execute([
            ':student' => $userId,
            ':activity' => $activityID,
        ]);
        if ($stmt->rowCount() != 0) {
            $_SESSION['error'] = "You have already registered this activity.";
            header($return);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO STUDENT_ACTIVITIES(student_ID, activity_ID) VALUES (:student, :activity)");
        $stmt->execute([
            ':student' => $userId,
            ':activity' => $activityID,
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Activity was successfully added to your schedule.";
            header($return);
            exit;
        } else {
            $_SESSION['error'] = "An error occurred while processing your request. Please try again.";
            header($return);
            exit;
        }
        
    } else {
        if(isset($_POST['activity'])){
            $_SESSION['error'] = "User failed to load. Please log in again.";
        }else{
            $_SESSION['error'] = "Chose activity time please.";
        }
        header($return);
        exit;
    }
}else{
    $_SESSION['error'] = "POST request required.";
    header($return);
    exit;
}

function isUserValid($user_ID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM USERS WHERE user_ID = ? AND user_role = 'Student'");
    $stmt->execute([$user_ID]);
    return $stmt->fetchColumn() > 0;
}

function isActivityValid($activity_ID, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ACTIVITY WHERE activity_ID = ?");
    $stmt->execute([$activity_ID]);
    return $stmt->fetchColumn() > 0;
}
?>
