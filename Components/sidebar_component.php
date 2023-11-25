<div class="sidebar hidden">
    <h2 class="menu-title">Menu</h2>
    <ul>
        <?php if ($_SESSION['user_role']  == "Admin") { ?>
            <li><a href="../../Pages/Admin/manage_users_page.php">Manage users</a></li>
            <li><a href="../../Pages/Admin/manage_rooms_page.php">Manage rooms</a></li>
            <li><a href="../../Pages/Admin/manage_subjects_page.php">Manage subjects</a></li>
        <?php } ?>
        <?php if ($_SESSION['user_role'] == "Guarantor" or $_SESSION['user_role'] == "Admin") { ?>
            <li><a href="../Pages/guaranted_sub_page.php">Guaranted Subjects</a></li>
        <?php } ?>
        <li><a href="../../Pages/User/anotations_page.php">Subjects anotations</a></li>
    </ul>
    <div class="logout-btn">
        <a href="../../Process/UserProcess/process_logout.php">Logout</a>
    </div>
</div>