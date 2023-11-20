<?php
session_start();
require '../Database/db_connect.php';

header('Content-Type: application/json');

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['user_role'] !== 'Admin') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit;
}

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $roomId = $_POST['roomId'];
    $roomName = $_POST['roomName'];
    $capacity = $_POST['capacity'];
    $roomLocation = $_POST['roomLocation'];


    // Ensure capacity is a number
    $capacity = filter_var($capacity, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    if (!$capacity) {
        echo json_encode(["error" => "Capacity must be a positive integer greater than zero"]);
        exit;
    }
    // Cast capacity to an integer to ensure it matches the database type
    $capacity = (int) $capacity;

    // Validate that no input is empty
    if (empty($roomId) || empty($roomName) || empty($capacity) || empty($roomLocation)) {
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }
    
    // Validate room name
    if (!isValidRoomName($roomId, $roomName, $pdo)) {
        echo json_encode(["error" => "Room name already exists. Please choose another."]);
        exit;
    }

    // Update the room data in the database
    try {
        $pdo->beginTransaction();

        $updateQuery = "UPDATE ROOM SET room_name = :roomName, capacity = :capacity, room_location = :roomLocation WHERE room_ID = :roomId";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->execute(['roomName' => $roomName, 'capacity' => $capacity, 'roomLocation' => $roomLocation, 'roomId' => $roomId]);

        if ($stmt->errorInfo()[0] != '00000') {
            echo json_encode(["error" => "SQL error: " . implode(", ", $stmt->errorInfo())]);
            $pdo->rollBack();
            exit;
        }

        $pdo->commit();
        echo json_encode(["success" => "Room updated successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => "Failed to update room: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

// Function to validate room name
function isValidRoomName($roomId, $roomName, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ROOM WHERE room_name = ? AND room_ID != ?");
    $stmt->execute([$roomName, $roomId]);
    return $stmt->fetchColumn() == 0;
}
?>
