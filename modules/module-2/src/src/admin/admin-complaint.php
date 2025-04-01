<?php

include_once '../config.inc';

    if (isset($_POST['submit'])){
        $compid = mysqli_real_escape_string($conn, $_POST['complaint_id']);
        $remark = mysqli_real_escape_string($conn, $_POST['remark']);
        $query = "UPDATE `complaints` SET `remark` = ? where complaint_id = ?";
        
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $remark, $compid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        header('Location: complaints.php');
        exit(); // Add exit after redirect
    }

    if (isset($_POST['delete'])){
        $compid = mysqli_real_escape_string($conn, $_POST['complaint_id']);
        $query = "DELETE FROM `complaints` WHERE complaint_id = ?";
        
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $compid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        header('Location: complaints.php');
        exit(); // Add exit after redirect
    }
?>
