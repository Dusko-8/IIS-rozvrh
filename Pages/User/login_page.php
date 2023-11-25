<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../../Styles/style.css">
    <link rel="stylesheet" href="../../Styles/login_style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-success">
                    <?= $_SESSION['error']; ?>
                </div>
                <script>
                    setTimeout(function () {
                        document.querySelector('.alert-success').style.display = 'none';
                    }, 5000); // 5000 milliseconds (5 seconds)
                </script>
                <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="../../Process/UserProcess/process_login.php" method="post">
            <div class="error" id="error-msg"></div> 
            
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>
        </form>
        <div class="btn-group">
            <button class="secondary-btn" onclick="location.href='../../Pages/User/register_page.php'">Register</button>
            <button class="secondary-btn" onclick="location.href='../../Pages/User/anotations_page.php'">See Subjects</button>
        </div>
    </div>
</body>
</html>

