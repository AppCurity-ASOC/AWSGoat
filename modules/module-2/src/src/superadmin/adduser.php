<?php

include_once '../config.inc';

// Sanitize and validate inputs
$username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
$email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
// Use password_hash instead of md5 for secure password hashing
$password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT, ['cost' => 12]);
$isadmin = mysqli_real_escape_string($conn, $_POST['isadmin'] ?? '');

$organization_id = mysqli_real_escape_string($conn, $_POST['organization_id'] ?? '');

// Use prepared statements to prevent SQL injection
$query = "INSERT INTO `users` (`organization_id`, `username`, `email`, `password`, `isadmin`) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssss", $organization_id, $username, $email, $password, $isadmin);
$stmt->execute();
$stmt->close();

$firstname = mysqli_real_escape_string($conn, $_POST['firstname'] ?? '');
$lastname = mysqli_real_escape_string($conn, $_POST['lastname'] ?? '');
$address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
$ssn = mysqli_real_escape_string($conn, $_POST['ssn'] ?? '');
$bank_account = mysqli_real_escape_string($conn, $_POST['bank_account'] ?? '');
$phone = mysqli_real_escape_string($conn, $_POST['phone'] ?? '');

// Use prepared statement to get the user ID
$query3 = "SELECT `id` FROM `users` WHERE `email`= ?";
$stmt = $conn->prepare($query3);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$id = $result->fetch_array()[0] ?? '';
$stmt->close();

// Use prepared statement for the second insert
$query2 = "INSERT INTO `users_info`(
    `id`,
    `first_name`,
    `last_name`,
    `email`,
    `address`,
    `ssn`,
    `bank_account`,
    `phone`,
    `isadmin`
)
VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query2);
$stmt->bind_param("sssssssss", $id, $firstname, $lastname, $email, $address, $ssn, $bank_account, $phone, $isadmin);
$stmt->execute();
$stmt->close();

header('Location: user-settings.php');
?>