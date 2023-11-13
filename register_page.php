<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="register-container">
        <h2>Register</h2>
        <form action="process_registration.php" method="post">
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
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>
