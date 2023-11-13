<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; ?></div>
            <?php unset($_SESSION['error']); // Unset the error message ?>
        <?php endif; ?>
        <form action="process_login.php" method="post">
            <div class="error" id="error-msg"></div> <!-- Error message placeholder -->
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="btn-group">
            <button class="secondary-btn" onclick="location.href='register_page.php'">Register</button>
            <button class="secondary-btn" onclick="location.href='anotations_page.php'">See Subjects</button>
        </div>
    </div>
</body>
</html>

