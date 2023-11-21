<?php
session_start();
require '../Database/db_connect.php';

$username = $password = $email = $user_role = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $user_role = $_POST['user_role'];
    $password_confirmation = $_POST['password_confirmation'];

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM USERS WHERE username = ?');
    $stmt->execute([$username]);
    $usernameExists = $stmt->fetchColumn() > 0;

    if ($usernameExists) {
        $error = "Username already exists. Please choose another.";
    }elseif($password !== $password_confirmation) {
        $error = "Passwords do not match.";
    }elseif (!isValidPassword($password)) {
        $error = "Password must be at least 5 characters long, include a number and a capital letter.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO USERS (username, hashed_password, email, user_role) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $hashed_password, $email, $user_role]);
            $_SESSION['message'] = "Registration successful!";
            header('Location: login_page.php');
            exit();
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}

function isValidPassword($password) {
    return strlen($password) >= 5 && preg_match('/[A-Z]/', $password) && preg_match('/[0-9]/', $password);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link rel="stylesheet" href="../Styles/style.css">
    <link rel="stylesheet" href="../Styles/register_page_style.css">
</head>

<body>
    <div class="register-container">
    <div class="login-button-container">
        <a href="../Pages/login_page.php" class="login-button">Log In</a>
    </div>
        <h2>Register</h2>
        <?php if($error): ?>
            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="register_page.php" method="post">
            <label for="modal_username">Username:</label>
            <input type="text" id="modal_username" name="username" placeholder="Username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">

            <label for="modal_password">Password:</label>
            <input type="password" id="modal_password" name="password" placeholder="Password" required>

            <label for="modal_password_confirmation">Confirm Password:</label>
            <input type="password" id="modal_password" name="password_confirmation" placeholder="Confirm Password" required>

            <label for="modal_email">Email:</label>
            <input type="email" id="modal_email" name="email" placeholder="Email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="modal_role">Role:</label>
            <select id="modal_role" name="user_role" required>
                <option value="Teacher" <?php echo (isset($_POST['user_role']) && $_POST['user_role'] == 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                <option value="Scheduler" <?php echo (isset($_POST['user_role']) && $_POST['user_role'] == 'Scheduler') ? 'selected' : ''; ?>>Scheduler</option>
                <option value="Student" <?php echo (isset($_POST['user_role']) && $_POST['user_role'] == 'Student') ? 'selected' : ''; ?>>Student</option>
            </select> 

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
