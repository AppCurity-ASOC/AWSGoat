<?php

include_once '../config.inc';

if (isset($_POST['update_user'])){
    $fname = isset($_REQUEST['inputfirstname']) ? $_REQUEST['inputfirstname'] : '';
    $lname = isset($_REQUEST['inputlastname']) ? $_REQUEST['inputlastname'] : '';
    $phone = isset($_REQUEST['inputphone']) ? $_REQUEST['inputphone'] : '';
    $email = isset($_REQUEST['inputEmail']) ? $_REQUEST['inputEmail'] : '';
    $address = isset($_REQUEST['inputAddress']) ? $_REQUEST['inputAddress'] : '';
    $ssn = isset($_REQUEST['inputssn']) ? $_REQUEST['inputssn'] : '';
    $bank = isset($_REQUEST['inputbank']) ? $_REQUEST['inputbank'] : '';
    $npass = isset($_REQUEST['inputnewPassword']) ? $_REQUEST['inputnewPassword'] : '';
    $cpass = isset($_REQUEST['inputcnfPassword']) ? $_REQUEST['inputcnfPassword'] : '';

    if ((!empty($fname)) && (!empty($lname)) && (!empty($email)) && (!empty($address)) && (!empty($ssn))) {
        // Prepare statements to prevent SQL injection
        $stmt1 = $conn->prepare("UPDATE `users_info` SET `first_name` = ?, `last_name` = ?, `phone` = ?, `email` = ?, `address` = ?, `ssn` = ?, `bank_account` = ? WHERE id = (SELECT id from users where username = ?)");
        $stmt1->bind_param("ssssssss", $fname, $lname, $phone, $email, $address, $ssn, $bank, $_SESSION['username']);
        $upload1 = $stmt1->execute();
        $stmt1->close();
        
        $stmt2 = $conn->prepare("UPDATE `users` SET `email` = ? where id =(SELECT id from users where username = ?)");
        $stmt2->bind_param("ss", $email, $_POST['username']);
        $upload2 = $stmt2->execute();
        $stmt2->close();

        if ((!empty($npass)) && (!empty($cpass))) {
            if (($npass == $cpass)) {
                // Use secure password hashing with PASSWORD_BCRYPT
                $pass = password_hash($cpass, PASSWORD_BCRYPT);
                $stmt3 = $conn->prepare("UPDATE `users` SET `password` = ? where id = ?");
                $stmt3->bind_param("si", $pass, $userid);
                $upload3 = $stmt3->execute();
                $stmt3->close();
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