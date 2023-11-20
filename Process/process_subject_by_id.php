<?php
session_start();
require '../Database/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT S.title, S.abbervation, S.credits, S.subj_description, S.guarantor_ID, U.username AS guarantor_username FROM SUBJECTS S LEFT JOIN USERS U ON S.guarantor_ID = U.user_ID WHERE S.subject_ID = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $subject = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($subject);
    } else {
        echo json_encode(["error" => "Unable to fetch subject data"]);
    }
}
?>