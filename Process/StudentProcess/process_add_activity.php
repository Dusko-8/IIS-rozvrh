<?php
session_start();

require '../../Database/db_connect.php';
if($_SESSION['pageNum'] == 1){
    $return = 'Location: ../../Pages/Student/student_weekly.php';
}else{
    $return = 'Location: ../../Pages/Student/student_yearly.php';
}
unset($_SESSION['pageNum']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if(isset($_POST['activity']) && isset($_SESSION['user_ID'])){
        
        $userId = $_SESSION['user_ID'];
        $activityID = $_POST['activity'];

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
            header($return);
            exit;
        } else {
            $_SESSION['error'] = "An error occurred while processing your request. Please try again.";
            header($return);
            exit;
        }
        
    } else {
        $_SESSION['error'] = "Chose activity time please.";
        header($return);
        exit;
    }
}
exit;
?>
