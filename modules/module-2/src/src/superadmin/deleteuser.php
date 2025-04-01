<?php

include_once '../config.inc';

if (isset($_POST['delete'])){
    $response = mysqli_real_escape_string($conn, $_POST['email']);
    // Using prepared statements to prevent SQL injection
    $stmt1 = mysqli_prepare($conn, "DELETE FROM `users` WHERE `email`=?");
    $stmt2 = mysqli_prepare($conn, "DELETE FROM `users_info` WHERE `email`=?");
    
    if ($stmt1 && $stmt2) {
        mysqli_stmt_bind_param($stmt1, "s", $response);
        mysqli_stmt_bind_param($stmt2, "s", $response);
        
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_execute($stmt1);
        
        mysqli_stmt_close($stmt1);
        mysqli_stmt_close($stmt2);
        
        header('Location: user-settings.php');
    }
}

?>