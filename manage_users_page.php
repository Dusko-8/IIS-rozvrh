<?php
session_start();

require 'db_connect.php';

// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login_page.php');
    exit;
}

// Check if the user role is not Admin
if ($_SESSION['user_role'] !== 'Admin') {
    header('Location: main_page.php');
    exit;
}

$searchQuery = isset($_GET['search']) ? $_GET['search'] : "";
$users = [];

if (!empty($searchQuery)) {
    $stmt = $pdo->prepare("SELECT * FROM USERS WHERE username LIKE :username");
    $stmt->execute(['username' => '%'.$searchQuery.'%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT * FROM USERS");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <!-- Sidebar Toggle Icon -->
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </div>
    <!-- Overlay -->
    <div class="overlay hidden" onclick="toggleSidebar()"></div>

    <div class="main-container">
        <!-- Sidebar Menu -->
        <?php include 'sidebar_component.php'; ?>
         <!-- Content Area -->
        <div class="content">
        <div class="title">Manage Users</div>
         <!-- Search Bar -->
         <div class="search-form">
             <!-- Search Box -->
            <input type="text" id="searchBox" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input" placeholder="Search by username...">
            <!-- Search Button -->
            <button onclick="searchUser()" class="search-btn">Search</button>
            <!-- Clear Button -->
            <button onclick="clearAndSearch()" class="search-btn">Clear</button>
        </div>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
                            <tr>
                                <td data-column="ID"><?php echo $user['user_ID']; ?></td>
                                <td data-column="Username"><?php echo $user['username']; ?></td>
                                <td data-column="Email"><?php echo $user['email']; ?></td>
                                <td data-column="Role"><?php echo $user['user_role']; ?></td>
                                <td data-column="Actions">
                                    <button class="edit-btn" onclick="openEditModal(<?php echo $user['user_ID']; ?>)">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="delete-btn" onclick="deleteUser(<?php echo $user['user_ID']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <div id="editModal" class="modal">
                <div class="modal-content">
                    <input type="text" name="username" placeholder="Username" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <div class="email-role-group">
                        <input type="email" name="email" placeholder="Email" required>
                        <select name="user_role" required>
                            <option value="Teacher">Teacher</option>
                            <option value="Scheduler">Scheduler</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <button onclick="saveChanges()" class="save-btn">Save changes</button>
                    <button onclick="closeModal()" class="save-btn">Close</button>
                </div>
            </div>
        </div>
        <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('hidden');
            overlay.classList.toggle('hidden');
        }
        function searchUser() {
        const query = document.getElementById('search').value;
        window.location.href = `your_page.php?search=${query}`;
        }
        function clearAndSearch() {
            document.getElementById('searchBox').value = '';
            searchUser(); // This will perform a search with an empty query, effectively clearing it.
        }
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = `delete_user_process.php?id=${userId}`;
            }
        }
        function openEditModal(userId) {
            // Fetch user data from the server (you need to replace this with actual AJAX code)
            const user = { /* fetch user data based on userId */ };

            document.getElementById('editModal').style.display = "block";
            document.getElementById('username').value = user.username; // make sure this is fetched
            document.getElementById('email').value = user.email;  // make sure this is fetched
        }

        function closeModal() {
            document.getElementById('editModal').style.display = "none";
        }
        
        </script>

    </div>
</body>
</html>
