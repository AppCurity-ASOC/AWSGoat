<?php
include_once '../config.inc';

if (isset($_POST['update_user'])){
    // Sanitize inputs using prepared statements
    $fname = mysqli_real_escape_string($conn, $_POST['inputfirstname']);
    $lname = mysqli_real_escape_string($conn, $_POST['inputlastname']);
    $phone = mysqli_real_escape_string($conn, $_POST['inputphone']);
    $email = mysqli_real_escape_string($conn, $_POST['inputEmail']);
    $address = mysqli_real_escape_string($conn, $_POST['inputAddress']);
    $ssn = mysqli_real_escape_string($conn, $_POST['inputssn']);
    $bank = mysqli_real_escape_string($conn, $_POST['inputbank']);
    $npass = $_POST['inputnewPassword'];
    $cpass = $_POST['inputcnfPassword'];
    $username = mysqli_real_escape_string($conn, $_SESSION['username']);

    if ((!empty($fname)) && (!empty($lname)) && (!empty($email)) && (!empty($address)) && (!empty($ssn))) {
        // Use prepared statements to prevent SQL injection
        $stmt1 = $conn->prepare("UPDATE users_info SET first_name = ?, last_name = ?, phone = ?, email = ?, address = ?, ssn = ?, bank_account = ? WHERE id = (SELECT id FROM users WHERE username = ?)");
        $stmt1->bind_param("ssssssss", $fname, $lname, $phone, $email, $address, $ssn, $bank, $username);
        $upload1 = $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE users SET email = ? WHERE id = (SELECT id FROM users WHERE username = ?)");
        $stmt2->bind_param("ss", $email, $username);
        $upload2 = $stmt2->execute();

        if ((!empty($npass)) && (!empty($cpass))) {
            if (($npass == $cpass)) {
                // Use PASSWORD_BCRYPT for secure password hashing
                $pass = password_hash($cpass, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt3 = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt3->bind_param("si", $pass, $userid);
                $upload3 = $stmt3->execute();
                header('Location: ../logout.php');
                exit;
            }
        }

        header('Location: user-settings.php');
        exit;
    } else {
        $_SESSION['errorMsg'] = "Only Phone & Bank details can be blank!";
        header('Location: user-settings.php');
        exit;
    }
}
?>