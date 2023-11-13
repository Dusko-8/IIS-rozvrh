<?php
include 'db_connect.php'; // Assuming your connection script is named db_connect.php

try {
    $pdo->query('SELECT 1'); // Simple query to test the connection
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>