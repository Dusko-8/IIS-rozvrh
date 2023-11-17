<?php
session_start();
session_destroy(); // Destroy the session
header('Location: ../Pages/login_page.php'); // Redirect to login page
exit;
?>

