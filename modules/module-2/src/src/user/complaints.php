<?php

include_once '../config.inc';
session_start();

if ((!isset($_SESSION['username']))) {
    header("Location: ../login.php");
    exit;
}

if($_SESSION['isadmin'] == 1 || $_SESSION['isadmin'] == 2){
    header("Location: ../logout.php");  
}

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT * from users_info where id =(SELECT id from users where username = ?);");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();
$userid = $_SESSION['id'];
$resultname = mysqli_fetch_assoc($result);
$firstname = $resultname['first_name'];

if (isset($_POST['submit'])) {
    $fname = trim($_POST['inputfirstname']);
    $lname = trim($_POST['inputlastname']);
    $phone = trim($_POST['inputphone']);
    $email = trim($_POST['inputEmail']);
    $address = trim($_POST['inputAddress']);
    $ssn = trim($_POST['inputssn']);
    $bank = trim($_POST['inputbank']);
    $npass = $_POST['inputnewPassword'];
    $cpass = $_POST['inputcnfPassword'];
    $uid = (int)$_POST['uid'];

    if ((!empty($fname)) && (!empty($lname)) && (!empty($email)) && (!empty($address)) && (!empty($ssn))) {
        // Use prepared statements to prevent SQL injection
        $upq = $conn->prepare("UPDATE `users_info` SET `first_name` = ?, `last_name` = ?, `phone` = ?, `email` = ?, `address` = ?, `ssn` = ?, `bank_account` = ? WHERE id = ?");
        $upq->bind_param("sssssssi", $fname, $lname, $phone, $email, $address, $ssn, $bank, $uid);
        $upload1 = $upq->execute();
        
        $upq2 = $conn->prepare("UPDATE `users` SET `email` = ? where id = ?");
        $upq2->bind_param("si", $email, $uid);
        $upload2 = $upq2->execute();

        if ((!empty($npass)) && (!empty($cpass))) {
            if (($npass == $cpass)) {
                // Use secure password hashing with PASSWORD_BCRYPT
                $pass = password_hash($cpass, PASSWORD_BCRYPT);
                $upq3 = $conn->prepare("UPDATE `users` SET `password` = ? where id = ?");
                $upq3->bind_param("si", $pass, $uid);
                $upload3 = $upq3->execute();
                header('Location: ../logout.php');
                exit;
            }
            else{
                echo "<script>alert('Confirm Password is Wrong!')</script>";
            }
        }

        header('Location: index.php');
        exit;
    } else {
        $_SESSION['errorMsg'] = "Only Phone & Bank details can be blank!";
        header('Location:index.php');
        exit;
    }
} else if (isset($_REQUEST['apply'])) {
    $leavetype = trim($_REQUEST['leavetype']);
    $fromdate = trim($_REQUEST['fromdate']);
    $todate = trim($_REQUEST['todate']);
    $inputreason = trim($_REQUEST['inputreason']);

    if ((!empty($leavetype)) && (!empty($fromdate))) {
        // Use prepared statement to prevent SQL injection
        $queryleaveinsert = $conn->prepare("INSERT INTO `leave_applications`(`first_name`, `id`,`leave_type`,`from_date`,`to_date`,`reason`) VALUES(?,?,?,?,?,?)");
        $queryleaveinsert->bind_param("sissss", $firstname, $userid, $leavetype, $fromdate, $todate, $inputreason);
        $upload4 = $queryleaveinsert->execute();
    } else {
        header('Location:leave-application.php');
        exit;
    }

    header('Location: leave-application.php');
    exit;
} else if (isset($_REQUEST['request'])) {

    if( $_FILES['file']['name'] != "" ) {
        $currentDirectory = getcwd();
        $uploadDirectory = "../uploads/";
        
        // Sanitize filename and generate a safe unique name
        $originalFileName = basename($_FILES['file']['name']);
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        $safeFileName = uniqid('file_') . '.' . $fileExtension;
        
        $uploadPath = $currentDirectory . $uploadDirectory . $safeFileName;
        
        // Validate file type (optional additional security)
        $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
        if (!in_array(strtolower($fileExtension), $allowedTypes)) {
            die("File type not allowed!");
        }
        
        move_uploaded_file( $_FILES['file']['tmp_name'], $uploadPath) or die( "Could not copy file!");
    }
    else {
        die("No file specified!");
    }
    $remtype = trim($_REQUEST['remtype']);
    $filedon = trim($_REQUEST['filedon']);
    $amount = trim($_REQUEST['amount']);


    if ((!empty($remtype)) && (!empty($filedon)) && (!empty($amount))) {
        // Use prepared statement to prevent SQL injection
        $queryreminsert = $conn->prepare("INSERT INTO `reimbursments` (`id`,`first_name`,`type`,`filed_on`,`amount`) VALUES(?,?,?,?,?)");
        $queryreminsert->bind_param("issss", $userid, $firstname, $remtype, $filedon, $amount);
        $upload5 = $queryreminsert->execute();
    } else {
        header('Location: reimbursment.php');
        exit;
    }

    header('Location: reimbursment.php');
    exit;
}




