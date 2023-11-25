<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../Pages/login_page.php');
    exit;
}

if ($_SESSION['user_role'] !== 'Admin' and $_SESSION['user_role'] !== 'Guarantor') {
    header('Location: ../Pages/main_page.php');
    exit;
}

$subs = [];

require '../../Database/db_connect.php';
$stmt = $pdo->prepare("SELECT username, title, subj_description, subject_ID FROM SUBJECTS AS s JOIN USERS AS u ON s.guarantor_ID = u.user_ID WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_SESSION['post_data'])) {
    unset($_SESSION['post_data']);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Subjects Overview</title>
    <link rel="stylesheet" href="../../Styles/style.css">
    <link rel="stylesheet" href="../../Styles/guarant_style.css">
    <link rel="stylesheet" href="../../Styles/anotations_style.css">
    <link rel="stylesheet" href="../../Styles/sidebar_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <!-- Overlay -->
    <div class="overlay hidden" onclick="toggleSidebar()"></div>
    <?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) : ?>
        <!-- Log In Button -->
        <div class="login-button-container">
            <a href="../Pages/login_page.php" class="login-button">Log In</a>
        </div>
    <?php else : ?>
        <!-- Sidebar Toggle Icon -->
        <div class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </div>
        <!-- Include Sidebar Component -->
        <?php include '../../Components/sidebar_component.php'; ?>
    <?php endif; ?>

    <div class="main-container">
        <!-- Content Area -->
        <div class="container">
        <div class="title">Guaranted Subjects</div>

        
            <div class="sub-container">

                <?php
                foreach ($subs as $row) :
                ?>
                    <div class="sub-window">
                        <h3><?= $row['title'] ?></h3>
                        <p><?= $row['subj_description'] ?></p>
                        <div class="button-container">
                            <a href="activity_page.php?subject_id=<?= $row['subject_ID'] ?>" class="sub-btn">Activities</a>
                        </div>
                    </div>
                <?php
                endforeach;
                ?>

            </div>
        </div>
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
</body>

</html>