﻿<?php
session_start();

require '../../Database/db_connect.php';
require_once '../../Process/UserProcess/process_session_check.php';
// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../../Pages/login_page.php');
    exit;
}

// Check if the user role is not Admin
if ($_SESSION['user_role'] !== 'Admin') {
    header('Location: ../../Pages/User/anotations_page.php');
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
    <link rel="stylesheet" href="../../Styles/style.css">
    <link rel="stylesheet" href="../../Styles/manage_users_style.css">
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
        <?php include '../../Components/sidebar_component.php'; ?>
         <!-- Content Area -->
        <div class="content">
            <div class="title">Manage Users</div>
            <?php if (isset($_SESSION['alert_failure'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['alert_failure']; ?>
                </div>
                <?php unset($_SESSION['alert_failure']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['alert_success'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['alert_success']; ?>
                </div>
                <script>
                    // Automatically close the success message after 5 seconds
                    setTimeout(function () {
                        document.querySelector('.alert-success').style.display = 'none';
                    }, 5000); // 5000 milliseconds (5 seconds)
                </script>
                <?php unset($_SESSION['alert_success']); ?>
            <?php endif; ?>
             <!-- Search Bar -->
            <div class="search-form">
                <input type="text" id="searchBox" value="<?php echo htmlspecialchars($searchQuery); ?>" class="search-input" placeholder="Search by username...">
                <button onclick="searchUser()" class="search-btn">Search</button>
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
                    <form id="editUserForm">
                        <h2 class="modal-title">Edit User Details</h2>
                        <input type="hidden" name="userId" id="modal_userId">
                                    
                        <label for="modal_username">* Username</label>
                        <input type="text" name="username" placeholder="Username" id="modal_username" required maxlength="50">
                                    
                        <label for="modal_password">* Password</label>
                        <input type="password" name="password" placeholder="Password" id="modal_password" maxlength="255">
                        
                        <label for="modal_password_confirmation">* Confirm Password</label>
                        <input type="password" id="modal_password_conf" name="password_confirmation" placeholder="Confirm Password"  maxlength="255">

                        <label for="modal_email">* Email</label>
                        <input type="email" name="email" placeholder="Email" id="modal_email" required maxlength="50">
            
                        <label for="modal_role">* User Role</label>
                        <select name="user_role" id="modal_role" required>
                            <option value="Teacher">Teacher</option>
                            <option value="Scheduler">Scheduler</option>
                            <option value="Guarantor">Guarantor</option>
                            <option value="Student">Student</option>
                            <option value="Admin">Admin</option>
                        </select>
                                    
                        <p id="modal_notification" style="color: green; display: none;"></p>
                                    
                        <button type="submit" class="save-btn">Save changes</button>
                        <button type="button" onclick="closeModal()" class="save-btn">Close</button>
                    </form>
                </div>
            </div>
        </div>
        <script>

        let originalUserData = {
            userID: '',
            username: '',
            email: '',
            user_role: ''
        };

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.overlay');

            sidebar.classList.toggle('hidden');
            overlay.classList.toggle('show');
        }
        function searchUser() {
            const query = document.getElementById('searchBox').value;
            window.location.href = `../../Pages/Admin/manage_users_page.php?search=${encodeURIComponent(query)}`;
        }
        function clearAndSearch() {
            document.getElementById('searchBox').value = '';
            searchUser(); 
        }
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = `../../Process/AdminProcess/process_delete_user.php?id=${userId}`;
            }
        }
        function openEditModal(userId) {
            // Fetch user data from the server
            fetch(`../../Process/AdminProcess/process_user_by_id.php?id=${userId}`)
                .then(response => response.json())
                .then(user => {    
                    console.log("Retrieved user:", user);
                
                    // Display the modal
                    document.getElementById('editModal').style.display = "block";
                
                    // Populate the modal form fields with the user's data
                    document.getElementById('modal_username').value = user.username;
                    document.getElementById('modal_email').value = user.email;
                    document.getElementById('modal_role').value = user.user_role;
                    document.getElementById('modal_userId').value = userId;
                    // Update the originalUserData object
                    originalUserData.userID = userId;
                    originalUserData.username = user.username;
                    originalUserData.email = user.email;
                    originalUserData.user_role = user.user_role;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }


        function closeModal() {
            document.getElementById('editModal').style.display = "none";
            document.getElementById('modal_notification').innerText = '';
            document.getElementById('modal_notification').style.display = 'none';
            location.reload();
        }
        
        document.getElementById('editUserForm').addEventListener('submit', function(event) {
            event.preventDefault();
            saveChanges();
        });
        
        function saveChanges() {
            const formData = new FormData(document.getElementById('editUserForm'));
            if (!validatePasswordMatch()) {
                document.getElementById('modal_notification').innerText = 'Passwords do not match';
                document.getElementById('modal_notification').style.display = 'block';
                return; 
            }
            // Check if any changes were made
            const currentUsername = formData.get('username');
            const currentEmail = formData.get('email');
            const currentRole = formData.get('user_role');
            const password = formData.get('password');

            if (currentUsername !== originalUserData.username ||
                currentEmail !== originalUserData.email ||
                currentRole !== originalUserData.user_role ||
                password !== '') {
                
                console.log('Changes detected, saving...');
                console.log(formData.get('user_Id'));

                fetch('../../Process/AdminProcess/process_edit_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.error) {
                        document.getElementById('modal_notification').innerText = data.error;
                        document.getElementById('modal_notification').style.display = 'block';
                    } else {
                        document.getElementById('modal_notification').innerText = 'Changes saved successfully!';
                        document.getElementById('modal_notification').style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('modal_notification').innerText = 'Error saving changes';
                    document.getElementById('modal_notification').style.display = 'block';
                });
            
            } else {
                console.log('No changes detected');
                document.getElementById('modal_notification').innerText = 'No changes to save.';
                document.getElementById('modal_notification').style.display = 'block';
            }
        }

        function validatePasswordMatch() {
            var password = document.getElementById('modal_password').value;
            var confirmPassword = document.getElementById('modal_password_conf').value;

            if (password !== confirmPassword) {
                return false; // Prevents form submission
            }
            return true;
        }
        </script>

    </div>
</body>
</html>
