<?php
session_start();
require '../Database/db_connect.php';
require_once '../Process/process_session_check.php';

$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";
$subjects = [];

if (!empty($searchQuery)) {
    // Fetching subjects with guarantor's name
    $stmt = $pdo->prepare("SELECT SUBJECTS.*, USERS.username as guarantor_name FROM SUBJECTS LEFT JOIN USERS ON SUBJECTS.guarantor_ID = USERS.user_ID WHERE SUBJECTS.title LIKE :title");
    $stmt->execute(['title' => '%'.$searchQuery.'%']);
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Fetching all subjects with guarantor's name
    $stmt = $pdo->prepare("SELECT SUBJECTS.*, USERS.username as guarantor_name FROM SUBJECTS LEFT JOIN USERS ON SUBJECTS.guarantor_ID = USERS.user_ID");
    $stmt->execute();
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Subjects Overview</title>
    <link rel="stylesheet" href="../Styles/anotations_style.css">
    <link rel="stylesheet" href="../Styles/sidebar_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Overlay -->
<div class="overlay hidden" onclick="toggleSidebar()"></div>
<?php if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true): ?>
    <!-- Log In Button -->
    <div class="login-button-container">
        <a href="../Pages/login_page.php" class="login-button">Log In</a>
    </div>
<?php else: ?>
    <!-- Sidebar Toggle Icon -->
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    <!-- Include Sidebar Component -->
    <?php include '../Components/sidebar_component.php'; ?>
<?php endif; ?>
<div class="title">Subjects anotations</div>
<div class="search-form">
     <!-- Search Box -->
    <input type="text" id="searchBox" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input" placeholder="Search by title...">
    <!-- Search Button -->
    <button onclick="searchUser()" class="search-btn">Search</button>
    <!-- Clear Button -->
    <button onclick="clearAndSearch()" class="search-btn">Clear</button>
</div>
<div class="card-container">
    <?php foreach ($subjects as $subject): ?>
        <div class="card">
            <h3><?= htmlspecialchars($subject['title']) ?></h3>
            <hr> <!-- Horizontal line after title -->
            <p><strong>Abbreviation:</strong> <?= htmlspecialchars($subject['abbervation']) ?></p>
            <hr>
            <p><strong>Credits:</strong> <?= htmlspecialchars($subject['credits']) ?> Credits</p>
            <hr>
            <p><strong>Description:</strong> <?= htmlspecialchars($subject['subj_description']) ?></p>
            <hr>
            <p><strong>Guarantor:</strong> <?= htmlspecialchars($subject['guarantor_name']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
    <script>
        function searchUser() {
            const query = document.getElementById('searchBox').value;
            window.location.href = `../Pages/anotations_page.php?search=${encodeURIComponent(query)}`;
        }
        function clearAndSearch() {
            document.getElementById('searchBox').value = '';
            searchUser(); // This will perform a search with an empty query, effectively clearing it.
        }
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('hidden');
            overlay.classList.toggle('show');
        }
    </script>
</body>
</html>
