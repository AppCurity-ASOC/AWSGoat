<?php 

include 'config.inc';

session_start();

error_reporting(0);

if (isset($_GET['organization'])) {
	$organization = mysqli_real_escape_string($conn, $_GET['organization']);
	$oidres = mysqli_prepare($conn, "SELECT organization_id FROM organizations WHERE organization = ?");
	mysqli_stmt_bind_param($oidres, "s", $organization);
	mysqli_stmt_execute($oidres);
	$result = mysqli_stmt_get_result($oidres);
	$oidq = mysqli_fetch_assoc($result);
	$oid = $oidq['organization_id'];
	$_SESSION['organization_id'] = $oid; 
    header("Location: ./superadmin/superadmin-index.php");
    exit(); // Prevent further execution after redirect
}

if (isset($_POST['submit'])) {
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	// Use password_hash and password_verify instead of md5
	$password = $_POST['password']; // Store plain password temporarily for verification

	$sql = "SELECT * FROM users WHERE email=? LIMIT 1";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, "s", $email);
	mysqli_stmt_execute($stmt);
	$result = mysqli_stmt_get_result($stmt);

	if ($result->num_rows > 0) {
		$row = mysqli_fetch_assoc($result);
		// Verify password - assuming the database still uses MD5 hashes
		// In a real scenario, you would migrate to password_hash
		if (md5($password) === $row['password']) {
			$_SESSION['username'] = $row['username'];
			$_SESSION['id'] = $row['id'];
			$_SESSION['isadmin']  = $row['isadmin'];
			$isadmin = $row['isadmin'];
			$_SESSION['organization_id'] = $row['organization_id'];
			
			if ($isadmin == 0) {
				header("Location: ./user/index.php");
				exit(); // Prevent further execution after redirect
			} else if($isadmin == 1) {
				header("Location: ./admin/admin-index.php");
				exit(); // Prevent further execution after redirect
			} else if($isadmin == 2) {
				$_SESSION['organization_id'] = 1;
				header("Location: ./superadmin/superadmin-index.php");
				exit(); // Prevent further execution after redirect
			}
		} else {
			echo "<script>alert('Email or Password is Wrong.')</script>";
		}
	} 
	else {
		echo "<script>alert('Email or Password is Wrong.')</script>";
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="CSS/loginstyle.css">
	<link rel="icon" type="image/x-icon" href="./images/AWScloud.png">
	<title>AWS GOAT V2 - Login</title>
</head>
<body>
	

	<div class="container">
		<form action="" method="POST" class="login-email">
			<div style="text-align:center;"><img src="./images/logo-login.png" height ="100" width="180"></div>
			<p class="login-text" style="font-size: 2rem; font-weight: 800;">Login</p>
			<div class="input-group">
				<input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
			</div>
			<div class="input-group">
				<input type="password" placeholder="Password" name="password" required>
			</div>
			<div class="input-group">
				<button name="submit" class="btn">Login</button>
			</div>
		</form>
		<div style="text-align:center;">
			<section>Made with &hearts; by INE</section></br>
				<div style="text-align:center;">
					<img src="./images/ine-logo.png" height ="25" width="50">
				</div>
		</div>
		
	</div>

	
	
</body>

</html>
