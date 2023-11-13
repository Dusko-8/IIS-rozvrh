<div class="sidebar">
    <h2 class="menu-title">Menu</h2>
    <ul>
        <?php if ($_SESSION['user_role']  == "Admin") { ?>
            <li><a href="manage_users_page.php">Manage users</a></li>
        <?php } ?>
        <li><a href="manage_users_page.php">Manage users</a></li>
        <li><a href="manage_users_page.php">Manage users</a></li>
        <li><a href="manage_users_page.php">Manage users</a></li>
        <li><a href="manage_users_page.php">Manage users</a></li>
    </ul>
    <div class="logout-btn">
        <a href="process_logout.php">Logout</a>
    </div>
</div>