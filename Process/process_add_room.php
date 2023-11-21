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
    if (empty($roomName) || empty($capacity) || empty($roomLocation)) {
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    // Validate room name
    if (!isValidRoomName($roomName, $pdo)) {
        echo json_encode(["error" => "Room name already exists. Please choose another."]);
        exit;
    }

    // Insert the new room data into the database
    try {
        $pdo->beginTransaction();

        $insertQuery = "INSERT INTO ROOM (room_name, capacity, room_location) VALUES (:roomName, :capacity, :roomLocation)";
        $stmt = $pdo->prepare($insertQuery);
        $stmt->execute(['roomName' => $roomName, 'capacity' => $capacity, 'roomLocation' => $roomLocation]);

        if ($stmt->errorInfo()[0] != '00000') {
            echo json_encode(["error" => "SQL error: " . implode(", ", $stmt->errorInfo())]);
            $pdo->rollBack();
            exit;
        }

        $pdo->commit();
        $_SESSION['alert_success'] = "Room added successfully";
        echo json_encode(["success" => "Room added successfully"]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(["error" => "Failed to add room: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["error" => "Invalid request method"]);
}

// Function to validate room name
function isValidRoomName($roomName, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM ROOM WHERE room_name = ?");
    $stmt->execute([$roomName]);
    return $stmt->fetchColumn() == 0;
}
?>
