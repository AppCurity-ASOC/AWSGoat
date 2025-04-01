<?php

include_once '../config.inc';

    if (isset($_POST['save_leave_status'])){
        $response = mysqli_real_escape_string($conn, $_POST['review']);
        $leaveid = mysqli_real_escape_string($conn, $_POST['leave_id']);
        $query = "UPDATE `leave_applications` SET `status` = ? WHERE `leave_id`= ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $response, $leaveid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: ./leave-application.php');
    }
    if (isset($_POST['delete_leave_status'])){
        $leaveid = mysqli_real_escape_string($conn, $_POST['leave_id']);
        $query = "DELETE FROM `leave_applications` WHERE `leave_id` = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $leaveid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: ./leave-application.php');
    }
?>
