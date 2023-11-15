<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login_page.php');
    exit;
}

$user_role = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar Toggle Icon -->
    <div class="sidebar-header">
        <!-- Sidebar Toggle -->
        <div class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </div>
    </div>
    <!-- Overlay -->
    <div class="overlay hidden" onclick="toggleSidebar()"></div>

    <div class="main-container">
        <!-- Sidebar Menu -->
        <?php include '../Components/sidebar_component.php'; ?>
        <!-- Content Area -->
        <div class="content">
            <!-- Your main content goes here -->
            <h1>Welcome to the Main Page</h1>
            <p>This is where your main content will be displayed.</p>
        </div>

        <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('hidden');
            // Toggle the 'show' class for the overlay
            overlay.classList.toggle('show');
        }
        </script>

    </div>
</body>
</html>
