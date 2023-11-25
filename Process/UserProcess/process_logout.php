<?php
session_start();
session_destroy(); // Destroy the session
header('Location: ../../Pages/User/login_page.php'); // Redirect to login page
exit;
?>