?>





<!DOCTYPE html>
<html lang="en">

<head>
    <title>AWS GOAT V2 - Lodge Complaints</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../images/AWScloud.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-0evHe/X+R7YkIZDRvuzKMRqM+OrBnVFBL6DOitfPri4tjfHxaWutUpFmBp4vmVor" crossorigin="anonymous">
    
    <link rel="stylesheet" href="../CSS/styles.css">
</head>

<body>

    <!-- Navbar & Menus -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-main-head">
            <div class="container-fluid navbar-bundle">
                <div class="nav-flex-container">
                    <a class="navbar-brand" href="index.php"><img src="../images/AWScloud.png" height="40" width="60"> &nbsp; <img src="../images/logo.png" height="25" width="120"></a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>

                <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <div class="dropdown profile">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown2" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img id="profileimg" src="../images/pic.png" height="30" width="30">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#myModal"><i class="fa fa-user"></i> Profile</a>
                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#settingsModal"><i class="fa fa-gear"></i> Settings</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="../logout.php"><i class="fa fa-arrow-right-from-bracket"></i> Logout</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
            <div class="navlinks" style="width: 260px;">
                <ul>
                    <li><a href="./index.php" type="button" data-page="homepage" class="navlink"><b></b><b></b><i class="fa fa-home fa-xl"></i><span class="title">Home</span></a></li>
                    <li><a href="./payslips.php" type="button" data-page="payslippage" class="navlink"><b></b><b></b><i class="fa fa-file-invoice-dollar fa-xl"></i><span class="title">Payslips</span></a></li>
                    <li><a href="./leave-application.php" type="button" data-page="leaveapplicationpage" class="navlink"><b></b><b></b><i class="fa fa-calendar fa-xl"></i></i><span class="title">Leave Applications</span></a></li>
                    <li><a href="./reimbursment.php" type="button" data-page="reimbursmentpage" class="navlink"><b></b><b></b><i class="fa fa-money-check-dollar fa-xl"></i><span class="title">Reimbursements</span></a></li>
                    <li><a href="#" type="button" data-page="complaintspage" class="navlink active"><b></b><b></b><i class="fa fa-ticket-simple fa-xl"></i><span class="title">Complaints</span></a></li>
                </ul>
            </div>
        </nav>

        <footer>
            <div class="waves">
                <div class="wave" id="wave1"></div>
                <div class="wave" id="wave2"></div>
                <div class="wave" id="wave3"></div>
                <div class="wave" id="wave4"></div>
            </div>
        </footer>

    </header>


    <div class="profilewrapper">
        <?php
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * from users_info where id =(SELECT id from users where username = ?);");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        ?>
        <div class="modal fade" id="myModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="profile_info_text">Profile</h1>
                    </div>
                    <div class="modal-body">
                        <?php if ($result->num_rows > 0) {
                            $row = mysqli_fetch_assoc($result); ?>
                            <div class="card">

                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-6">Name</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']); ?></dd>
                                        <dt class="col-6">Email</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['email']); ?></dd>
                                        <dt class="col-6">Address</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['address']); ?></dd>
                                        <dt class="col-6">Social Security Number</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['ssn']); ?></dd>
                                        <dt class="col-6">Phone</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['phone']); ?></dd>
                                        <dt class="col-6">Bank Account Number</dt>
                                        <dd class="col-6"><?php echo htmlspecialchars($row['bank_account']); ?></dd>
                                    </dl>
                                </div>
                            </div>
                        <?php } else { ?>
                            User not found.
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn " data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="settingswrapper">
        <?php
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * from users_info where id =(SELECT id from users where username = ?);");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();

        ?>
        <div class="modal fade" id="settingsModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="profile_info_text">Settings</h1>
                    </div>
                    <div class="modal-body">

                        <?php if ($result->num_rows > 0) {
                            $row = mysqli_fetch_assoc($result); ?>
                            <form method="POST" action="#">
                                <input type='hidden' name='uid' value="<?php echo (int)$_SESSION['id'] ?>">
                                <div class="form-group row">
                                    <label for="inputfirstname" class="col-sm-4 col-form-label">First Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['first_name']) ?>" id="inputfirstname" name="inputfirstname" placeholder="First Name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputlastname" class="col-sm-4 col-form-label">Last Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['last_name']) ?>" id="inputlastname" name="inputlastname" placeholder="Last Name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputphone" class="col-sm-4 col-form-label">Phone</label>
                                    <div class="col-sm-8">
                                        <input type="tel" class="form-control" value="<?php echo htmlspecialchars($row['phone']) ?>" id="inputphone" name="inputphone" placeholder="Phone Number">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputEmail" class="col-sm-4 col-form-label">Email</label>
                                    <div class="col-sm-8">
                                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($row['email']) ?>" id="inputEmail" name="inputEmail" placeholder="Email">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputAddress" class="col-sm-4 col-form-label">Address</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['address']) ?>" id="inputAddress" name="inputAddress" placeholder="Address">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputssn" class="col-sm-4 col-form-label">SSN</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['ssn']) ?>" id="inputssn" name="inputssn" placeholder="SSN">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputbank" class="col-sm-4 col-form-label">Account Number</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['bank_account']) ?>" id="inputbank" name="inputbank" placeholder="SSN">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputnewPassword" class="col-sm-4 col-form-label">New Password</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="inputnewPassword" name="inputnewPassword" placeholder="New Password">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="inputcnfPassword" class="col-sm-4 col-form-label">Confirm New Password</label>
                                    <div class="col-sm-8">
                                        <input type="password" class="form-control" id="inputcnfPassword" name="inputcnfPassword" placeholder="Confirm Password">
                                    </div>
                                </div>

                                <div class="form-group text-right">
                                    <div class="col-sm-12 ">
                                        <input type="submit" name="submit" class="btn btn-primary" value="Update">
                                    </div>
                                </div>
                            </form>
                        <?php } else { ?>
                            User not found.
                        <?php } ?>
                    </div>
                    <div class="modal-footer">
                        <button class="btn " data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Complaints -->

    <section id="complaintspage" style="margin-left:10px;">

        <div class="complaintswrapper">
            <h4 class="titletext"> Your Complaints</h4>
            <div class="container" style="margin-left:-10px;">
                <ul class="nav nav-tabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#appliedcomplaints">Applied Complaints</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#applycomplaints">Log Complaints</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="appliedcomplaints">
                        <div class="row border g-0  shadow-sm">
                            <div class="col p-4">
                                <div class="table-responsive">
                                    <table class="table bg-white">
                                        <thead class="text-light">
                                            <tr id="tableheadingrem">
                                                <th>Complaint ID</th>
                                                <th>Message</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody class="tablebodyrows">
                                            <?php
                                            // Use prepared statement to prevent SQL injection
                                            $stmt = $conn->prepare("SELECT complaint_id, message, remark FROM `complaints` WHERE first_name = ? LIMIT 7");
                                            $stmt->bind_param("s", $_SESSION['username']);
                                            $stmt->execute();
                                            $comresult = $stmt->get_result();

                                            if (!$comresult) {
                                                die("Invalid Query: ");
                                            }

                                            while ($comrow = $comresult->fetch_assoc()) {
                                                echo "<tr>
                                                    <td>" . htmlspecialchars($comrow["complaint_id"]) . "</td>
                                                    <td>" . htmlspecialchars($comrow["message"]) . "</td>
                                                    <td>" . htmlspecialchars($comrow["remark"]) . "</td>;
                                                </tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="applycomplaints">
                        <div class="row border g-0  shadow-sm">
                            <div class="col p-4">

                                <form method="POST" action="complaint-apply.php" enctype="multipart/form-data">
                                    <div class="myform">
                                        <div class="form-row align-items-center">
                                            <div class="col-auto my-1">
                                            <p><label for="message">Write your complaint here:</label></p>
                                            <textarea id="message" name="message" rows="4" cols="50"></textarea>
                                            </div>
                                            <div>
                                                <?php
                                                    // Use prepared statement to prevent SQL injection
                                                    $stmt = $conn->prepare("SELECT id, organization_id FROM users WHERE username = ?");
                                                    $stmt->bind_param("s", $_SESSION['username']);
                                                    $stmt->execute();
                                                    $userresult = $stmt->get_result();
                                                    
                                                    if (!$userresult) {
                                                        die("Invalid Query: ");
                                                    }
                                                    $userrow = $userresult->fetch_assoc();
                                                    echo "<input type='hidden' name='id' value=" . (int)$userrow["id"] .">";
                                                    echo "<input type='hidden' name='organization_id' value=" . (int)$userrow["organization_id"] .">";
                                                    echo "<input type='hidden' name='username' value='" . htmlspecialchars($_SESSION['username']) ."'>";
                                                ?>
                                            </div>
                                        </div>
                                        <div class="form-row align-items-center">
                                            <div class="form-group col-6">
                                                <div class="mr-sm-8">
                                                    <input type="submit" name="request" value="Apply" class="btn btn-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>









    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/ums/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pprn3073KE6tl6bjs2QrFaJGz5/SUsLqktiwsUTF55Jfv3qYSDhgCecCxMW52nD2" crossorigin="anonymous"></script>
</body>

</html>
