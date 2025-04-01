<?php


include_once '../config.inc';

    if (isset($_POST['request'])){
        // Sanitize inputs to prevent SQL injection
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $organization_id = mysqli_real_escape_string($conn, $_POST['organization_id']);
        
        // Use prepared statement to prevent SQL injection
        $stmt = mysqli_prepare($conn, "INSERT INTO `complaints` (`id`, `first_name`, `message`, `organization_id`) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $id, $username, $message, $organization_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        header('Location: ./complaints.php');
    }
?>
