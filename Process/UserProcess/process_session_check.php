<?php

if (isset($_SESSION['last_activity'])) {
    $timeSinceLastActivity = time() - $_SESSION['last_activity'];

    $timeout = 30 * 60;

    if ($timeSinceLastActivity > $timeout) {

        session_unset();  // unset $_SESSION variables
        session_destroy();  // destroy session data

        header('Location: login_page.php');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>