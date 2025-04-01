<?php
include_once '../config.inc';

// Sanitize inputs using prepared statements
$stmt = $conn->prepare("INSERT INTO users (organization_id, username, email, password, isadmin) VALUES (?, ?, ?, ?, ?)");

// Validate and sanitize inputs
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]); // Using secure password hashing
$isadmin = filter_input(INPUT_POST, 'isadmin', FILTER_SANITIZE_STRING);
$organization_id = filter_input(INPUT_POST, 'organization_id', FILTER_SANITIZE_NUMBER_INT);

// Bind parameters and execute first query
$stmt->bind_param("sssss", $organization_id, $username, $email, $password, $isadmin);
$stmt->execute();

// Sanitize additional inputs
$firstname = filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING);
$lastname = filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
$ssn = filter_input(INPUT_POST, 'ssn', FILTER_SANITIZE_STRING);
$bank_account = filter_input(INPUT_POST, 'bank_account', FILTER_SANITIZE_STRING);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);

// Get user ID using prepared statement
$stmt2 = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt2->bind_param("s", $email);
$stmt2->execute();
$result = $stmt2->get_result();
$id = $result->fetch_array()[0] ?? '';

// Insert user info using prepared statement
$stmt3 = $conn->prepare("INSERT INTO users_info (id, first_name, last_name, email, address, ssn, bank_account, phone, isadmin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt3->bind_param("sssssssss", $id, $firstname, $lastname, $email, $address, $ssn, $bank_account, $phone, $isadmin);
$stmt3->execute();

// Close statements
$stmt->close();
$stmt2->close();
$stmt3->close();

header('Location: user-settings.php');
?>