<?php
session_start();
require '../../Database/db_connect.php';

// TIME SELECT FOR ONE TIME ACTIVITY
echo '<label for="activityDate" style ="text-align:center;">Date:</label>';
echo '<input type="date" id="activityDate" name="activityDate" required onchange="loadDateSchedule()">';
echo '<div id="room_date_schedule"></div>';
?>