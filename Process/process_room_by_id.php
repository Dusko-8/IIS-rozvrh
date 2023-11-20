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

    $stmt = $pdo->prepare("SELECT room_name, capacity, room_location FROM ROOM WHERE room_ID = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $room = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($room);
    } else {
        echo json_encode(["error" => "Unable to fetch room data"]);
    }
}
?>
