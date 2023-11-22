<?php
session_start();
require '../../Database/db_connect.php';
echo '<label for="activityDate">Date:</label>';
echo '<input type="date" id="activityDate" name="activityDate" required onchange="loadDateSchedule()">';
echo '<div id="room_date_schedule"></div>';
?>

<style>
    input[type="date"] {
        padding: 8px; 
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
        margin-bottom: 10px;
        display: block;
        margin: auto;
        text-align: center;
    }

    label{
        display: block;
        margin: auto;
        text-align: center;
    }
</style>