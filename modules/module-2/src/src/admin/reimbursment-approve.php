<?php

include_once '../config.inc';

    if (isset($_POST['save_rem_status'])){
        $response = mysqli_real_escape_string($conn, $_POST['review']);
        $remid = mysqli_real_escape_string($conn, $_POST['reimbursment_id']);
        $query = "UPDATE `reimbursments` SET `status` = ? WHERE `reimbursment_id`= ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ss', $response, $remid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: reimbursment.php');
    }
    if (isset($_POST['delete_rem_status'])){
        $remid = mysqli_real_escape_string($conn, $_POST['reimbursment_id']);
        $query = "DELETE FROM `reimbursments` WHERE `reimbursment_id`= ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $remid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: reimbursment.php');
    }
?>
