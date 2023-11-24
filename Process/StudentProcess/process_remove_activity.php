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
    $_SESSION['error2'] = "You don't have rights to remove activities. Please log in with Student account";
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
            $_SESSION['error2'] = "Invalid data posted.";
            header($return);
            exit;
        }

        if(!areIDsCorrect($userId, $activityID, $pdo)){
            $_SESSION['error2'] = "Invalid data posted.";
            header($return);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM STUDENT_ACTIVITIES WHERE student_ID = :student AND activity_ID = :activity");
        $stmt->execute([
            ':student' => $userId,
            ':activity' => $activityID,
        ]);

        if ($stmt->rowCount() == 0) {
            $_SESSION['error2'] = "This activity is not registered";
            header($return);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM STUDENT_ACTIVITIES WHERE student_ID = :student AND activity_ID = :activity");
        $stmt->execute([
            ':student' => $userId,
            ':activity' => $activityID,
        ]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Activity was successfully removed from your schedule.";
            header($return);
            exit;
        } else {
            $_SESSION['error2'] = "An error occurred while processing your request. Please try again.";
            header($return);
            exit;
        }
        
    } else {
        $_SESSION['error2'] = "Chose activity time please.";
        header($return);
        exit;
    }
}else{
    $_SESSION['error2'] = "POST request required.";
    header($return);
    exit;
}

function areIDsCorrect($user_ID, $activity_ID, $pdo){
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM STUDENT_ACTIVITIES WHERE activity_ID = :aid AND student_ID = :uid");
    $stmt->execute([
        ':aid' => $activity_ID,
        ':uid' => $user_ID,
    ]);
    return $stmt->fetchColumn() > 0;
}
?>